<?php declare(strict_types=1);

namespace PrinsFrank\Container\Resolver;

use Closure;
use PrinsFrank\Container\Container;
use PrinsFrank\Container\Exception\InvalidArgumentException;
use PrinsFrank\Container\Exception\InvalidServiceProviderException;
use PrinsFrank\Container\Exception\MissingDefinitionException;
use PrinsFrank\Container\Exception\UnresolvableException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;

final class ParameterResolver {
    public function __construct(
        private readonly Container $container,
    ) {
    }

    /**
     * @param class-string<object> $identifier
     * @param non-empty-string $methodName
     */
    public function canResolveParamsForMethod(string $identifier, string $methodName): bool {
        if (method_exists($identifier, $methodName) === false) {
            throw new InvalidArgumentException(sprintf('Method "%s" does not exist on "%s"', $methodName, $identifier));
        }

        foreach ((new ReflectionMethod($identifier, $methodName))->getParameters() as $parameterReflection) {
            $parameterType = $parameterReflection->getType();
            if ($parameterType instanceof ReflectionNamedType === false || (class_exists($parameterType->getName()) === false && interface_exists($parameterType->getName()) === false)) {
                return false;
            }

            if ($this->container->has($parameterType->getName()) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param class-string<object> $identifier
     * @param non-empty-string $methodName
     * @throws InvalidServiceProviderException|UnresolvableException|MissingDefinitionException
     * @return array<mixed>
     */
    public function resolveParamsForMethod(string $identifier, string $methodName): array {
        if (method_exists($identifier, $methodName) === false) {
            throw new InvalidArgumentException(sprintf('Method "%s" does not exist on "%s"', $methodName, $identifier));
        }

        $params = [];
        foreach ((new ReflectionMethod($identifier, $methodName))->getParameters() as $key => $parameterReflection) {
            $parameterType = $parameterReflection->getType();
            if ($parameterType instanceof ReflectionNamedType === false || (class_exists($parameterType->getName()) === false && interface_exists($parameterType->getName()) === false)) {
                throw new UnresolvableException(sprintf('Parameter %s for %s::%s is not resolvable as it doesn\'t have a type specified', $key, $identifier, $methodName));
            }

            $params[] = $this->optionallyResolve($parameterType->getName(), $parameterType->allowsNull());
        }

        return $params;
    }

    /**
     * @throws InvalidServiceProviderException|UnresolvableException|MissingDefinitionException
     * @return array<mixed>
     */
    public function resolveParamsForClosure(Closure $closure): array {
        $params = [];
        foreach (($reflectionFunction = (new ReflectionFunction($closure)))->getParameters() as $key => $parameterReflection) {
            $parameterType = $parameterReflection->getType();
            if ($parameterType instanceof ReflectionNamedType === false || (class_exists($parameterType->getName()) === false && interface_exists($parameterType->getName()) === false)) {
                throw new UnresolvableException(sprintf('Parameter %s for closure at %s::%s is not resolvable as it doesn\'t have a type specified', $key, $reflectionFunction->getFileName(), $reflectionFunction->getStartLine()));
            }

            $params[] = $this->optionallyResolve($parameterType->getName(), $parameterType->allowsNull());
        }

        return $params;
    }

    /**
     * @template T of object
     * @param class-string<T> $identifier
     * @throws InvalidServiceProviderException|UnresolvableException|MissingDefinitionException
     * @return T|null
     */
    private function optionallyResolve(string $identifier, bool $allowsNull): ?object {
        if ($allowsNull === false) {
            return $this->container->get($identifier);
        }

        try {
            return $this->container->get($identifier);
        } catch (UnresolvableException $e) {
            return null;
        }
    }
}
