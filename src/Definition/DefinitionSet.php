<?php
declare(strict_types=1);

namespace PrinsFrank\Container\Definition;

use PrinsFrank\Container\Container;
use PrinsFrank\Container\Definition\Item\Definition;
use PrinsFrank\Container\Exception\ShouldNotHappenException;

final class DefinitionSet {
    /** @var list<Definition<covariant object>> */
    private array $definitions = [];

    public function __construct(public readonly Container $forContainer) {
    }

    /**
     * @template T of object
     * @param class-string<T> $identifier
     * @throws ShouldNotHappenException
     * @return T
     */
    public function get(string $identifier, Container $container): ?object {
        foreach ($this->definitions as $definition) {
            if ($definition->isFor($identifier) === false) {
                continue;
            }

            $item = $definition->get($container);
            if (is_a($item, $identifier, true) === false) {
                throw new ShouldNotHappenException(sprintf('The container returned an %s but expected to return a %s', gettype($item), $identifier));
            }

            return $item;
        }

        return null;
    }

    /** @param Definition<covariant object> $definition */
    public function add(Definition $definition): void {
        $this->definitions[] = $definition;
    }
}
