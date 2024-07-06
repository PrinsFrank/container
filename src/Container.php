<?php
declare(strict_types=1);

namespace PrinsFrank\Container;

use Closure;
use Override;
use PrinsFrank\Container\Definition\Item\Singleton;
use PrinsFrank\Container\Exception\InvalidServiceProviderException;
use PrinsFrank\Container\Exception\UnresolvableException;
use PrinsFrank\Container\Definition\DefinitionSet;
use PrinsFrank\Container\ServiceProvider\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use ReflectionMethod;
use ReflectionNamedType;

class Container implements ContainerInterface {
    /** @var list<ServiceProviderInterface> */
    private array $serviceProvider = [];
    private readonly DefinitionSet $resolvedSet;

    public function __construct() {
        $this->resolvedSet = new DefinitionSet();
    }

    public function addServiceProvider(ServiceProviderInterface $serviceProvider): void {
        $this->serviceProvider[] = $serviceProvider;
    }

    public function addServiceProviders(ServiceProviderInterface... $serviceProviders): void {
        foreach ($serviceProviders as $serviceProvider) {
            $this->addServiceProvider($serviceProvider);
        }
    }

    /**
     * @template T of object
     * @param class-string<T> $id
     * @throws UnresolvableException|InvalidServiceProviderException
     * @return T
     *
     * @phpstan-ignore method.childParameterType
     */
    #[Override]
    public function get(string $id): object {
        if (($resolvedItem = $this->resolvedSet->get($id, $this)) !== null) {
            return $resolvedItem;
        }

        foreach ($this->serviceProvider as $serviceProvider) {
            if ($serviceProvider->provides($id) === false) {
                continue;
            }

            $serviceProvider->register($this->resolvedSet);
            if (($resolvedItem = $this->resolvedSet->get($id, $this)) === null) {
                throw new InvalidServiceProviderException($serviceProvider::class);
            }

            return $resolvedItem;
        }

        throw new UnresolvableException(sprintf('Id "%s" is not resolvable', $id));
    }

    /**
     * @param class-string<object> $id
     *
     * @phpstan-ignore method.childParameterType
     */
    #[Override]
    public function has(string $id): bool {
        if ($this->resolvedSet->get($id, $this) !== null) {
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
     * @param class-string<T> $identifier
     * @throws UnresolvableException|InvalidServiceProviderException
     * @return T
     */
    public function construct(string $identifier): object {
        return new $identifier(...$this->resolveParamsFor($identifier, '__construct'));
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

            $params[] = $this->get($parameterType->getName());
        }

        return $params;
    }
}
