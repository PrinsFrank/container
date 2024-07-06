<?php
declare(strict_types=1);

namespace PrinsFrank\Container\Definition\Item;

use Closure;
use Override;
use PrinsFrank\Container\Container;
use PrinsFrank\Container\Exception\InvalidArgumentException;
use PrinsFrank\Container\Exception\InvalidServiceProviderException;
use PrinsFrank\Container\Exception\ShouldNotHappenException;
use PrinsFrank\Container\Exception\UnresolvableException;
use ReflectionClass;

/**
 * @template T of object
 * @implements Definition<T>
 */
final readonly class AbstractConcrete implements Definition {
    /**
     * @param class-string<T> $identifier
     * @param Closure(): T $new
     * @throws InvalidArgumentException
     */
    public function __construct(
        private string  $identifier,
        private Closure $new,
    ) {
        if (interface_exists($this->identifier) === false || class_exists($this->identifier) === false || (new ReflectionClass($this->identifier))->isAbstract() === false) {
            throw new InvalidArgumentException('Argument $identifier is expected to be a class-string for an interface or abstract class');
        }
    }

    #[Override]
    public function isFor(string $identifier): bool {
        return $identifier === $this->identifier;
    }

    /** @throws ShouldNotHappenException|UnresolvableException|InvalidServiceProviderException */
    #[Override]
    public function get(Container $container): object {
        $resolved = $this->new->__invoke(...$container->resolveParamsFor($this->new, '__invoke'));
        if ($resolved instanceof $this->identifier === false) {
            throw new ShouldNotHappenException(sprintf('Container returned type "%s" instead of concrete for "%s"', gettype($resolved), $this->identifier));
        }

        return $resolved;
    }
}
