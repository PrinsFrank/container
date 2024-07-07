<?php declare(strict_types=1);

namespace PrinsFrank\Container\Tests\Unit\Definition;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PrinsFrank\Container\Container;
use PrinsFrank\Container\Definition\DefinitionSet;
use PrinsFrank\Container\Resolver\ParameterResolver;

#[CoversClass(DefinitionSet::class)]
class DefinitionSetTest extends TestCase {
    public function testConstruct(): void {
        $definitionSet = new DefinitionSet($container = new Container(), $parameterResolver = new ParameterResolver($container));

        static::assertSame($container, $definitionSet->forContainer);
        static::assertSame($parameterResolver, $definitionSet->parameterResolver);
    }
}
