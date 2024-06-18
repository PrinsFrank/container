<?php
declare(strict_types=1);

namespace PrinsFrank\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

final class UnresolvableException extends ContainerException implements NotFoundExceptionInterface {
}
