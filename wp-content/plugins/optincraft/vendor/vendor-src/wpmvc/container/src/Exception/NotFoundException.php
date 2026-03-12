<?php

namespace OptinCraft\WpMVC\Container\Exception;

\defined('ABSPATH') || exit;
use Exception;
use OptinCraft\Psr\Container\NotFoundExceptionInterface;
class NotFoundException extends Exception implements NotFoundExceptionInterface
{
}
