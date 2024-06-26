<?php
declare(strict_types=1);

namespace PrinsFrank\Container\Definition\Item;

use PrinsFrank\Container\Container;

/** @template T of object */
interface Definition {
    /** @phpstan-assert-if-true class-string<T> $identifier */
    public function isFor(string $identifier): bool;

    /** @return T */
    public function get(Container $container): object;
}
