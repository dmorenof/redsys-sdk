<?php
declare(strict_types=1);

namespace Redsys;

/** The `Ds_Response` codes shops meet most, in English. Redsys's own table is the reference for the rest. */
final class ResponseCodes
{
    private const array DESCRIPTIONS = [
        '400' => 'Cancellation authorised',
        '900' => 'Refund or confirmation authorised',
        '101' => 'Card expired',
        '102' => 'Card under temporary exception or suspected of fraud',
        '106' => 'PIN attempts exceeded',
        '125' => 'Card not effective',
        '129' => 'Wrong security code (CVV2/CVC2)',
        '180' => 'Card not covered by the service',
        '184' => 'Cardholder authentication failed',
        '190' => 'Refused by the issuer, no reason given',
        '191' => 'Wrong expiry date',
        '202' => 'Card under temporary exception or suspected of fraud, to be retained',
        '904' => 'Merchant not registered',
        '909' => 'System error',
        '912' => 'Issuer not available',
        '913' => 'Repeated order number',
        '944' => 'Wrong session',
        '950' => 'Refund not allowed',
        '9912' => 'Issuer not available',
        '9064' => 'Wrong number of card digits',
        '9078' => 'Type of operation not allowed for this card',
        '9093' => 'Card does not exist',
        '9094' => 'Refused by the international servers',
        '9104' => 'Merchant requires a secure cardholder and the cardholder has no secure purchase key',
        '9218' => 'Merchant does not allow secure operations through this entrance',
        '9253' => 'Card fails the check digit',
        '9256' => 'Merchant cannot make preauthorisations',
        '9257' => 'Card does not allow preauthorisations',
        '9261' => 'Operation stopped by the restriction control of the SIS',
        '9915' => 'Payment cancelled at the payer\'s request',
        '9997' => 'Another transaction with the same card is being processed',
        '9998' => 'Operation waiting for the card data',
        '9999' => 'Operation redirected to the issuer to authenticate',
    ];

    public static function describe(string $code): ?string
    {
        if (!ctype_digit($code)) {
            return null;
        }
        $number = (int) $code;
        return $number <= 99 ? 'Authorised' : (self::DESCRIPTIONS[(string) $number] ?? null);
    }
}
