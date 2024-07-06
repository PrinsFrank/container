<?php declare(strict_types=1);

namespace PrinsFrank\Container\Resolver;

use Closure;
use PrinsFrank\Container\Container;
use PrinsFrank\Container\Exception\InvalidServiceProviderException;
use PrinsFrank\Container\Exception\UnresolvableException;
use ReflectionMethod;
use ReflectionNamedType;

class ParameterResolver {
    public function __construct(
        private readonly Container $container,
    ) {
    }

    /**
     * @param class-string<object>|Closure $identifier
     * @throws InvalidServiceProviderException|UnresolvableException
     * @return array<mixed>
     */
    public function resolveParamsFor(string|Closure $identifier, string $methodName): array {
        $params = [];
        foreach ((new ReflectionMethod($identifier, $methodName))->getParameters() as $key => $parameterReflection) {
            $parameterType = $parameterReflection->getType();
            if ($parameterType instanceof ReflectionNamedType === false || (class_exists($parameterType->getName()) === false && interface_exists($parameterType->getName()) === false)) {
                throw new UnresolvableException(sprintf('Parameter %s for %s::%s is not resolvable as it doesn\'t have a type specified', $key, $identifier instanceof Closure ? $identifier::class : $identifier, $methodName));
            }

            $params[] = $this->container->get($parameterType->getName());
        }

        return $params;
    }
}
