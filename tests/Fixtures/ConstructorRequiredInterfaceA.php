<?php declare(strict_types=1);

namespace PrinsFrank\Container\Tests\Fixtures;

class ConstructorRequiredInterfaceA {
    public function __construct(public readonly InterfaceA $interfaceA) {
    }
}
