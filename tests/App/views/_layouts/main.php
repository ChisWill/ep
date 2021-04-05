<?php

/**
 * @var Ep\Base\View $this 
 */

use Ep\Tests\App\Asset\MainAsset;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Assets\AssetPublisher;
use Yiisoft\Assets\AssetPublisherInterface;

$manager = Ep::getDi()->get(AssetManager::class);
$manager->setPublisher(Ep::getDi()->get(AssetPublisherInterface::class));
$manager->register([
    MainAsset::class
]);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/ico;base64,aWNv">
    <title><?= $this->context->title ?: 'Basic - EP' ?></title>
    <?php foreach ($manager->getJsFiles() as $jsFile) : ?>
        <script src="<?= $jsFile['url'] ?>"></script>
    <?php endforeach ?>
</head>

<body>
    <header>
        <h3>头部</h3>
    </header>

    <?= $content ?>

    <footer>
        <h3>尾部</h3>
    </footer>
</body>

</html>