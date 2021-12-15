<?php

declare(strict_types=1);

namespace Ep\Tests\App\Asset;

use Ep\Web\View;
use Yiisoft\Assets\AssetBundle;

class CommonAsset extends AssetBundle
{
    public ?string $baseUrl = '/assets';

    public ?string $basePath = '@root/static';

    public array $js = [
        'js/common.js',
    ];

    public array $css = [
        'css/site.css',
    ];

    public array $depends = [
        JqueryAsset::class
    ];

    public array $jsVars = [
        'name' => '456',
        ['address', 'efg']
    ];

    public ?int $jsPosition = View::POSITION_BEGIN;
}
