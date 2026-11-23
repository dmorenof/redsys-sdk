<?php
declare(strict_types=1);

namespace Redsys;

/** Redsys refused the request (`errorCode` such as `SIS0057`), or what it answered cannot be trusted. */
class RedsysException extends \RuntimeException
{
    public function __construct(string $message, public readonly ?string $errorCode = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
