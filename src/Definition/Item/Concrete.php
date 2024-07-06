<?php
declare(strict_types=1);

namespace PrinsFrank\Container\Definition\Item;

use Closure;
use Override;
use PrinsFrank\Container\Container;
use PrinsFrank\Container\Exception\InvalidArgumentException;
use PrinsFrank\Container\Exception\InvalidMethodException;
use PrinsFrank\Container\Exception\InvalidServiceProviderException;
use PrinsFrank\Container\Exception\ShouldNotHappenException;
use PrinsFrank\Container\Exception\UnresolvableException;
use PrinsFrank\Container\Resolver\ParameterResolver;
use ReflectionClass;

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
        if (class_exists($this->identifier) === false || interface_exists($this->identifier) === true || (new ReflectionClass($this->identifier))->isAbstract()) {
            throw new InvalidArgumentException('Argument $identifier is expected to be a class-string for a concrete class');
        }
    }

    #[Override]
    public function isFor(string $identifier): bool {
        return is_a($identifier, $this->identifier, true);
    }

    /** @throws ShouldNotHappenException|UnresolvableException|InvalidServiceProviderException|InvalidMethodException */
    #[Override]
    public function get(Container $container, ParameterResolver $parameterResolver): object {
        $resolved = $this->new->__invoke(...$parameterResolver->resolveParamsFor($this->new, '__invoke'));
        if ($resolved instanceof $this->identifier === false) {
            throw new ShouldNotHappenException(sprintf('Container returned type "%s" instead of "%s"', gettype($resolved), $this->identifier));
        }

        return $resolved;
    }
}
