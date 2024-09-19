<picture>
    <source srcset="https://github.com/PrinsFrank/container/raw/main/docs/images/banner_dark.png" media="(prefers-color-scheme: dark)">
    <img src="https://github.com/PrinsFrank/container/raw/main/docs/images/banner_light.png" alt="Banner">
</picture>

# Container

[![GitHub](https://img.shields.io/github/license/prinsfrank/container)](https://github.com/PrinsFrank/container/blob/main/LICENSE)
[![PHP Version Support](https://img.shields.io/packagist/php-v/prinsfrank/container)](https://github.com/PrinsFrank/container/blob/main/composer.json)
[![codecov](https://codecov.io/gh/PrinsFrank/container/branch/main/graph/badge.svg?token=9O3VB563MU)](https://codecov.io/gh/PrinsFrank/container)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg?style=flat)](https://github.com/PrinsFrank/container/blob/main/phpstan.neon)

**A strict container implementation**

## Setup

> **Note**
> Make sure you are running PHP 8.1 or higher to use this package

To start right away, run the following command in your composer project;

```composer require prinsfrank/container```

Or for development only;

```composer require prinsfrank/container --dev```

# Why this container package?

## 1. Dynamic Concrete Bindings

At the entrypoints of an application - like controllers or commands - There might be instance where you'd want to inject a wide variety of different classes.

In most containers, If you'd want to automatically resolve [all requests for example](https://github.com/PrinsFrank/object-resolver), you'd have to register each individual concrete class in a service provider:

```php
private const REQUEST_CLASSES = [
    // list of classes
];

public function provides(string $id): bool {
    return in_array($id, self::REQUEST_CLASSES, true);
}

public function register(mixed ...): void {
    foreach (self::REQUEST_CLASSES as $requestClass) {
        $this->container->add(
            $requestClass, 
            fn() => $objectResolver->resolve($requestClass)
        )
    }
}
```

This means, that even if the provider was deferred and all requests were not added to the container at container initialization, as soon as one request gets requested and the service provider is booted, all requests will be registered. This increase the memory footprint, but also performs unnecessary actions, as most likely we will only ever use one request per execution cycle.

Instead, in this package, it is possible to add Dynamic Concrete Bindings. The identifier that was requested by the consumer is passed to the service provider. Not only to the `provides` method, but also to the `register` method:

```php
final class RequestDataProvider implements ServiceProviderInterface {
    public function provides(string $identifier): bool {
        return is_a($identifier, RequestData::class, true);
    }

    public function register(string $identifier, DefinitionSet $resolvedSet): void {
        $resolvedSet->add(
            new Concrete(
                $identifier,
                fn (ObjectResolver $objectResolver) => $objectResolver->resolve($identifier),
            )
        );
    }
}
```

As you can see above, as long as there is an interface that is implemented by all the requests, it's now possible to resolve all requests without needing to manually keep a list of what request classes exist, and without the need to register tens, hundreds or even thousands of mostly unneeded classes in the container.

## 2. No opaque entry identifiers, only class-strings

Unlike other containers, this package is fully strict with service entry identifiers. Instead of **any** string as an entry identifier, this package only allows FQNs of existing classes and interfaces. A simple `'db'` string to identify a `\DatabaseConnection` instance is therefore not allowed. This means that this package is very static-analysis friendly, as it's not necessary to boot the container in static analysis to determine the type of object that is located by an entry identifier.

This does slightly diverge from the PSR-11 standard, $1.1.1 Entry Identifiers, where the [following is specified](https://www.php-fig.org/psr/psr-11/#111-entry-identifiers):

> An entry identifier is any PHP-legal string of at least one character that uniquely identifies an item within a container. An entry identifier is an opaque string, so callers SHOULD NOT assume that the structure of the string carries any semantic meaning.

In the context of **this** container package, the following is true instead:

> An entry identifier is any class-string for a class or interface. Callers can assume that the returned object for an entry identifier that is a class-string of an interface implements that interface, a class-string of an abstract class results in an object that extends that abstract class and a class-string of a concrete class is an instance of that concrete class or a child thereof.

As most entry identifiers are already class-strings, this is not a big chance, but it makes things a lot more elegant. All other specification points from PSR-11 are still valid.

## 3. DI'ed services in ServiceProvider closure

Let's assume we have a service (A) can only be partially resolved because it has two dependencies, one that can be resolved by the container (B) and one that cannot (C).

When autowiring is enabled, you could simply add a definition for the service that cannot be resolved (C) so that service A can also be resolved. But if the argument is not a service or should not be universally available, you can also request the services that are available as arguments for the service closure:

```php
class FooApiServiceProvider implements ServiceProviderInterface {
    public function provides(string $identifier): bool {
        return $identifier === FooApi::class;
    }

    public function register(string $identifier, DefinitionSet $resolvedSet, Container $container): void {
        $resolvedSet->add(
            new AbstractConcrete(
                $identifier,
                static function (Environment $environment, ClientInterface $client) {
                    return new FooApi($environment->get('FOO_API_KEY'), $client)
                }
            )
        );
    }
}
```

## Feature comparison with other containers

|                                               | PrinsFrank | League | PHP-DI | Laravel<sup>1</sup> |
|-----------------------------------------------|------------|--------|--------|---------------------|
| No opaque entry identifiers                   | ✅          | ❌      | ❌      | ❌                   |
| Dynamic abstract concrete bindings            | ✅          | ❌      | ❌      | ❌                   |
| No dependencies to other packages<sup>2</sup> | ✅          | ✅      | ❌      | ❌                   |
| DI'ed services in ServiceProvider Closures    | ✅          | ❌      | ❌      | ❌                   |
| Autowiring                                    | ✅          | ✅      | ✅      | ✅                   |

<sup>1</sup> Published as illuminate/container 
<sup>2</sup> Other than the psr/container interface
