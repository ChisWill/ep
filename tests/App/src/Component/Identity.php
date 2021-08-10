<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Yiisoft\Auth\IdentityInterface;

class Identity implements IdentityInterface
{
    private array $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function getId(): ?string
    {
        return (string) $this->attributes['id'] ?? null;
    }

    public function getAll()
    {
        return $this->attributes;
    }
}
