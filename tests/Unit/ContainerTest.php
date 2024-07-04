<?php declare(strict_types=1);

namespace PrinsFrank\Container\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PrinsFrank\Container\Container;
use PrinsFrank\Container\Exception\InvalidServiceProviderException;
use PrinsFrank\Container\Exception\UnresolvableException;

class ContainerTest extends TestCase {
    /** @throws UnresolvableException|InvalidServiceProviderException */
    public function testInvokeThrowsExceptionOnParameterWithoutType(): void {
        $container = new Container();

        $closure = fn ($foo) => null;
        $this->expectException(UnresolvableException::class);
        $this->expectExceptionMessage('Parameter 0 for Closure::__invoke is not resolvable as it doesn\'t have a type specified');
        $container->resolveParamsFor($closure, '__invoke');
    }
}
