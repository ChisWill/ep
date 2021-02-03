<?php

declare(strict_types=1);

namespace Ep\Standard;

interface RouteInterface
{
    public function matchRule(string $requestPath, string $method = 'GET'): array;

    public function solveRouteInfo(array $routeInfo): array;

    public function parseHandler(string $handler): array;

    public function getCapture(): array;
}
