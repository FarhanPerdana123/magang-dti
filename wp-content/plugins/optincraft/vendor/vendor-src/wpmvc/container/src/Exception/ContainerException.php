<?php

namespace OptinCraft\WpMVC\Container\Exception;

\defined('ABSPATH') || exit;
use Exception;
use OptinCraft\Psr\Container\ContainerExceptionInterface;
class ContainerException extends Exception implements ContainerExceptionInterface
{
}
