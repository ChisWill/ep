<?php

declare(strict_types=1);

namespace Ep\Tests\App\Asset;

use Yiisoft\Assets\AssetBundle;

class JqueryAsset extends AssetBundle
{
    public bool $cdn = true;

    public array $js = [
        'https://lib.baomitu.com/jquery/3.5.1/jquery.min.js',
    ];
}
