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
            public function register(string $identifier, DefinitionSet $resolvedSet): void {
                $resolvedSet->add(new Singleton(DateTime::class, fn () => new DateTime('2001-01-01 01:01:01')));
            }
        });

        $firstResolveResult = $container->get(DateTime::class);
        static::assertInstanceOf(DateTime::class, $firstResolveResult);
        static::assertSame('2001-01-01 01:01:01', $firstResolveResult->format('Y-m-d H:i:s'));

        $secondResolveResult = $container->get(DateTime::class);
        static::assertInstanceOf(DateTime::class, $secondResolveResult);
        static::assertSame('2001-01-01 01:01:01', $secondResolveResult->format('Y-m-d H:i:s'));
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
            public function register(string $identifier, DefinitionSet $resolvedSet): void {
                $resolvedSet->add(new AbstractConcrete(DateTimeInterface::class, fn () => new DateTime('2001-01-01 01:01:01')));
            }
        });

        $firstResolveResult = $container->get(DateTimeInterface::class);
        static::assertInstanceOf(DateTime::class, $firstResolveResult);
        static::assertSame('2001-01-01 01:01:01', $firstResolveResult->format('Y-m-d H:i:s'));

        $secondResolveResult = $container->get(DateTimeInterface::class);
        static::assertInstanceOf(DateTime::class, $secondResolveResult);
        static::assertSame('2001-01-01 01:01:01', $secondResolveResult->format('Y-m-d H:i:s'));
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
            public function register(string $identifier, DefinitionSet $resolvedSet): void {
                $resolvedSet->add(new Concrete(DateTime::class, fn () => new DateTime('2001-01-01 01:01:01')));
            }
        });

        $firstResolveResult = $container->get(DateTime::class);
        static::assertInstanceOf(DateTime::class, $firstResolveResult);
        static::assertSame('2001-01-01 01:01:01', $firstResolveResult->format('Y-m-d H:i:s'));

        $secondResolveResult = $container->get(DateTime::class);
        static::assertInstanceOf(DateTime::class, $secondResolveResult);
        static::assertSame('2001-01-01 01:01:01', $secondResolveResult->format('Y-m-d H:i:s'));
        static::assertNotSame($firstResolveResult, $secondResolveResult);
        static::assertEquals($firstResolveResult, $secondResolveResult);
    }
}
