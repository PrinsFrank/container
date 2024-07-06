<?php
declare(strict_types=1);

namespace PrinsFrank\Container\Definition;

use PrinsFrank\Container\Container;
use PrinsFrank\Container\Definition\Item\Definition;
use PrinsFrank\Container\Exception\MissingDefinitionException;
use PrinsFrank\Container\Exception\ShouldNotHappenException;
use PrinsFrank\Container\Resolver\ParameterResolver;

final class DefinitionSet {
    /** @var list<Definition<covariant object>> */
    private array $definitions = [];

    public function __construct(public readonly Container $forContainer, public readonly ParameterResolver $parameterResolver) {
    }

    /**
     * @template T of object
     * @param class-string<T> $identifier
     */
    public function has(string $identifier): bool {
        foreach ($this->definitions as $definition) {
            if ($definition->isFor($identifier)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @template T of object
     * @param class-string<T> $identifier
     * @throws MissingDefinitionException|ShouldNotHappenException
     * @return T|null
     */
    public function get(string $identifier, Container $container): ?object {
        foreach ($this->definitions as $definition) {
            if ($definition->isFor($identifier) === false) {
                continue;
            }

            $item = $definition->get($container, $this->parameterResolver);
            if ($item !== null && is_a($item, $identifier, true) === false) {
                throw new ShouldNotHappenException(sprintf('The container returned an %s but expected to return a %s', gettype($item), $identifier));
            }

            return $item;
        }

        throw new MissingDefinitionException($identifier);
    }

    /** @param Definition<covariant object> $definition */
    public function add(Definition $definition): void {
        $this->definitions[] = $definition;
    }
}
