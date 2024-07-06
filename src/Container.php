<?php
declare(strict_types=1);

namespace PrinsFrank\Container;

use Override;
use PrinsFrank\Container\Exception\InvalidServiceProviderException;
use PrinsFrank\Container\Exception\UnresolvableException;
use PrinsFrank\Container\Definition\DefinitionSet;
use PrinsFrank\Container\Resolver\ParameterResolver;
use PrinsFrank\Container\ServiceProvider\ContainerProvider;
use PrinsFrank\Container\ServiceProvider\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface {
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

        if ($this->autowire === true) {
            return new $id(...$this->resolvedSet->parameterResolver->resolveParamsFor($id, '__construct'));
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

    public function addServiceProvider(ServiceProviderInterface $serviceProvider): void {
        $this->serviceProvider[] = $serviceProvider;
    }

    public function addServiceProviders(ServiceProviderInterface... $serviceProviders): void {
        foreach ($serviceProviders as $serviceProvider) {
            $this->addServiceProvider($serviceProvider);
        }
    }
}
