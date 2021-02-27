<?php

/**
 * @var Throwable $t
 * @var Psr\Http\Message\ServerRequestInterface $request
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Error Page</title>
</head>

<body>
    <h1>Message: <?= $t->getMessage() ?></h1>
    <h2>Path: <?= $request->getUri()->getPath() ?></h2>
</body>

</html>