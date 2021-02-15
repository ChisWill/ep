<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep;
use Ep\Base\ControllerFactory;
use Ep\Base\View;
use Ep\Contract\ContextInterface;
use Ep\Helper\Alias;
use Ep\Helper\Date;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;
use Throwable;

class ErrorHandler extends \Ep\Base\ErrorHandler implements ContextInterface
{
    public string $id = 'error';

    public int $maxSourceLines = 19;

    public int $maxTraceSourceLines = 13;

    public array $displayVars = ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION'];

    private ?View $view = null;

    protected function getView(): View
    {
        if ($this->view === null) {
            $this->view = new View($this, '@ep/views');
        }
        return $this->view;
    }

    /**
     * @param  ServerRequestInterface|null $request
     * 
     * @return ResponseInterface|string|null
     */
    public function renderException(Throwable $e, $request = null)
    {
        if (Ep::getConfig()->debug) {
            http_response_code(Status::INTERNAL_SERVER_ERROR);
            return $this->getView()->renderPartial('exception', ['exception' => $e]);
        } else {
            return (new ControllerFactory(Ep::getConfig()->controllerDirAndSuffix))->run(Ep::getConfig()->errorHandler, $request);
        }
    }

    /**
     * @param ServerRequestInterface $request
     */
    protected function log(Throwable $e, $request): void
    {
        $context = [
            'category' => 'exception',
            'host' => $request->getUri()->getHost(),
            'path' => $request->getRequestTarget(),
            'method' => $request->getMethod()
        ];
        if ($request->getMethod() === Method::POST) {
            $context['post'] = $request->getParsedBody();
        }
        $this->logger->error($this->convertToString($e), $context);
    }

    public function renderPreviousException(Throwable $e): string
    {
        if (($previous = $e->getPrevious()) !== null) {
            return $this->getView()->renderPartial('_previous', ['exception' => $previous]);
        } else {
            return '';
        }
    }

    public function renderCallStackItem(?string $file, ?int $line, ?string $class, ?string $method, array $args, int $index): string
    {
        $lines = [];
        $begin = $end = 0;
        if ($file !== null && $line !== null) {
            $line--;
            $lines = @file($file);
            if ($line < 0 || $lines === false || ($lineCount = count($lines)) < $line) {
                return '';
            }
            $half = (int) (($index === 1 ? $this->maxSourceLines : $this->maxTraceSourceLines) / 2);
            $begin = $line - $half > 0 ? $line - $half : 0;
            $end = $line + $half < $lineCount ? $line + $half : $lineCount - 1;
        }

        return $this->getView()->renderPartial('_item', [
            'file' => $file,
            'line' => $line,
            'class' => $class,
            'method' => $method,
            'index' => $index,
            'lines' => $lines,
            'begin' => $begin,
            'end' => $end,
            'args' => $args,
        ]);
    }

    public function renderRequest(): string
    {
        $request = '';
        foreach ($this->displayVars as $name) {
            if (!empty($GLOBALS[$name])) {
                $request .= '$' . $name . ' = ' . var_export($GLOBALS[$name], true) . ";\n\n";
            }
        }
        if ($request) {
            return '<pre>' . rtrim($request, "\n") . '</pre>';
        } else {
            return '';
        }
    }

    public function isVendorFile(?string $file): bool
    {
        return $file === null || strpos($file, Alias::get('@vendor')) === 0;
    }

    public function htmlEncode(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    public function argumentsToString(array $args): string
    {
        $count = 0;
        $isAssoc = $args !== array_values($args);

        foreach ($args as $key => $value) {
            $count++;
            if ($count >= 5) {
                if ($count > 5) {
                    unset($args[$key]);
                } else {
                    $args[$key] = '...';
                }
                continue;
            }

            if (is_object($value)) {
                $args[$key] = '<span class="title">' . $this->htmlEncode(get_class($value)) . '</span>';
            } elseif (is_bool($value)) {
                $args[$key] = '<span class="keyword">' . ($value ? 'true' : 'false') . '</span>';
            } elseif (is_string($value)) {
                $fullValue = $this->htmlEncode($value);
                if (mb_strlen($value, 'UTF-8') > 64) {
                    $displayValue = $this->htmlEncode(mb_substr($value, 0, 64, 'UTF-8')) . '...';
                    $args[$key] = "<span class=\"string\" title=\"$fullValue\">'$displayValue'</span>";
                } else {
                    $args[$key] = "<span class=\"string\">'$fullValue'</span>";
                }
            } elseif (is_array($value)) {
                $args[$key] = '[' . $this->argumentsToString($value) . ']';
            } elseif ($value === null) {
                $args[$key] = '<span class="keyword">null</span>';
            } elseif (is_resource($value)) {
                $args[$key] = '<span class="keyword">resource</span>';
            } else {
                $args[$key] = '<span class="number">' . $value . '</span>';
            }

            if (is_string($key)) {
                $args[$key] = '<span class="string">\'' . $this->htmlEncode($key) . "'</span> => $args[$key]";
            } elseif ($isAssoc) {
                $args[$key] = "<span class=\"number\">$key</span> => $args[$key]";
            }
        }

        return implode(', ', $args);
    }

    public function getServerInfo(): array
    {
        return [
            'Now' => Date::fromUnix(),
            'Server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'PHP Version' => phpversion(),
            'Time Zone' => @ini_get('date.timezone') ?: 'Unknown',
            'Timeout' => @ini_get('max_execution_time') ?: 'Unknown',
            'Post Max Size' => @ini_get('post_max_size') ?: 'Unknown',
            'Upload Max Filesize' => @ini_get('upload_max_filesize') ?: 'Unknown',
        ];
    }
}
