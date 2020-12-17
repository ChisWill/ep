<?php

namespace ep\base;

abstract class Request
{
    public abstract function getRequestUri(): string;
}
