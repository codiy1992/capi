<?php
namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    public function __construct(int $code = 0, string $message = '', Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
