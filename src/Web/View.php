<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Base\View as BaseView;
use Ep\Web\Event\BeginBody;
use Ep\Web\Event\BeginPage;
use Ep\Web\Event\EndBody;
use Ep\Web\Event\EndPage;
use Ep\Web\Event\Head;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Script;
use Yiisoft\Html\Tag\Style;
use Yiisoft\Json\Json;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use InvalidArgumentException;

final class View extends BaseView
{
    public const POSITION_HEAD = 1;
    public const POSITION_BEGIN = 2;
    public const POSITION_END = 3;

    private const PLACEHOLDER_HEAD = '<![CDATA[EP-BLOCK-HEAD]]>';
    private const PLACEHOLDER_BODY_BEGIN = '<![CDATA[EP-BLOCK-BODY-BEGIN]]>';
    private const PLACEHOLDER_BODY_END = '<![CDATA[EP-BLOCK-BODY-END]]>';

    protected AssetManager $assetManager;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        ContainerInterface $container,
        AssetManager $assetManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($container);

        $this->assetManager = clone $assetManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function register(array $bundles = []): void
    {
        $this->assetManager->register($bundles, self::POSITION_END, self::POSITION_HEAD);

        $this->registerFiles('JS', $this->assetManager->getJsFiles());
        $this->registerFiles('CSS', $this->assetManager->getCssFiles());
        $this->registerStrings('JS', $this->assetManager->getJsStrings());
        $this->registerStrings('CSS', $this->assetManager->getCssStrings());
        $this->registerJsVars($this->assetManager->getJsVars());
    }

    private array $jsFiles = [];

    public function registerJsFile(string $url, int $position = self::POSITION_END, array $options = [], string $key = null): void
    {
        $this->jsFiles[$position][$key] = Html::javaScriptFile($url, $options)->render();
    }

    private array $cssFiles = [];

    public function registerCssFile(string $url, int $position = self::POSITION_HEAD, array $options = [], string $key = null): void
    {
        $this->cssFiles[$position][$key] = Html::cssFile($url, $options)->render();
    }

    private array $js = [];

    /**
     * @param string|Script $js
     */
    public function registerJs($js, int $position = self::POSITION_END, string $key = null): void
    {
        if (is_string($js)) {
            $js = Html::script($js);
        }
        $js = $js->render();

        $this->js[$position][$key ?? md5($js)] = $js;
    }

    private array $css = [];

    /**
     * @param string|Style $css
     */
    public function registerCss($css, int $position = self::POSITION_HEAD, string $key = null): void
    {
        if (is_string($css)) {
            $css = Html::style($css);
        }
        $css = $css->render();

        $this->css[$position][$key ?? md5($css)] = $css;
    }

    private array $metaTags = [];

    public function registerMeta(array $attributes, string $key = null): void
    {
        $meta = Html::meta($attributes)->render();

        $key === null ? $this->metaTags[] = $meta : $this->metaTags[$key] = $meta;
    }

    private array $linkTags = [];

    public function registerLink(array $attributes, int $position = self::POSITION_HEAD, string $key = null): void
    {
        $link = Html::link()->attributes($attributes)->render();

        $key === null ? $this->linkTags[$position][] = $link : $this->linkTags[$position][$key] = $link;
    }

    public function beginPage(): void
    {
        ob_start();
        PHP_VERSION_ID >= 80000 ? ob_implicit_flush(false) : ob_implicit_flush(0);

        $this->eventDispatcher->dispatch(new BeginPage($this));
    }

    public function endPage(): void
    {
        $this->eventDispatcher->dispatch(new EndPage($this));

        echo strtr(ob_get_clean(), [
            self::PLACEHOLDER_HEAD => $this->renderHeadHtml(),
            self::PLACEHOLDER_BODY_BEGIN => $this->renderBodyBeginHtml(),
            self::PLACEHOLDER_BODY_END => $this->renderBodyEndHtml(),
        ]);
    }

    public function head(): void
    {
        echo self::PLACEHOLDER_HEAD;

        $this->eventDispatcher->dispatch(new Head($this));
    }

    public function beginBody(): void
    {
        echo self::PLACEHOLDER_BODY_BEGIN;

        $this->eventDispatcher->dispatch(new BeginBody($this));
    }

    public function endBody(): void
    {
        echo self::PLACEHOLDER_BODY_END;

        $this->eventDispatcher->dispatch(new EndBody($this));
    }

    private function renderHeadHtml(): string
    {
        return implode("\n", $this->metaTags) . $this->renderPositionHtml(self::POSITION_HEAD);
    }

    private function renderBodyBeginHtml(): string
    {
        return $this->renderPositionHtml(self::POSITION_BEGIN);
    }

    private function renderBodyEndHtml(): string
    {
        return $this->renderPositionHtml(self::POSITION_END);
    }

    private function renderPositionHtml(int $position): string
    {
        $lines = [];

        if (!empty($this->linkTags[$position])) {
            $lines[] = implode("\n", $this->linkTags[$position]);
        }
        if (!empty($this->cssFiles[$position])) {
            $lines[] = implode("\n", $this->cssFiles[$position]);
        }
        if (!empty($this->css[$position])) {
            $lines[] = implode("\n", $this->css[$position]);
        }
        if (!empty($this->jsFiles[$position])) {
            $lines[] = implode("\n", $this->jsFiles[$position]);
        }
        if (!empty($this->js[$position])) {
            $lines[] = implode("\n", $this->js[$position]);
        }

        return implode("\n", $lines);
    }

    private function registerFiles(string $type, array $files): void
    {
        foreach ($files as $key => $config) {
            $file =  $config[0] ?? null;
            if (!is_string($file)) {
                throw new InvalidArgumentException(sprintf('%s file should be string.', $type));
            }
            $position = $config[1];
            unset($config[0], $config[1]);

            switch ($type) {
                case 'JS':
                    $this->registerJsFile($file, $position, $config, $key);
                    break;
                case 'CSS':
                    $this->registerCssFile($file, $position, $config, $key);
                    break;
            }
        }
    }

    private function registerStrings(string $type, array $strings): void
    {
        foreach ($strings as $key => $config) {
            $string =  $config[0] ?? null;
            switch ($type) {
                case 'JS':
                    if (!is_string($string) && !($string instanceof Script)) {
                        throw new InvalidArgumentException('JS string should be string or instance of ' . Script::class);
                    }
                    $htmlMethod = 'script';
                    $registerMethod = 'registerJs';
                    break;
                case 'CSS':
                    if (!is_string($string) && !($string instanceof Style)) {
                        throw new InvalidArgumentException('CSS string should be string or instance of ' . Style::class);
                    }
                    $htmlMethod = 'style';
                    $registerMethod = 'registerCss';
                    break;
            }
            $position = $config[1];
            unset($config[0], $config[1]);

            if (is_string($string)) {
                $string = Html::$htmlMethod($string)->attributes($config);
            }

            $this->$registerMethod($string, $position, is_string($key) ? $key : md5($string->render()));
        }
    }

    private function registerJsVars(array $jsVars)
    {
        foreach ($jsVars as [$name, $value, $position]) {
            if (!is_string($name)) {
                throw new InvalidArgumentException('JS variable name should be string');
            }

            $js = sprintf('var %s = %s;', $name, Json::htmlEncode($value));

            $this->js[$position][$name] = Html::script($js)->render();
        }
    }
}
