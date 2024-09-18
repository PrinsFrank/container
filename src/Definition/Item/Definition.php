<?php
declare(strict_types=1);

namespace PrinsFrank\Container\Definition\Item;

use PrinsFrank\Container\Container;

/** @template T of object */
interface Definition {
    public function isFor(string $identifier): bool;

    /** @return T|null */
    public function get(Container $container): ?object;
}
