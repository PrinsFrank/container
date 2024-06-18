<?php
declare(strict_types=1);

namespace PrinsFrank\Container\Definition\Item;

use Closure;
use Override;

/**
 * @template T of object
 * @implements Definition<T>
 */
final readonly class Singleton implements Definition {
    /** @var T */
    private object $instance; // @phpstan-ignore property.uninitializedReadonly

    public function __construct(
        private string  $identifier,
        private Closure $new,
    ) {
    }

    #[Override]
    public function isFor(string $identifier): bool {
        return $identifier === $this->identifier;
    }

    #[Override]
    public function get(): object {
        if (isset($this->instance) === false) {
            $this->instance = $this->new->__invoke(); // @phpstan-ignore property.readOnlyAssignNotInConstructor
        }

        return $this->instance;
    }
}
