<?php
declare(strict_types=1);

namespace PrinsFrank\Container\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;

abstract class ContainerException extends Exception implements ContainerExceptionInterface {
}
