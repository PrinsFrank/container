<?php
declare(strict_types=1);

namespace PrinsFrank\Container\ServiceProvider;

use PrinsFrank\Container\Definition\DefinitionSet;

interface ServiceProviderInterface {
    /** @param class-string<object> $identifier */
    public function provides(string $identifier): bool;

    public function register(string $identifier, DefinitionSet $resolvedSet): void;
}
