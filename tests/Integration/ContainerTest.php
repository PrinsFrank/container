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
use PrinsFrank\Container\Exception\UnresolvableException;
use PrinsFrank\Container\ServiceProvider\ServiceProviderInterface;
use PrinsFrank\Container\Tests\Fixtures\ConstructorOptionalInterfaceA;
use PrinsFrank\Container\Tests\Fixtures\ConstructorRequiredInterfaceA;
use PrinsFrank\Container\Tests\Fixtures\InterfaceA;

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
            public function register(string $identifier, DefinitionSet $resolvedSet, Container $container): void {
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
            public function register(string $identifier, DefinitionSet $resolvedSet, Container $container): void {
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
            public function register(string $identifier, DefinitionSet $resolvedSet, Container $container): void {
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

    /** @throws ContainerException */
    public function testDoesntResolvesNonConfiguredRequiredInterface(): void {
        $container = new Container();

        static::expectException(UnresolvableException::class);
        static::expectExceptionMessage(sprintf('Id "%s" is not resolvable', InterfaceA::class));
        $container->get(ConstructorRequiredInterfaceA::class);
    }

    /** @throws ContainerException */
    public function testResolvesNonConfiguredOptionalInterface(): void {
        $container = new Container();

        $resolvedClass = $container->get(ConstructorOptionalInterfaceA::class);
        static::assertNotNull($resolvedClass);
        static::assertNull($resolvedClass->interfaceA);
    }
}
