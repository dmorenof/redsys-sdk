<?php
declare(strict_types=1);

namespace Redsys;

/**
 * The signature of a request or notification: the HMAC of the Base64 `Ds_MerchantParameters` string, keyed with a key
 * derived for the operation from the merchant's key and the order number.
 *
 * - `HMAC_SHA512_V2` and `HMAC_SHA512_V1`: the key is the AES-128-CBC encryption (zero IV) of the order number, with the
 *   first 16 characters of the merchant's key text as the AES key (padded with `0`), written in Base64; HMAC SHA-512.
 *   V2 writes the result in URL-safe Base64 without padding, V1 with it.
 * - `HMAC_SHA256_V1`: the key is the 3DES-CBC encryption (zero IV, order padded with zero bytes to 8) of the order number
 *   with the merchant's key decoded from Base64; HMAC SHA-256.
 */
final class Signature
{
    public static function sign(
        string $encodedParameters,
        string $order,
        #[\SensitiveParameter] string $merchantKey,
        SignatureVersion $version = SignatureVersion::HmacSha512V2,
    ): string {
        return match ($version) {
            SignatureVersion::HmacSha256V1 => self::urlSafe(hash_hmac('sha256', $encodedParameters, self::tripleDesKey($order, $merchantKey), true), true),
            SignatureVersion::HmacSha512V1 => self::urlSafe(hash_hmac('sha512', $encodedParameters, self::aesKey($order, $merchantKey), true), true),
            SignatureVersion::HmacSha512V2 => self::urlSafe(hash_hmac('sha512', $encodedParameters, self::aesKey($order, $merchantKey), true), false),
        };
    }

    /** Whether a signature received (in either Base64 alphabet, with or without padding) is the one the operation must carry. */
    public static function verify(
        string $received,
        string $encodedParameters,
        string $order,
        #[\SensitiveParameter] string $merchantKey,
        SignatureVersion $version = SignatureVersion::HmacSha512V2,
    ): bool {
        return hash_equals(self::canonical(self::sign($encodedParameters, $order, $merchantKey, $version)), self::canonical($received));
    }

    private static function aesKey(string $order, #[\SensitiveParameter] string $merchantKey): string
    {
        $key = str_pad(substr($merchantKey, 0, 16), 16, '0');
        $encrypted = openssl_encrypt($order, 'aes-128-cbc', $key, OPENSSL_RAW_DATA, str_repeat("\0", 16));
        if ($encrypted === false) {
            throw new \RuntimeException('The AES encryption of the order number failed: ' . (openssl_error_string() ?: 'unknown error'));
        }
        return base64_encode($encrypted);
    }

    private static function tripleDesKey(string $order, #[\SensitiveParameter] string $merchantKey): string
    {
        $key = base64_decode(strtr($merchantKey, '-_', '+/'), true);
        if ($key === false || strlen($key) !== 24) {
            throw new \InvalidArgumentException('For HMAC_SHA256_V1 the merchant key must be the Base64 text Redsys gives, which decodes to 24 bytes.');
        }
        $padded = str_pad($order, (int) (ceil(strlen($order) / 8) * 8), "\0");
        $encrypted = openssl_encrypt($padded, 'des-ede3-cbc', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, str_repeat("\0", 8));
        if ($encrypted === false) {
            throw new \RuntimeException('The 3DES encryption of the order number failed: ' . (openssl_error_string() ?: 'unknown error'));
        }
        return $encrypted;
    }

    private static function urlSafe(string $binary, bool $padded): string
    {
        $text = strtr(base64_encode($binary), '+/', '-_');
        return $padded ? $text : rtrim($text, '=');
    }

    /** One spelling for comparing: standard alphabet, padded. */
    private static function canonical(string $base64): string
    {
        $standard = strtr(rtrim($base64, '='), '-_', '+/');
        return $standard . str_repeat('=', (4 - strlen($standard) % 4) % 4);
    }
}
