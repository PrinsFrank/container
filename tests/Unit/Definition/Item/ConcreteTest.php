<?php declare(strict_types=1);

namespace PrinsFrank\Container\Tests\Unit\Definition\Item;

use PHPUnit\Framework\TestCase;
use PrinsFrank\Container\Container;
use PrinsFrank\Container\Definition\Item\Concrete;
use PrinsFrank\Container\Exception\InvalidArgumentException;
use PrinsFrank\Container\Exception\InvalidServiceProviderException;
use PrinsFrank\Container\Exception\ShouldNotHappenException;
use PrinsFrank\Container\Exception\UnresolvableException;
use PrinsFrank\Container\Tests\Fixtures\AbstractBImplementsInterfaceA;
use PrinsFrank\Container\Tests\Fixtures\ConcreteCExtendsAbstractBImplementsInterfaceA;
use PrinsFrank\Container\Tests\Fixtures\InterfaceA;
use stdClass;

class ConcreteTest extends TestCase {
    /** @throws InvalidArgumentException */
    public function testConstructThrowsExceptionWhenInterfaceDoesntExist(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument $identifier is expected to be a class-string for a concrete class');
        /** @phpstan-ignore argument.type, argument.type */
        new Concrete('foo', fn () => null);
    }

    /** @throws InvalidArgumentException */
    public function testIsFor(): void {
        $concrete = new Concrete(ConcreteCExtendsAbstractBImplementsInterfaceA::class, fn () => new ConcreteCExtendsAbstractBImplementsInterfaceA());

        static::assertFalse($concrete->isFor(AbstractBImplementsInterfaceA::class));
        static::assertFalse($concrete->isFor(InterfaceA::class));
        static::assertTrue($concrete->isFor(ConcreteCExtendsAbstractBImplementsInterfaceA::class));
    }

    /** @throws ShouldNotHappenException|UnresolvableException|InvalidServiceProviderException|InvalidArgumentException */
    public function testGetResolvesNewInstance(): void {
        $new = new ConcreteCExtendsAbstractBImplementsInterfaceA();
        $closureNew = fn () => $new;
        $concrete = new Concrete(ConcreteCExtendsAbstractBImplementsInterfaceA::class, $closureNew);

        $container = $this->createMock(Container::class);
        $container->expects(self::once())->method('resolveParamsFor')->with($closureNew)->willReturn([]);

        static::assertSame($new, $concrete->get($container));
    }
}
