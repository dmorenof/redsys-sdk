<?php
declare(strict_types=1);

namespace Redsys\Dto;

use Dmorenof\SdkRuntime\DTO;
use Redsys\ResponseCodes;

/**
 * The outcome of an operation, as Redsys reports it in a REST answer or a notification. The names Redsys writes (`Ds_Order`, `DS_ORDER`…) are all read; a parameter this model does not declare is in `additionalProperties()`.
 */
final class Result extends DTO
{
    public const string ADDITIONAL_PROPERTIES_TYPE = 'mixed';
    public const bool ADDITIONAL_PROPERTIES_NULLABLE = true;

    /** @var array<string, string> Name in the answer, in capitals => PHP property: every spelling Redsys uses. */
    protected const array PROPERTY_MAP = [
        'DS_ORDER' => 'order',
        'DS_AMOUNT' => 'amount',
        'DS_CURRENCY' => 'currency',
        'DS_MERCHANTCODE' => 'merchantCode',
        'DS_TERMINAL' => 'terminal',
        'DS_TRANSACTIONTYPE' => 'transactionType',
        'DS_RESPONSE' => 'response',
        'DS_AUTHORISATIONCODE' => 'authorisationCode',
        'DS_DATE' => 'date',
        'DS_HOUR' => 'hour',
        'DS_MERCHANTDATA' => 'merchantData',
        'DS_SECUREPAYMENT' => 'securePayment',
        'DS_CONSUMERLANGUAGE' => 'consumerLanguage',
        'DS_LANGUAGE' => 'consumerLanguage',
        'DS_CARD_NUMBER' => 'cardNumber',
        'DS_CARDNUMBER' => 'cardNumber',
        'DS_EXPIRYDATE' => 'cardExpiryDate',
        'DS_CARD_BRAND' => 'cardBrand',
        'DS_CARD_COUNTRY' => 'cardCountry',
        'DS_CARD_TYPE' => 'cardType',
        'DS_CARD_PSD2' => 'cardPsd2',
        'DS_CARD_TYPOLOGY' => 'cardTypology',
        'DS_CARD_ISSUER' => 'cardIssuer',
        'DS_CARD_TOKEN_INDICATOR' => 'cardTokenIndicator',
        'DS_CARD_TREATMENT' => 'cardTreatment',
        'DS_CARD_PRODUCTTYPE' => 'cardProductType',
        'DS_MERCHANT_IDENTIFIER' => 'tokenizedCard',
        'DS_MERCHANT_COF_TXNID' => 'cofTxnId',
        'DS_DCC' => 'dcc',
        'DS_AMOUNT_DCC' => 'dccAmount',
        'DS_CURRENCY_DCC' => 'dccCurrency',
        'DS_AMOUNT_EURO' => 'amountEuro',
        'DS_EXCEP_SCA' => 'excepSca',
        'DS_RTS' => 'rts',
        'RTS' => 'rts',
        'DS_ECI' => 'eci',
        'ECI' => 'eci',
        'DS_PROCESSEDPAYMETHOD' => 'processedPayMethod',
        'DS_URLPAGO2FASES' => 'paymentLinkUrl',
        'DS_BIZUMIDSTATUS' => 'bizumIdStatus',
        'DS_BIZUMIDRESPONSE' => 'bizumIdResponse',
        'DS_BIZUMIDDESCRIPTION' => 'bizumIdDescription',
        'DS_RESPONSE_DESCRIPTION' => 'responseDescription',
        'DS_ERRORCODE' => 'errorCode',
        'ERRORCODE' => 'errorCode',
        'CODIGO' => 'code',
        'DS_EMV3DS' => 'emv3ds',
    ];

    /** Order number. */
    public ?string $order;

    /** Amount in the smallest unit of the currency, as Redsys writes it. */
    public ?string $amount;

    /** ISO 4217 numeric currency code. */
    public ?string $currency;

    /** FUC code of the merchant. */
    public ?string $merchantCode;

    /** Terminal number. */
    public ?string $terminal;

    /** Type of operation that was made. */
    public ?string $transactionType;

    /** Response code: 0000 to 0099 is an authorised payment or preauthorisation; 900 a refund or confirmation; 400 a cancellation; anything else a refusal. */
    public ?string $response;

    /** Authorisation code the card issuer gave. */
    public ?string $authorisationCode;

    /** Date of the operation (dd/mm/yyyy). */
    public ?string $date;

    /** Time of the operation (HH:mm). */
    public ?string $hour;

    /** The free data of the request, unchanged. */
    public ?string $merchantData;

    /** `1` when the payer was authenticated. */
    public ?string $securePayment;

    /** Language of the payer. */
    public ?string $consumerLanguage;

    /** Masked card number. */
    public ?string $cardNumber;

    /** Expiry of the card. */
    public ?string $cardExpiryDate;

    /** Card brand. */
    public ?string $cardBrand;

    /** Country of issue of the card (ISO 3166 numeric). */
    public ?string $cardCountry;

    /** `C` credit, `D` debit. */
    public ?string $cardType;

    /** Whether the card is subject to PSD2. */
    public ?string $cardPsd2;

    /** Typology of the card. */
    public ?string $cardTypology;

    /** Issuer of the card. */
    public ?string $cardIssuer;

    /** Whether the card is a token. */
    public ?string $cardTokenIndicator;

    /** Treatment of the card. */
    public ?string $cardTreatment;

    /** Product type of the card. */
    public ?string $cardProductType;

    /** Reference of the stored card. */
    public ?string $tokenizedCard;

    /** Credential on file: identifier of the operation. */
    public ?string $cofTxnId;

    /** Dynamic currency conversion. */
    public ?string $dcc;

    /** Amount in the payer's currency. */
    public ?string $dccAmount;

    /** The payer's currency. */
    public ?string $dccCurrency;

    /** Amount in euros. */
    public ?string $amountEuro;

    /** Exemption from strong customer authentication that was applied. */
    public ?string $excepSca;

    /** Real-time settlement. */
    public ?string $rts;

    /** Electronic commerce indicator of the 3-D Secure authentication. */
    public ?string $eci;

    /** Payment method that processed the operation. */
    public ?string $processedPayMethod;

    /** Address of the payment page of a two-step payment. */
    public ?string $paymentLinkUrl;

    /** Bizum: status. */
    public ?string $bizumIdStatus;

    /** Bizum: response code. */
    public ?string $bizumIdResponse;

    /** Bizum: description. */
    public ?string $bizumIdDescription;

    /** Description of the response code, when Redsys sends it. */
    public ?string $responseDescription;

    /** Error code of the operation. */
    public ?string $errorCode;

    /** Code of the answer. */
    public ?string $code;

    /** The 3-D Secure data of the answer: the verification the card needs, or the challenge the payer must pass. */
    public ?\Redsys\Dto\Emv3ds $emv3ds;


    /** Whether the operation was carried out: response 0000 to 0099 (payment, preauthorisation), 900 (refund, confirmation) or 400 (cancellation). */
    public function authorised(): bool
    {
        if (!isset($this->response) || !ctype_digit($this->response)) {
            return false;
        }
        $code = (int) $this->response;
        return $code <= 99 || $code === 900 || $code === 400;
    }

    /** What the response code means, in English, for the codes shops meet most; null for the others (see Redsys's table). */
    public function describeResponse(): ?string
    {
        return isset($this->response) ? ResponseCodes::describe($this->response) : null;
    }
}
