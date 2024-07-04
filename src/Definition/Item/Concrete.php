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
final readonly class Concrete implements Definition {
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
        return is_a($identifier, $this->identifier, true);
    }

    #[Override]
    public function get(Container $container): object {
        $resolved = $container->invoke($this->new);
        if ($resolved instanceof $this->identifier === false) {
            throw new ShouldNotHappenException(sprintf('Container returned type "%s" instead of "%s"', gettype($resolved), $this->identifier));
        }

        return $resolved;
    }
}
