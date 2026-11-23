<?php
declare(strict_types=1);

namespace Redsys\Dto;

use Dmorenof\SdkRuntime\DTO;
use Redsys\Signature;
use Redsys\SignatureVersion;

/**
 * What travels to and from the REST endpoints and in notifications: the parameters in URL-safe Base64, their signature and
 * the version of the signature scheme. An answer that is a failure carries only `errorCode`.
 */
final class Envelope extends DTO
{
    protected const array REQUIRED_FIELDS = ['signatureVersion', 'merchantParameters', 'signature'];

    protected const array WIRE_PROPERTY_MAP = [
        'signatureVersion' => 'Ds_SignatureVersion',
        'merchantParameters' => 'Ds_MerchantParameters',
        'signature' => 'Ds_Signature',
        'errorCode' => 'errorCode',
    ];

    protected const array PROPERTY_MAP = [
        'Ds_SignatureVersion' => 'signatureVersion',
        'Ds_MerchantParameters' => 'merchantParameters',
        'Ds_Signature' => 'signature',
        'errorCode' => 'errorCode',
    ];

    public string $signatureVersion;
    public string $merchantParameters;
    public string $signature;
    public ?string $errorCode;

    /**
     * Signs the parameters of an operation (the values of {@see Parameters::parameters()}, completed) with the merchant's key.
     *
     * @param array<string, mixed> $values
     */
    public static function sign(array $values, #[\SensitiveParameter] string $merchantKey, SignatureVersion $version = SignatureVersion::HmacSha512V2): self
    {
        $order = $values['DS_MERCHANT_ORDER'] ?? throw new \InvalidArgumentException('An operation needs its order number (the `order` parameter).');
        $encoded = rtrim(strtr(base64_encode(json_encode($values, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)), '+/', '-_'), '=');
        $envelope = new self();
        $envelope->signatureVersion = $version->value;
        $envelope->merchantParameters = $encoded;
        $envelope->signature = Signature::sign($encoded, (string) $order, $merchantKey, $version);
        return $envelope;
    }

    /** The envelope as an array of fields, as it is posted in a form. */
    public static function fromFields(array $fields): self
    {
        $envelope = new self();
        foreach (['Ds_SignatureVersion' => 'signatureVersion', 'Ds_MerchantParameters' => 'merchantParameters', 'Ds_Signature' => 'signature'] as $name => $property) {
            if (isset($fields[$name]) && is_string($fields[$name])) {
                $envelope->{$property} = $fields[$name];
            }
        }
        return $envelope;
    }
}
