<?php

/**
 * @var string|null $file
 * @var int|null $line
 * @var string|null $class
 * @var string|null $method
 * @var int $index
 * @var string[] $lines
 * @var int $begin
 * @var int $end
 * @var array $args
 * @var Ep\Web\View $this
 * @var Ep\Web\ErrorHandler $handler
 */
$handler = $this->context;
?>
<li class="<?php if ($index === 1 || !$handler->isVendorFile($file)) echo 'application'; ?> call-stack-item" data-line="<?= (int) ($line - $begin) ?>">
    <div class="element-wrap">
        <div class="element">
            <span class="item-number"><?= (int) $index ?>.</span>
            <span class="text"><?php if ($file !== null) echo 'in ' . $handler->htmlEncode($file); ?></span>
            <span class="at">
                <?php if ($line !== null) echo 'at line'; ?>
                <span class="line"><?php if ($line !== null) echo (int) $line + 1; ?></span>
            </span>
            <?php if ($method !== null) : ?>
                <span class="call">
                    <?php if ($file !== null) echo '&ndash;'; ?>
                    <?= ($class ? $class . '::' : '') . $method . '(' . $handler->argumentsToString($args) . ')' ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <?php if (!empty($lines)) : ?>
        <div class="code-wrap">
            <div class="error-line"></div>
            <?php for ($i = $begin; $i <= $end; ++$i) : ?><div class="hover-line"></div><?php endfor; ?>
            <div class="code">
                <?php for ($i = $begin; $i <= $end; ++$i) : ?><span class="lines-item"><?= (int) ($i + 1) ?></span><?php endfor; ?>
                <pre><?php for ($i = $begin; $i <= $end; ++$i) echo (trim($lines[$i]) === '') ? " \n" : $handler->htmlEncode($lines[$i]); ?>
                </pre>
            </div>
        </div>
    <?php endif; ?>
</li>