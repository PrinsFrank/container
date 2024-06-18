<?php
declare(strict_types=1);

namespace PrinsFrank\Container\Definition\Item;

use Closure;
use Override;
use PrinsFrank\Container\Exception\InvalidArgumentException;

/** @implements Definition<object> */
final readonly class Concrete implements Definition {
    /** @throws InvalidArgumentException */
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
        return is_a($this->identifier, $identifier, true);
    }

    #[Override]
    public function get(): object {
        return $this->new->__invoke();
    }
}
