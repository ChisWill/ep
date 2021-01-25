<?php

namespace Ep\base;

abstract class Request
{
    public abstract function solveRouteRules(array $rules, string $requestPath): string;
}
