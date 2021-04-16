<?php

use Ep\Contract\NotFoundException;

/**
 * @var string            $path
 * @var NotFoundException $exception
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Not Found</title>

    <style type="text/css">
        <?= $this->renderFile('production.css') ?>
    </style>
</head>

<body>
    <h1>Not Found.</h1>
    <h2>Unable to find the page "<?= $path ?>".</h2>
    <h3>
        Because <?= $exception->getMessage() ?>
    </h3>
    <div class="version">
        <?= date('Y-m-d H:i:s') ?>
    </div>
</body>

</html>