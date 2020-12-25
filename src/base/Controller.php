<?php

namespace ep\base;

abstract class Controller
{
    public abstract function beforeAction(): bool;

    public abstract function afterAction(Response $response): void;
}
