<?php declare(strict_types=1);

namespace PrinsFrank\Container\Tests\Unit\Definition\Item;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PrinsFrank\Container\Container;
use PrinsFrank\Container\Definition\Item\AbstractConcrete;
use PrinsFrank\Container\Exception\InvalidArgumentException;
use PrinsFrank\Container\Exception\InvalidServiceProviderException;
use PrinsFrank\Container\Exception\MissingDefinitionException;
use PrinsFrank\Container\Exception\UnresolvableException;
use PrinsFrank\Container\Resolver\ParameterResolver;
use PrinsFrank\Container\Tests\Fixtures\AbstractBImplementsInterfaceA;
use PrinsFrank\Container\Tests\Fixtures\ConcreteCExtendsAbstractBImplementsInterfaceA;
use PrinsFrank\Container\Tests\Fixtures\InterfaceA;
use stdClass;

#[CoversClass(AbstractConcrete::class)]
class AbstractConcreteTest extends TestCase {
    /** @throws InvalidArgumentException */
    public function testIsForThrowsExceptionOnNonAbstractClass(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument $identifier is expected to be a class-string for an interface or abstract class');
        new AbstractConcrete(ConcreteCExtendsAbstractBImplementsInterfaceA::class, fn () => null);
    }

    /** @throws InvalidArgumentException */
    public function testIsFor(): void {
        $abstractConcrete = new AbstractConcrete(InterfaceA::class, fn () => null);

        static::assertTrue($abstractConcrete->isFor(InterfaceA::class));
        static::assertFalse($abstractConcrete->isFor(AbstractBImplementsInterfaceA::class));
        static::assertFalse($abstractConcrete->isFor(ConcreteCExtendsAbstractBImplementsInterfaceA::class));
    }

    /** @throws InvalidServiceProviderException|MissingDefinitionException|UnresolvableException|InvalidArgumentException */
    public function testResolveThrowsExceptionWhenClosureReturnsInvalidType(): void {
        /** @phpstan-ignore argument.type */
        $abstractConcrete = new AbstractConcrete(InterfaceA::class, fn () => 42);

        $this->expectException(InvalidServiceProviderException::class);
        $this->expectExceptionMessage('Closure returned type "integer" instead of concrete for "' . InterfaceA::class . '"');
        $abstractConcrete->get($container = new Container(), new ParameterResolver($container));
    }

    /** @throws InvalidServiceProviderException|MissingDefinitionException|UnresolvableException|InvalidArgumentException */
    public function testResolveThrowsExceptionWhenClosureReturnsInvalidClassType(): void {
        $abstractConcrete = new AbstractConcrete(InterfaceA::class, fn () => new stdClass());

        $this->expectException(InvalidServiceProviderException::class);
        $this->expectExceptionMessage('Closure returned type "stdClass" instead of concrete for "' . InterfaceA::class . '"');
        $abstractConcrete->get($container = new Container(), new ParameterResolver($container));
    }

    /** @throws InvalidServiceProviderException|MissingDefinitionException|UnresolvableException|InvalidArgumentException */
    public function testResolve(): void {
        $concrete = new ConcreteCExtendsAbstractBImplementsInterfaceA();
        $abstractConcrete = new AbstractConcrete(InterfaceA::class, fn () => $concrete);

        static::assertSame(
            $concrete,
            $abstractConcrete->get($container = new Container(), new ParameterResolver($container)),
        );
    }

    /** @throws InvalidServiceProviderException|MissingDefinitionException|UnresolvableException|InvalidArgumentException */
    public function testResolveAllowsNullValue(): void {
        $abstractConcrete = new AbstractConcrete(InterfaceA::class, fn () => null);

        static::assertNull($abstractConcrete->get($container = new Container(), new ParameterResolver($container)));
    }
}
