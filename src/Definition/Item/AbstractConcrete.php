<?php
declare(strict_types=1);

namespace PrinsFrank\Container\Definition\Item;

use Closure;
use Override;
use PrinsFrank\Container\Container;
use PrinsFrank\Container\Exception\InvalidArgumentException;
use PrinsFrank\Container\Exception\ShouldNotHappenException;
use PrinsFrank\Container\Exception\UnresolvableException;

/** @implements Definition<object> */
final readonly class AbstractConcrete implements Definition {
    /** @throws InvalidArgumentException */
    public function __construct(
        private string  $identifier,
        private Closure $new,
    ) {
        if (interface_exists($this->identifier) === false) {
            throw new InvalidArgumentException('Argument $identifier is expected to be a class-string for an interface');
        }
    }

    #[Override]
    public function isFor(string $identifier): bool {
        return is_a($this->identifier, $identifier, true);
    }

    /**
     * @throws UnresolvableException
     * @throws ShouldNotHappenException
     */
    #[Override]
    public function get(Container $container): object {
        $resolved = $container->invoke($this->new);
        if ($resolved instanceof $this->identifier === false) {
            throw new ShouldNotHappenException();
        }

        return $resolved;
    }
}
