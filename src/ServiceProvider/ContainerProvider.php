<?php declare(strict_types=1);

namespace PrinsFrank\Container\ServiceProvider;

use PrinsFrank\Container\Container;
use PrinsFrank\Container\Definition\DefinitionSet;
use PrinsFrank\Container\Definition\Item\Singleton;

class ContainerProvider implements ServiceProviderInterface {
    public function provides(string $identifier): bool {
        return $identifier === Container::class;
    }

    public function register(DefinitionSet $resolvedSet): void {
        $resolvedSet->add(
            new Singleton(
                Container::class,
                fn () => $resolvedSet->forContainer
            )
        );
    }
}
