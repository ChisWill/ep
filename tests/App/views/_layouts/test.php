<?php

/**
 * @var Ep\Web\View $this 
 */

use Ep\Tests\App\Asset\MainAsset;

$manager = $this->register([
    MainAsset::class
]);

$this->registerMeta([
    'a' => 1
]);
$this->registerLink([
    'b' => 3,
], $this::POSITION_END);
$this->registerJs('console.log("js string");');
$this->registerCss('h1 {color: blue} ');

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test - Ep</title>
    <?php $this->head() ?>
</head>

<body>
    <?php $this->beginBody(); ?>

    <header>
        <h3>头部</h3>
        <h2>Controller: <?= $this->context->id ?></h2>
        <h3>Action: <?= $this->context->actionId ?></h3>
    </header>

    <?= $content ?>

    <footer>
        <h3>尾部</h3>
    </footer>

    <?php $this->endBody(); ?>
</body>

</html>
<?php
$this->endPage();
