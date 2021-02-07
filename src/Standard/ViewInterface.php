<?php

declare(strict_types=1);

namespace Ep\Standard;

interface ViewInterface
{
    public function __construct(ContextInterface $context, string $viewPath);

    public function render(string $path, array $params = []): string;

    public function renderPartial(string $path, array $params = []): string;

    public function setLayout(string $layout): void;
}
