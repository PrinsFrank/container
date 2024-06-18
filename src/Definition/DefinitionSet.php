<?php
declare(strict_types=1);

namespace PrinsFrank\Container\Definition;

use PrinsFrank\Container\Definition\Item\Definition;
use PrinsFrank\Container\Exception\ShouldNotHappenException;

final class DefinitionSet {
    /** @var list<Definition<object>> */
    private array $definitions = [];

    /** @param class-string<object> $identifier */
    public function has(string $identifier): bool {
        foreach ($this->definitions as $definition) {
            if ($definition->isFor($identifier) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @template T of object
     * @param class-string<T> $identifier
     * @throws ShouldNotHappenException
     * @return T
     */
    public function get(string $identifier): object {
        foreach ($this->definitions as $definition) {
            if ($definition->isFor($identifier) === false) {
                continue;
            }

            return $definition->get();
        }

        throw new ShouldNotHappenException();
    }

    /** @param Definition<object> $definition */
    public function add(Definition $definition): void {
        $this->definitions[] = $definition;
    }
}
