<?php
declare(strict_types=1);

namespace PrinsFrank\Container\Definition\Item;

use Closure;
use Override;
use PrinsFrank\Container\Container;
use PrinsFrank\Container\Exception\InvalidArgumentException;
use PrinsFrank\Container\Exception\InvalidMethodException;
use PrinsFrank\Container\Exception\InvalidServiceProviderException;
use PrinsFrank\Container\Exception\MissingDefinitionException;
use PrinsFrank\Container\Exception\UnresolvableException;
use PrinsFrank\Container\Resolver\ParameterResolver;
use ReflectionClass;
use stdClass;

/**
 * @template T of object
 * @implements Definition<T>
 */
class AbstractConcrete implements Definition {
    /**
     * @param class-string<T> $identifier
     * @param Closure(): T|Closure(): null $new
     * @throws InvalidArgumentException
     */
    public function __construct(
        readonly private string  $identifier,
        readonly private Closure $new,
    ) {
        if (interface_exists($this->identifier) === false && (class_exists($this->identifier) === false || (new ReflectionClass($this->identifier))->isAbstract() === false)) {
            throw new InvalidArgumentException('Argument $identifier is expected to be a class-string for an interface or abstract class');
        }
    }

    #[Override]
    public function isFor(string $identifier): bool {
        return $identifier === $this->identifier;
    }

    /** @throws UnresolvableException|InvalidServiceProviderException|InvalidMethodException|MissingDefinitionException */
    #[Override]
    public function get(Container $container, ParameterResolver $parameterResolver): ?object {
        $resolved = ($this->new)(...$parameterResolver->resolveParamsForClosure($this->new));
        if ($resolved !== null && $resolved instanceof $this->identifier === false) {
            throw new InvalidServiceProviderException(sprintf('Closure returned type "%s" instead of concrete for "%s"', $resolved instanceof stdClass ? get_class($resolved) : gettype($resolved), $this->identifier));
        }

        return $resolved;
    }
}
