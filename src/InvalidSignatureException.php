<?php
declare(strict_types=1);

namespace Redsys;

/** An answer or notification whose signature does not match, or that does not carry the fields it must: do not trust it. */
final class InvalidSignatureException extends RedsysException
{
}
