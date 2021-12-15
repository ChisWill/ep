<?php

declare(strict_types=1);

namespace Ep\Tests\App\Asset;

use Ep\Web\View;
use Yiisoft\Assets\AssetBundle;
use Yiisoft\Html\Html;

class MainAsset extends AssetBundle
{
    public ?string $baseUrl = '/assets';

    public ?string $basePath = '@root/static';

    public array $js = [
        [
            'js/main.js',
            View::POSITION_END
        ]
    ];

    public array $css = [
        [
            'css/main.css',
            View::POSITION_BEGIN
        ]
    ];

    public array $jsStrings = [
        // 'alert(1);',
        // ['alert(2);'],
        // ['alert(3);', View::POSITION_HEAD],
        // ['alert(4);', View::POSITION_END, 'id' => 'main'],
        // 'key1' => 'alert(5);',
        // 'key2' => ['alert(6);'],
        // 'key3' => ['alert(7);', View::POSITION_BEGIN],
        // 'key4' => ['alert(8);', View::POSITION_HEAD, 'id' => 'second'],
    ];

    public array $cssStrings = [
        'body {margin: 0; padding: 0}',
        'h2 { color: red; }',
        ['h2 { color: red; }'],
        ['h2 { color: red; }', 3],
        ['h2 { color: red; }', 3, 'crossorigin' => 'any'],
        'key1' => 'h2 { color: red; }',
        'key2' => ['h2 { color: red; }'],
        'key3' => ['h3 { color: green; }', 3],
        'key4' => ['h3 { color: green; }', 3, 'crossorigin' => 'any'],
    ];

    public array $jsVars = [
        'name' => '123',
        ['address', 'abc', View::POSITION_BEGIN]
    ];

    public array $depends = [
        CommonAsset::class
    ];

    public ?int $jsPosition = View::POSITION_END;

    public function __construct()
    {
        $this->jsStrings[] = Html::script('var b = 123;');
    }
}
