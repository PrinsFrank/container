<?php
declare(strict_types=1);

namespace PrinsFrank\Container\Definition\Item;

use Closure;
use Override;
use PrinsFrank\Container\Container;
use PrinsFrank\Container\Exception\ShouldNotHappenException;

/**
 * @template T of object
 * @implements Definition<T>
 */
final readonly class Singleton implements Definition {
    /** @var T */
    private object $instance; // @phpstan-ignore property.uninitializedReadonly

    public function __construct(
        private string  $identifier,
        private Closure $new,
    ) {
    }

    #[Override]
    public function isFor(string $identifier): bool {
        return $identifier === $this->identifier;
    }

    #[Override]
    public function get(Container $container): object {
        if (isset($this->instance) === false) {
            $resolved = $container->invoke($this->new);
            if ($resolved instanceof $this->identifier === false) {
                throw new ShouldNotHappenException();
            }

            $this->instance = $resolved; // @phpstan-ignore property.readOnlyAssignNotInConstructor
        }

        return $this->instance;
    }
}
