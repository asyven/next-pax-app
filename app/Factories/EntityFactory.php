<?php

namespace App\Factories;

class EntityFactory
{
    protected string $className;

    public function __construct(
        string $className
    ) {
        $this->className = $className;
    }

    public function createNew(): object
    {
        return new $this->className();
    }
}
