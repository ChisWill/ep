<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Error</title>

    <style type="text/css">
        <?= $this->loadFile('simple.css') ?>
    </style>
</head>

<body>
    <h1>Error</h1>
    <h2>An internal server error occurred.</h2>
    <p>
        The above error occurred while the Web server was processing your request.
    </p>
    <p>
        Please contact us if you think this is a server error. Thank you.
    </p>
    <div class="version">
        <?= date('Y-m-d H:i:s') ?>
    </div>
</body>

</html>