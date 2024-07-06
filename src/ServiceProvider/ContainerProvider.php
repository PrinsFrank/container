<?php declare(strict_types=1);

namespace PrinsFrank\Container\ServiceProvider;

use Override;
use PrinsFrank\Container\Container;
use PrinsFrank\Container\Definition\DefinitionSet;
use PrinsFrank\Container\Definition\Item\Singleton;
use PrinsFrank\Container\Exception\InvalidArgumentException;

class ContainerProvider implements ServiceProviderInterface {
    #[Override]
    public function provides(string $identifier): bool {
        return $identifier === Container::class;
    }

    /** @throws InvalidArgumentException */
    #[Override]
    public function register(DefinitionSet $resolvedSet): void {
        $resolvedSet->add(
            new Singleton(
                Container::class,
                fn () => $resolvedSet->forContainer
            )
        );
    }
}
