<?php declare(strict_types=1);

namespace PrinsFrank\Container\Tests\Unit\Definition\Item;

use PHPUnit\Framework\TestCase;
use PrinsFrank\Container\Container;
use PrinsFrank\Container\Definition\Item\AbstractConcrete;
use PrinsFrank\Container\Exception\InvalidArgumentException;
use PrinsFrank\Container\Exception\ShouldNotHappenException;
use PrinsFrank\Container\Tests\Fixtures\AbstractBImplementsInterfaceA;
use PrinsFrank\Container\Tests\Fixtures\ConcreteCExtendsAbstractBImplementsInterfaceA;
use PrinsFrank\Container\Tests\Fixtures\InterfaceA;
use stdClass;

class AbstractConcreteTest extends TestCase {
    /** @throws InvalidArgumentException */
    public function testConstructThrowsExceptionWhenInterfaceDoesntExist(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument $identifier is expected to be a class-string for an interface');
        /** @phpstan-ignore argument.type, argument.type */
        new AbstractConcrete('foo', fn () => null);
    }

    /** @throws InvalidArgumentException */
    public function testIsFor(): void {
        $abstractConcrete = new AbstractConcrete(InterfaceA::class, fn () => new ConcreteCExtendsAbstractBImplementsInterfaceA());

        static::assertFalse($abstractConcrete->isFor(AbstractBImplementsInterfaceA::class));
        static::assertFalse($abstractConcrete->isFor(ConcreteCExtendsAbstractBImplementsInterfaceA::class));
        static::assertTrue($abstractConcrete->isFor(InterfaceA::class));
    }

    /** @throws InvalidArgumentException */
    public function testGetThrowsExceptionOnInvalidReturnTypeContainer(): void {
        $closureNew = fn () => new ConcreteCExtendsAbstractBImplementsInterfaceA();
        $abstractConcrete = new AbstractConcrete(InterfaceA::class, $closureNew);

        $container = $this->createMock(Container::class);
        $container->expects(self::once())->method('invoke')->with($closureNew)->willReturn(new stdClass());

        $this->expectException(ShouldNotHappenException::class);
        $this->expectExceptionMessage('Container returned type "object" instead of concrete for "' . InterfaceA::class . '"');
        $abstractConcrete->get($container);
    }

    /** @throws InvalidArgumentException */
    public function testGetResolvesNewInstance(): void {
        $new = new ConcreteCExtendsAbstractBImplementsInterfaceA();
        $closureNew = fn () => $new;
        $abstractConcrete = new AbstractConcrete(InterfaceA::class, $closureNew);

        $container = $this->createMock(Container::class);
        $container->expects(self::once())->method('invoke')->with($closureNew)->willReturn($new);

        static::assertSame($new, $abstractConcrete->get($container));
    }
}
