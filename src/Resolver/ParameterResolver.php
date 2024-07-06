<?php declare(strict_types=1);

namespace PrinsFrank\Container\Resolver;

use Closure;
use PrinsFrank\Container\Container;
use PrinsFrank\Container\Exception\InvalidMethodException;
use PrinsFrank\Container\Exception\InvalidServiceProviderException;
use PrinsFrank\Container\Exception\UnresolvableException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;

class ParameterResolver {
    public function __construct(
        private readonly Container $container,
    ) {
    }

    /**
     * @param class-string<object> $identifier
     * @param non-empty-string $methodName
     * @return array<mixed>
     * @throws InvalidMethodException|InvalidServiceProviderException|UnresolvableException
     */
    public function resolveParamsForMethod(string $identifier, string $methodName): array {
        if (method_exists($methodName, $identifier) === false) {
            throw new InvalidMethodException(sprintf('Method "%s" does not exist on "%s"', $methodName, $identifier));
        }

        $params = [];
        foreach ((new ReflectionMethod($identifier, $methodName))->getParameters() as $key => $parameterReflection) {
            $parameterType = $parameterReflection->getType();
            if ($parameterType instanceof ReflectionNamedType === false || (class_exists($parameterType->getName()) === false && interface_exists($parameterType->getName()) === false)) {
                throw new UnresolvableException(sprintf('Parameter %s for %s::%s is not resolvable as it doesn\'t have a type specified', $key, $identifier, $methodName));
            }

            $params[] = $this->container->get($parameterType->getName());
        }

        return $params;
    }

    /**
     * @return array<mixed>
     * @throws InvalidMethodException|InvalidServiceProviderException|UnresolvableException
     */
    public function resolveParamsForClosure(Closure $closure): array {
        $params = [];
        foreach (($reflectionFunction = (new ReflectionFunction($closure)))->getParameters() as $key => $parameterReflection) {
            $parameterType = $parameterReflection->getType();
            if ($parameterType instanceof ReflectionNamedType === false || (class_exists($parameterType->getName()) === false && interface_exists($parameterType->getName()) === false)) {
                throw new UnresolvableException(sprintf('Parameter %s for closure at %s::%s is not resolvable as it doesn\'t have a type specified', $key, $reflectionFunction->getFileName(), $reflectionFunction->getStartLine()));
            }

            $params[] = $this->container->get($parameterType->getName());
        }

        return $params;
    }
}
