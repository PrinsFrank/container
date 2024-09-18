<?php
declare(strict_types=1);

namespace PrinsFrank\Container\Definition\Item;

use Closure;
use Override;
use PrinsFrank\Container\Container;
use PrinsFrank\Container\Exception\InvalidArgumentException;
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
class Concrete implements Definition {
    /**
     * @param class-string<T> $identifier
     * @param Closure(mixed ...): (T|null) $new
     * @throws InvalidArgumentException
     */
    public function __construct(
        readonly private string  $identifier,
        readonly private Closure $new,
    ) {
        if (class_exists($this->identifier) === false || interface_exists($this->identifier) === true || (new ReflectionClass($this->identifier))->isAbstract()) {
            throw new InvalidArgumentException('Argument $identifier is expected to be a class-string for a concrete class');
        }
    }

    #[Override]
    public function isFor(string $identifier): bool {
        return is_a($identifier, $this->identifier, true);
    }

    /** @throws UnresolvableException|InvalidServiceProviderException|MissingDefinitionException */
    #[Override]
    public function get(Container $container): ?object {
        $resolved = ($this->new)(...ParameterResolver::resolveParamsForClosure($this->new, $container));
        if ($resolved !== null && $resolved instanceof $this->identifier === false) {
            throw new InvalidServiceProviderException(sprintf('Closure returned type "%s" instead of "%s"', $resolved instanceof stdClass ? get_class($resolved) : gettype($resolved), $this->identifier));
        }

        return $resolved;
    }
}
