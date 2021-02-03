<?php

declare(strict_types=1);

namespace Ep\Standard;

interface ViewInterface extends ResponseHandlerInterface
{
    public function __construct(ContextInterface $context, string $viewPath);

    public function render(string $path, array $params = []): ResponseHandlerInterface;

    public function renderPartial(string $path, array $params = []): ResponseHandlerInterface;
}
