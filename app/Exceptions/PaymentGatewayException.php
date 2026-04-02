<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class PaymentGatewayException extends RuntimeException
{
    public static function wrap(Throwable $exception, string $message = ''): self
    {
        return new self($message ?: $exception->getMessage(), (int) $exception->getCode(), $exception);
    }
}
