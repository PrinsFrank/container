<?php
declare(strict_types=1);

namespace PrinsFrank\Container;

use Override;
use PrinsFrank\Container\Exception\InvalidMethodException;
use PrinsFrank\Container\Exception\InvalidServiceProviderException;
use PrinsFrank\Container\Exception\MissingDefinitionException;
use PrinsFrank\Container\Exception\UnresolvableException;
use PrinsFrank\Container\Definition\DefinitionSet;
use PrinsFrank\Container\Resolver\ParameterResolver;
use PrinsFrank\Container\ServiceProvider\ContainerProvider;
use PrinsFrank\Container\ServiceProvider\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

final class Container implements ContainerInterface {
    /** @var list<ServiceProviderInterface> */
    private array $serviceProvider = [];
    private readonly DefinitionSet $resolvedSet;

    public function __construct(bool $allowSelfResolve = true, private readonly bool $autowire = true) {
        $this->resolvedSet = new DefinitionSet($this, new ParameterResolver($this));
        if ($allowSelfResolve === true) {
            $this->addServiceProvider(new ContainerProvider());
        }
    }

    /**
     * @template T of object
     * @param class-string<T> $id
     * @throws UnresolvableException|InvalidServiceProviderException|InvalidMethodException|MissingDefinitionException
     * @return T|null
     *
     * @phpstan-ignore method.childParameterType
     */
    #[Override]
    public function get(string $id): ?object {
        if ($this->resolvedSet->has($id)) {
            return $this->resolvedSet->get($id, $this);
        }

        foreach ($this->serviceProvider as $serviceProvider) {
            if ($serviceProvider->provides($id) === false) {
                continue;
            }

            $serviceProvider->register($id, $this->resolvedSet);
            if ($this->resolvedSet->has($id) === false) {
                throw new InvalidServiceProviderException(sprintf('Provider "%s" said it would provide "%s" but after registering it is not resolvable', $serviceProvider::class, $id));
            }

            return $this->resolvedSet->get($id, $this);
        }

        if ($this->autowire === true) {
            return method_exists($id, '__construct') === false ? new $id() : new $id(...$this->resolvedSet->parameterResolver->resolveParamsForMethod($id, '__construct'));
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
        if ($this->autowire === true) {
            return true;
        }

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

    public function addServiceProvider(ServiceProviderInterface $serviceProvider): void {
        $this->serviceProvider[] = $serviceProvider;
    }

    public function addServiceProviders(ServiceProviderInterface... $serviceProviders): void {
        foreach ($serviceProviders as $serviceProvider) {
            $this->addServiceProvider($serviceProvider);
        }
    }
}
