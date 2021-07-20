<?php

/**
 * @var Ep\Base\View $this 
 */

use Ep\Tests\App\Asset\MainAsset;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Assets\AssetPublisherInterface;

$manager = Ep::getDi()->get(AssetManager::class);
$manager = $manager->withPublisher(Ep::getDi()->get(AssetPublisherInterface::class));
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
    <title><?= $this->context->title ?? 'Basic - EP' ?></title>
    <?php foreach ($manager->getJsFiles() as $jsFiles) : ?>
        <?php foreach ($jsFiles as $js) : ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach ?>
    <?php endforeach ?>
</head>

<body>
    <header>
        <h3>头部</h3>
        <h2>Controller: <?= $this->context->id ?></h2>
        <h3>Action: <?= $this->context->actionId ?></h3>
    </header>

    <?= $content ?>

    <footer>
        <h3>尾部</h3>
    </footer>
</body>

</html>