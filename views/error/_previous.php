<?php

/**
 * @var Exception $exception
 * @var Ep\Web\View $this
 * @var Ep\Web\ErrorHandler $handler
 */
$handler = $this->context;
?>
<div class="previous">
    <span class="arrow">&crarr;</span>
    <h2>
        <span>Caused by:</span>
        <span><?= get_class($exception) ?></span>
    </h2>
    <h3><?= nl2br($handler->htmlEncode($exception->getMessage())) ?></h3>
    <p>in <span class="file"><?= $exception->getFile() ?></span> at line <span class="line"><?= $exception->getLine() ?></span></p>
    <?= $handler->renderPreviousException($exception) ?>
</div>