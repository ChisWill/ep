<?php

declare(strict_types=1);

namespace Ep\Tests\App\Asset;

use Ep\Web\View;
use Yiisoft\Assets\AssetBundle;

class JqueryAsset extends AssetBundle
{
    public bool $cdn = true;

    public array $js = [
        'https://lib.baomitu.com/jquery/3.5.1/jquery.min.js',
    ];

    public ?int $jsPosition = View::POSITION_HEAD;
}
