<?php
declare(strict_types=1);

namespace PrinsFrank\Container\Definition\Item;

use PrinsFrank\Container\Container;
use PrinsFrank\Container\Resolver\ParameterResolver;

/** @template T of object */
interface Definition {
    public function isFor(string $identifier): bool;

    /** @return T */
    public function get(Container $container, ParameterResolver $parameterResolver): object;
}
