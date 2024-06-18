<?php
declare(strict_types=1);

namespace PrinsFrank\Container;

use Override;
use PrinsFrank\Container\Exception\ContainerException;
use PrinsFrank\Container\Exception\InvalidServiceProviderException;
use PrinsFrank\Container\Exception\UnresolvableException;
use PrinsFrank\Container\Definition\DefinitionSet;
use PrinsFrank\Container\ServiceProvider\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use ReflectionMethod;

final class Container implements ContainerInterface {
    /** @var list<ServiceProviderInterface> */
    private array $serviceProvider = [];
    private readonly DefinitionSet $resolvedSet;

    public function __construct() {
        $this->resolvedSet = new DefinitionSet();
    }

    /**
     * @template T of object
     * @param class-string<T> $id
     * @throws ContainerException
     * @return T
     */
    #[Override]
    public function get(string $id): object {
        if ($this->resolvedSet->has($id)) {
            return $this->resolvedSet->get($id, $this);
        }

        foreach ($this->serviceProvider as $serviceProvider) {
            if ($serviceProvider->provides($id) === false) {
                continue;
            }

            $serviceProvider->register($this->resolvedSet);
            if ($this->resolvedSet->has($id) === false) {
                throw new InvalidServiceProviderException($serviceProvider::class);
            }

            return $this->resolvedSet->get($id, $this);
        }

        throw new UnresolvableException();
    }

    /** @param class-string<object> $id */
    #[Override]
    public function has(string $id): bool {
        if ($this->resolvedSet->has($id)) {
            return true;
        }

        foreach ($this->serviceProvider as $serviceProvider) {
            if ($serviceProvider->provides($id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @template T of object
     * @param class-string<T>
     * @return T
     */
    public function construct(string $identifier): object {
        return $this->call($identifier, '__construct');
    }

    public function invoke(callable|object $identifier): mixed {
        return $this->call($identifier, '__invoke');
    }

    /**
     * @param class-string<object>|callable|object $identifier
     * @throws UnresolvableException
     */
    public function call(string|callable|object $identifier, string $methodName): mixed {
        $params = [];
        foreach ((new ReflectionMethod($identifier, $methodName))->getParameters() as $parameterReflection) {
            $parameterType = $parameterReflection->getType();
            if ($parameterType === null || $this->has($parameterType->__toString())) {
                throw new UnresolvableException(sprintf('Parameter %s for %s::%s is not resolvable', $parameterType->getName(), $identifier, $methodName));
            }

            $params[] = $this->get($identifier);
        }

        return $identifier->{$methodName}(...$params);
    }

    public function addServiceProvider(ServiceProviderInterface $serviceProvider): void {
        $this->serviceProvider[] = $serviceProvider;
    }

    public function addServiceProviders(ServiceProviderInterface... $serviceProviders): void {
        foreach ($serviceProviders as $serviceProvider) {
            $this->addServiceProvider($serviceProvider);
        }
    }
}
