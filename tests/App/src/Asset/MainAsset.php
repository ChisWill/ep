<?php

declare(strict_types=1);

namespace Ep\Tests\App\Asset;

use Yiisoft\Assets\AssetBundle;

class MainAsset extends AssetBundle
{
    public ?string $baseUrl = '/assets';

    public ?string $basePath = '@root/public/assets';

    public ?string $sourcePath = '@root/static';

    public array $js = [
        'js/main.js',
    ];

    public array $depends = [
        JqueryAsset::class
    ];
}
