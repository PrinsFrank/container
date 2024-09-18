<?php declare(strict_types=1);

namespace PrinsFrank\Container\Tests\Unit\ServiceProvider;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PrinsFrank\Container\Container;
use PrinsFrank\Container\Definition\DefinitionSet;
use PrinsFrank\Container\Exception\InvalidArgumentException;
use PrinsFrank\Container\Exception\MissingDefinitionException;
use PrinsFrank\Container\ServiceProvider\ContainerProvider;

#[CoversClass(ContainerProvider::class)]
class ContainerProviderTest extends TestCase {
    public function testProvides(): void {
        $containerProvider = new ContainerProvider();

        /** @phpstan-ignore argument.type */
        static::assertFalse($containerProvider->provides('foo'));
        static::assertTrue($containerProvider->provides(Container::class));
    }

    /** @throws InvalidArgumentException|MissingDefinitionException */
    public function testRegister(): void {
        (new ContainerProvider())
            ->register(Container::class, $resolvedSet = new DefinitionSet(), $container = new Container());

        static::assertSame(
            $container,
            $resolvedSet->get(Container::class, $container)
        );
    }
}
