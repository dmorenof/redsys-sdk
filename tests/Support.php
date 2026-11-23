<?php
declare(strict_types=1);

namespace Redsys\Tests;

use Redsys\Signature;
use Redsys\SignatureVersion;

/** What the tests share: the test merchant Redsys publishes, and answers signed the way Redsys signs them. */
final class Support
{
    /** The generic test key Redsys publishes for every merchant. */
    public const KEY = 'sq7HjrUOBfKmC576ILgskD5srU870gJ7';

    /**
     * The fields of a signed answer or notification, as Redsys sends them: URL-safe Base64 parameters.
     *
     * @param array<string, mixed> $parameters
     * @return array{Ds_SignatureVersion: string, Ds_MerchantParameters: string, Ds_Signature: string}
     */
    public static function signed(array $parameters, string $key = self::KEY, SignatureVersion $version = SignatureVersion::HmacSha512V2): array
    {
        $encoded = rtrim(strtr(base64_encode(json_encode($parameters, JSON_THROW_ON_ERROR)), '+/', '-_'), '=');
        $order = (string) ($parameters['Ds_Order'] ?? $parameters['DS_ORDER']);
        return ['Ds_SignatureVersion' => $version->value, 'Ds_MerchantParameters' => $encoded, 'Ds_Signature' => Signature::sign($encoded, $order, $key, $version)];
    }
}
