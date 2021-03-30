<?php

/**
 * @var Throwable $exception
 * @var Psr\Http\Message\ServerRequestInterface|null $request
 * @var Ep\Web\View $this
 * @var Ep\Web\ErrorRenderer $renderer
 */
$renderer = $this->context;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/ico;base64,aWNv">
    <title><?= get_class($exception) ?></title>

    <style type="text/css">
        <?= $this->loadFile('development.css') ?>
    </style>
</head>

<body>
    <div class="header">
        <h1><?= ($exception instanceof ErrorException ? '<span>' . $renderer->getErrorName($exception->getSeverity()) . '</span> &ndash; ' : '') . get_class($exception) ?></h1>
        <h2><?= nl2br($renderer->htmlEncode($exception->getMessage())) ?></h2>
        <?= $renderer->renderPreviousException($exception) ?>
    </div>

    <div class="call-stack">
        <ul>
            <?= $renderer->renderCallStackItem($exception->getFile(), $exception->getLine(), null, null, [], 1) ?>
            <?php for ($i = 0, $trace = $exception->getTrace(), $length = count($trace); $i < $length; ++$i) : ?>
                <?= $renderer->renderCallStackItem(
                    $trace[$i]['file'] ?? null,
                    $trace[$i]['line'] ?? null,
                    $trace[$i]['class'] ?? null,
                    $trace[$i]['function'] ?? null,
                    $trace[$i]['args'] ?? [],
                    $i + 2
                ) ?>
            <?php endfor; ?>
        </ul>
    </div>

    <div class="request">
        <div class="code">
            <?= $renderer->renderRequest($request) ?>
        </div>
    </div>

    <div class="footer">
        <?php foreach ($renderer->getServerInfo($request) as $title => $info) : ?>
            <?= '<p>' . $title . '：' . $info . '</p>' ?>
        <?php endforeach ?>
    </div>

    <script type="text/javascript">
        <?= $this->loadFile('hljs.js') ?>
    </script>

    <script type="text/javascript">
        window.onload = function() {
            var codeBlocks = document.getElementsByTagName('pre'),
                callStackItems = document.getElementsByClassName('call-stack-item');

            // highlight code blocks
            for (var i = 0, imax = codeBlocks.length; i < imax; ++i) {
                hljs.highlightBlock(codeBlocks[i], '    ');
            }

            var refreshCallStackItemCode = function(callStackItem) {
                if (!callStackItem.getElementsByTagName('pre')[0]) {
                    return;
                }
                var top = callStackItem.getElementsByClassName('code-wrap')[0].offsetTop - window.pageYOffset + 3,
                    lines = callStackItem.getElementsByTagName('pre')[0].getClientRects(),
                    lineNumbers = callStackItem.getElementsByClassName('lines-item'),
                    errorLine = callStackItem.getElementsByClassName('error-line')[0],
                    hoverLines = callStackItem.getElementsByClassName('hover-line');
                for (var i = 0, imax = lines.length; i < imax; ++i) {
                    if (!lineNumbers[i]) {
                        continue;
                    }
                    lineNumbers[i].style.top = parseInt(lines[i].top - top) + 'px';
                    hoverLines[i].style.top = parseInt(lines[i].top - top) + 'px';
                    hoverLines[i].style.height = parseInt(lines[i].bottom - lines[i].top + 6) + 'px';
                    if (parseInt(callStackItem.getAttribute('data-line')) == i) {
                        errorLine.style.top = parseInt(lines[i].top - top) + 'px';
                        errorLine.style.height = parseInt(lines[i].bottom - lines[i].top + 6) + 'px';
                    }
                }
            };

            for (var i = 0, imax = callStackItems.length; i < imax; ++i) {
                refreshCallStackItemCode(callStackItems[i]);

                // toggle code block visibility
                callStackItems[i].getElementsByClassName('element-wrap')[0].addEventListener('click', function() {
                    var callStackItem = this.parentNode,
                        code = callStackItem.getElementsByClassName('code-wrap')[0]
                    code.style.display = window.getComputedStyle(code).display == 'block' ? 'none' : 'block';
                    refreshCallStackItemCode(callStackItem);
                });
            }
        };

        // // Highlight lines that have text in them but still support text selection:
        document.onmousedown = function() {
            document.getElementsByTagName('body')[0].classList.add('mousedown');
        }
        document.onmouseup = function() {
            document.getElementsByTagName('body')[0].classList.remove('mousedown');
        }
    </script>
</body>

</html>