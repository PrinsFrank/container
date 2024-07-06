<?php
declare(strict_types=1);

namespace PrinsFrank\Container\Tests\Integration;

use DateTime;
use DateTimeInterface;
use Override;
use PHPUnit\Framework\TestCase;
use PrinsFrank\Container\Container;
use PrinsFrank\Container\Definition\DefinitionSet;
use PrinsFrank\Container\Definition\Item\AbstractConcrete;
use PrinsFrank\Container\Definition\Item\Concrete;
use PrinsFrank\Container\Definition\Item\Singleton;
use PrinsFrank\Container\Exception\ContainerException;
use PrinsFrank\Container\ServiceProvider\ServiceProviderInterface;

class ContainerTest extends TestCase {
    /** @throws ContainerException */
    public function testResolvesSingleton(): void {
        $container = new Container();
        $container->addServiceProvider(new class () implements ServiceProviderInterface {
            #[Override]
            public function provides(string $identifier): bool {
                return $identifier === DateTime::class;
            }

            /** @throws ContainerException */
            #[Override]
            public function register(DefinitionSet $resolvedSet): void {
                $resolvedSet->add(new Singleton(DateTime::class, fn () => new DateTime()));
            }
        });

        $firstResolveResult = $container->get(DateTime::class);
        static::assertInstanceOf(DateTime::class, $firstResolveResult);

        $secondResolveResult = $container->get(DateTime::class);
        static::assertInstanceOf(DateTime::class, $secondResolveResult);
        static::assertSame($firstResolveResult, $secondResolveResult);
    }

    /** @throws ContainerException */
    public function testResolvesAbstractConcrete(): void {
        $container = new Container();
        $container->addServiceProvider(new class () implements ServiceProviderInterface {
            #[Override]
            public function provides(string $identifier): bool {
                return $identifier === DateTimeInterface::class;
            }

            /** @throws ContainerException */
            #[Override]
            public function register(DefinitionSet $resolvedSet): void {
                $resolvedSet->add(new AbstractConcrete(DateTimeInterface::class, fn () => new DateTime('2001-01-01 01:01:01')));
            }
        });

        $firstResolveResult = $container->get(DateTimeInterface::class);
        static::assertInstanceOf(DateTime::class, $firstResolveResult);

        $secondResolveResult = $container->get(DateTimeInterface::class);
        static::assertInstanceOf(DateTime::class, $secondResolveResult);
        static::assertNotSame($firstResolveResult, $secondResolveResult);
        static::assertEquals($firstResolveResult, $secondResolveResult);
    }

    /** @throws ContainerException */
    public function testResolvesConcrete(): void {
        $container = new Container();
        $container->addServiceProvider(new class () implements ServiceProviderInterface {
            #[Override]
            public function provides(string $identifier): bool {
                return $identifier === DateTime::class;
            }

            /** @throws ContainerException */
            #[Override]
            public function register(DefinitionSet $resolvedSet): void {
                $resolvedSet->add(new Concrete(DateTime::class, fn () => new DateTime('2001-01-01 01:01:01')));
            }
        });

        $firstResolveResult = $container->get(DateTime::class);
        static::assertInstanceOf(DateTime::class, $firstResolveResult);

        $secondResolveResult = $container->get(DateTime::class);
        static::assertInstanceOf(DateTime::class, $secondResolveResult);
        static::assertNotSame($firstResolveResult, $secondResolveResult);
        static::assertEquals($firstResolveResult, $secondResolveResult);
    }
}
