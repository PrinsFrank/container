<?php declare(strict_types=1);

namespace PrinsFrank\Container\Tests\Unit\Definition\Item;

use PHPUnit\Framework\TestCase;
use PrinsFrank\Container\Container;
use PrinsFrank\Container\Definition\Item\Singleton;
use PrinsFrank\Container\Exception\InvalidArgumentException;
use PrinsFrank\Container\Exception\ShouldNotHappenException;
use PrinsFrank\Container\Tests\Fixtures\AbstractBImplementsInterfaceA;
use PrinsFrank\Container\Tests\Fixtures\ConcreteCExtendsAbstractBImplementsInterfaceA;
use PrinsFrank\Container\Tests\Fixtures\InterfaceA;
use stdClass;

class SingletonTest extends TestCase {
    /** @throws InvalidArgumentException */
    public function testIsFor(): void {
        $singleton = new Singleton(ConcreteCExtendsAbstractBImplementsInterfaceA::class, fn () => new ConcreteCExtendsAbstractBImplementsInterfaceA());

        static::assertFalse($singleton->isFor(AbstractBImplementsInterfaceA::class));
        static::assertFalse($singleton->isFor(InterfaceA::class));
        static::assertTrue($singleton->isFor(ConcreteCExtendsAbstractBImplementsInterfaceA::class));
    }

    /** @throws InvalidArgumentException */
    public function testGetThrowsExceptionOnInvalidReturnTypeContainer(): void {
        $closureNew = fn () => new ConcreteCExtendsAbstractBImplementsInterfaceA();
        $singleton = new Singleton(ConcreteCExtendsAbstractBImplementsInterfaceA::class, $closureNew);

        $container = $this->createMock(Container::class);
        $container->expects(self::once())->method('invoke')->with($closureNew)->willReturn(new stdClass());

        $this->expectException(ShouldNotHappenException::class);
        $this->expectExceptionMessage('Container returned type "object" instead of "' . ConcreteCExtendsAbstractBImplementsInterfaceA::class . '"');
        $singleton->get($container);
    }

    /** @throws InvalidArgumentException */
    public function testGetResolvesNewInstance(): void {
        $new = new ConcreteCExtendsAbstractBImplementsInterfaceA();
        $closureNew = fn () => $new;
        $singleton = new Singleton(ConcreteCExtendsAbstractBImplementsInterfaceA::class, $closureNew);

        $container = $this->createMock(Container::class);
        $container->expects(self::once())->method('invoke')->with($closureNew)->willReturn($new);

        static::assertSame($new, $singleton->get($container));
        static::assertSame($new, $singleton->get($container));
        static::assertSame($new, $singleton->get($container));
    }
}
