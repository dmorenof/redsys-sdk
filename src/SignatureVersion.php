<?php
declare(strict_types=1);

namespace Redsys;

/** `Ds_SignatureVersion`: how a request is signed. New integrations use {@see self::HmacSha512V2}. */
enum SignatureVersion: string
{
    case HmacSha512V2 = 'HMAC_SHA512_V2';
    case HmacSha512V1 = 'HMAC_SHA512_V1';
    /** The 2016 scheme of the redirection manual. */
    case HmacSha256V1 = 'HMAC_SHA256_V1';
}
