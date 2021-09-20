<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Object\Animal;

abstract class Animal implements AnimalInterface
{
    abstract public function getName(): string;

    abstract public function getSize(): int;

    abstract public function getPower(): int;

    abstract public function getSpeed(): int;

    abstract public function isFly(): bool;

    abstract public function isWalk(): bool;

    public function introduce(): string
    {
        return sprintf(
            'My name is "%s". I can%s fly, I can%s walk. My size is %s(%d), power is %d, speed is %d',
            $this->getName(),
            $this->isFly() ? '' : " not",
            $this->isWalk() ? '' : " not",
            $this->getSizeLevel(),
            $this->getSize(),
            $this->getPower(),
            $this->getSpeed()
        );
    }

    private function getSizeLevel(): string
    {
        $size = $this->getSize();
        switch (true) {
            case $size <= 10:
                return 'S';
            case $size <= 25:
                return 'M';
            case $size <= 55:
                return 'L';
            case $size <= 250:
                return '2L';
            case $size > 250:
                return '3L';
        }
    }
}
