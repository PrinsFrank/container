<?php
declare(strict_types=1);

namespace PrinsFrank\Container\Definition\Item;

use Closure;
use Override;
use PrinsFrank\Container\Container;
use PrinsFrank\Container\Exception\InvalidArgumentException;
use PrinsFrank\Container\Exception\ShouldNotHappenException;

/**
 * @template T of object
 * @implements Definition<T>
 */
final readonly class Singleton implements Definition {
    /** @var T */
    private object $instance; // @phpstan-ignore property.uninitializedReadonly

    /**
     * @param class-string<T> $identifier
     * @param Closure(): T $new
     * @throws InvalidArgumentException
     */
    public function __construct(
        private string  $identifier,
        private Closure $new,
    ) {
        if (class_exists($this->identifier) === false || interface_exists($this->identifier) === true) {
            throw new InvalidArgumentException('Argument $identifier is expected to be a class-string for a concrete class');
        }
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
                throw new ShouldNotHappenException(sprintf('Container returned type "%s" instead of "%s"', gettype($resolved), $this->identifier));
            }

            $this->instance = $resolved; // @phpstan-ignore property.readOnlyAssignNotInConstructor
        }

        return $this->instance;
    }
}
