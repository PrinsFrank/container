<?php declare(strict_types=1);

namespace PrinsFrank\Container\Tests\Unit\Definition\Item;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PrinsFrank\Container\Container;
use PrinsFrank\Container\Definition\Item\Concrete;
use PrinsFrank\Container\Exception\InvalidArgumentException;
use PrinsFrank\Container\Exception\InvalidServiceProviderException;
use PrinsFrank\Container\Exception\MissingDefinitionException;
use PrinsFrank\Container\Exception\UnresolvableException;
use PrinsFrank\Container\Tests\Fixtures\AbstractBImplementsInterfaceA;
use PrinsFrank\Container\Tests\Fixtures\ConcreteCExtendsAbstractBImplementsInterfaceA;
use PrinsFrank\Container\Tests\Fixtures\InterfaceA;
use stdClass;

#[CoversClass(Concrete::class)]
class ConcreteTest extends TestCase {
    /** @throws InvalidArgumentException */
    public function testIsForThrowsExceptionOnAbstractClass(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument $identifier is expected to be a class-string for a concrete class');
        new Concrete(AbstractBImplementsInterfaceA::class, fn () => null);
    }

    /** @throws InvalidArgumentException */
    public function testIsForThrowsExceptionOnInterface(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument $identifier is expected to be a class-string for a concrete class');
        new Concrete(InterfaceA::class, fn () => null);
    }

    /** @throws InvalidArgumentException */
    public function testIsFor(): void {
        $abstractConcrete = new Concrete(ConcreteCExtendsAbstractBImplementsInterfaceA::class, fn () => null);

        static::assertFalse($abstractConcrete->isFor(InterfaceA::class));
        static::assertFalse($abstractConcrete->isFor(AbstractBImplementsInterfaceA::class));
        static::assertTrue($abstractConcrete->isFor(ConcreteCExtendsAbstractBImplementsInterfaceA::class));
    }

    /** @throws InvalidServiceProviderException|MissingDefinitionException|UnresolvableException|InvalidArgumentException */
    public function testResolveThrowsExceptionWhenClosureReturnsInvalidType(): void {
        /** @phpstan-ignore argument.type */
        $abstractConcrete = new Concrete(ConcreteCExtendsAbstractBImplementsInterfaceA::class, fn () => 42);

        $this->expectException(InvalidServiceProviderException::class);
        $this->expectExceptionMessage('Closure returned type "integer" instead of "' . ConcreteCExtendsAbstractBImplementsInterfaceA::class . '"');
        $abstractConcrete->get(new Container());
    }

    /** @throws InvalidServiceProviderException|MissingDefinitionException|UnresolvableException|InvalidArgumentException */
    public function testResolveThrowsExceptionWhenClosureReturnsInvalidClassType(): void {
        $abstractConcrete = new Concrete(ConcreteCExtendsAbstractBImplementsInterfaceA::class, fn () => new stdClass());

        $this->expectException(InvalidServiceProviderException::class);
        $this->expectExceptionMessage('Closure returned type "stdClass" instead of "' . ConcreteCExtendsAbstractBImplementsInterfaceA::class . '"');
        $abstractConcrete->get(new Container());
    }

    /** @throws InvalidServiceProviderException|MissingDefinitionException|UnresolvableException|InvalidArgumentException */
    public function testResolve(): void {
        $concrete = new ConcreteCExtendsAbstractBImplementsInterfaceA();
        $abstractConcrete = new Concrete(ConcreteCExtendsAbstractBImplementsInterfaceA::class, fn () => $concrete);

        static::assertSame(
            $concrete,
            $abstractConcrete->get(new Container()),
        );
    }

    /** @throws InvalidServiceProviderException|MissingDefinitionException|UnresolvableException|InvalidArgumentException */
    public function testResolveAllowsNullValue(): void {
        $abstractConcrete = new Concrete(ConcreteCExtendsAbstractBImplementsInterfaceA::class, fn () => null);

        static::assertNull($abstractConcrete->get(new Container()));
    }
}
