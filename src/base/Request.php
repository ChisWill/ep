<?php

namespace ep\base;

abstract class Request
{
    public abstract function solveRouteRules(array $rules, string $requestPath): string;
}
