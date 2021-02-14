<?php

/**
 * @var Ep\Base\View $this 
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/ico;base64,aWNv">
    <title><?= $this->context->title ?: 'Basic - EP' ?></title>
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