<?php
declare(strict_types=1);

namespace Redsys\Dto;

use Dmorenof\SdkRuntime\DTO;

/**
 * The data of an operation sent to the TPV Virtual: payment, preauthorisation, confirmation, refund, cancellation…
 * 
 * Every value is checked where it is assigned (a PHP 8.4 `set` hook), against the length and format of Redsys's manual:
 * 
 *     $payment = new Parameters();
 *     $payment->amount = 145;
 *     $payment->order = '1234abc';        // not 'abc': the first four characters must be digits
 * 
 * A parameter the model does not declare is set under its name in the manual: `$payment->setAdditionalProperty('DS_MERCHANT_ANYTHING', 'x')`.
 */
final class Parameters extends DTO
{
    protected const array REQUIRED_FIELDS = ['order'];

    public const string ADDITIONAL_PROPERTIES_TYPE = 'mixed';
    public const bool ADDITIONAL_PROPERTIES_NULLABLE = true;

    protected const array VALIDATION = [
        'merchantCode' => ['pattern' => '^[0-9]{1,9}$'],
        'terminal' => ['pattern' => '^[0-9]{1,3}$'],
        'amount' => ['minimum' => 0, 'maximum' => 999999999999],
        'currency' => ['pattern' => '^[0-9]{1,4}$'],
        'order' => ['pattern' => '^[0-9]{4}[0-9A-Za-z]{0,8}$'],
        'merchantUrl' => ['maxLength' => 250],
        'urlOk' => ['maxLength' => 250],
        'urlKo' => ['maxLength' => 250],
        'productDescription' => ['maxLength' => 125],
        'cardholderName' => ['maxLength' => 60],
        'merchantName' => ['maxLength' => 25],
        'merchantData' => ['maxLength' => 1024],
        'consumerLanguage' => ['pattern' => '^[0-9]{1,3}$'],
        'cardNumber' => ['pattern' => '^[0-9]{8,19}$'],
        'cardExpiryDate' => ['pattern' => '^[0-9]{4}$'],
        'cvv2' => ['pattern' => '^[0-9]{3,4}$'],
    ];

    /** @var array<string, string> PHP property => name in the parameters Redsys reads. */
    protected const array WIRE_PROPERTY_MAP = [
        'merchantCode' => 'DS_MERCHANT_MERCHANTCODE',
        'terminal' => 'DS_MERCHANT_TERMINAL',
        'transactionType' => 'DS_MERCHANT_TRANSACTIONTYPE',
        'amount' => 'DS_MERCHANT_AMOUNT',
        'currency' => 'DS_MERCHANT_CURRENCY',
        'order' => 'DS_MERCHANT_ORDER',
        'merchantUrl' => 'DS_MERCHANT_MERCHANTURL',
        'urlOk' => 'DS_MERCHANT_URLOK',
        'urlKo' => 'DS_MERCHANT_URLKO',
        'productDescription' => 'DS_MERCHANT_PRODUCTDESCRIPTION',
        'cardholderName' => 'DS_MERCHANT_TITULAR',
        'merchantName' => 'DS_MERCHANT_MERCHANTNAME',
        'merchantData' => 'DS_MERCHANT_MERCHANTDATA',
        'consumerLanguage' => 'DS_MERCHANT_CONSUMERLANGUAGE',
        'payMethods' => 'DS_MERCHANT_PAYMETHODS',
        'cardNumber' => 'DS_MERCHANT_PAN',
        'cardExpiryDate' => 'DS_MERCHANT_EXPIRYDATE',
        'cvv2' => 'DS_MERCHANT_CVV2',
        'tokenizedCard' => 'DS_MERCHANT_IDENTIFIER',
        'cofIni' => 'DS_MERCHANT_COF_INI',
        'cofType' => 'DS_MERCHANT_COF_TYPE',
        'cofTxnId' => 'DS_MERCHANT_COF_TXNID',
        'excepSca' => 'DS_MERCHANT_EXCEP_SCA',
        'dcc' => 'DS_MERCHANT_DCC',
        'rts' => 'DS_MERCHANT_RTS',
        'directPayment' => 'DS_MERCHANT_DIRECTPAYMENT',
        'group' => 'DS_MERCHANT_GROUP',
        'idOper' => 'DS_MERCHANT_IDOPER',
        'authorisationCode' => 'DS_MERCHANT_AUTHORISATIONCODE',
        'transactionDate' => 'DS_MERCHANT_TRANSACTIONDATE',
        'taxReference' => 'DS_MERCHANT_TAX_REFERENCE',
        'merchantDescriptor' => 'DS_MERCHANT_MERCHANTDESCRIPTOR',
        'persoCode' => 'DS_MERCHANT_PERSOCODE',
        'matchingData' => 'DS_MERCHANT_MATCHINGDATA',
        'externalMpi' => 'DS_MERCHANT_MPIEXTERNAL',
        'customerMobile' => 'DS_MERCHANT_CUSTOMER_MOBILE',
        'customerMail' => 'DS_MERCHANT_CUSTOMER_MAIL',
        'customerSmsText' => 'DS_MERCHANT_CUSTOMER_SMS_TEXT',
        'p2fExpiryDate' => 'DS_MERCHANT_P2F_EXPIRYDATE',
        'p2fXmlData' => 'DS_MERCHANT_P2F_XMLDATA',
        'financing' => 'DS_MERCHANT_FINANCING',
        'ota' => 'DS_MERCHANT_OTA',
        'clientIp' => 'DS_MERCHANT_CLIENTIP',
        'shippingAddressPyp' => 'DS_MERCHANT_SHIPPINGADDRESSPYP',
        'tokenData' => 'DS_MERCHANT_TOKENDATA',
        'bizumData' => 'DS_MERCHANT_BIZUM_DATA',
        'bizumMobileNumber' => 'DS_MERCHANT_BIZUM_MOBILENUMBER',
        'xPayType' => 'DS_XPAYTYPE',
        'xPayOrigin' => 'DS_XPAYORIGEN',
        'xPayData' => 'DS_XPAYDATA',
        'xPayDecodedData' => 'DS_XPAYDECODEDDATA',
        'module' => 'DS_MERCHANT_MODULE',
        'emv3ds' => 'DS_MERCHANT_EMV3DS',
    ];

    /** @var array<string, string> Name in the parameters => PHP property (Redsys accepts capitals or camel case). */
    protected const array PROPERTY_MAP = [
        'DS_MERCHANT_MERCHANTCODE' => 'merchantCode',
        'DS_MERCHANT_TERMINAL' => 'terminal',
        'DS_MERCHANT_TRANSACTIONTYPE' => 'transactionType',
        'DS_MERCHANT_AMOUNT' => 'amount',
        'DS_MERCHANT_CURRENCY' => 'currency',
        'DS_MERCHANT_ORDER' => 'order',
        'DS_MERCHANT_MERCHANTURL' => 'merchantUrl',
        'DS_MERCHANT_URLOK' => 'urlOk',
        'DS_MERCHANT_URLKO' => 'urlKo',
        'DS_MERCHANT_PRODUCTDESCRIPTION' => 'productDescription',
        'DS_MERCHANT_TITULAR' => 'cardholderName',
        'DS_MERCHANT_MERCHANTNAME' => 'merchantName',
        'DS_MERCHANT_MERCHANTDATA' => 'merchantData',
        'DS_MERCHANT_CONSUMERLANGUAGE' => 'consumerLanguage',
        'DS_MERCHANT_PAYMETHODS' => 'payMethods',
        'DS_MERCHANT_PAN' => 'cardNumber',
        'DS_MERCHANT_EXPIRYDATE' => 'cardExpiryDate',
        'DS_MERCHANT_CVV2' => 'cvv2',
        'DS_MERCHANT_IDENTIFIER' => 'tokenizedCard',
        'DS_MERCHANT_COF_INI' => 'cofIni',
        'DS_MERCHANT_COF_TYPE' => 'cofType',
        'DS_MERCHANT_COF_TXNID' => 'cofTxnId',
        'DS_MERCHANT_EXCEP_SCA' => 'excepSca',
        'DS_MERCHANT_DCC' => 'dcc',
        'DS_MERCHANT_RTS' => 'rts',
        'DS_MERCHANT_DIRECTPAYMENT' => 'directPayment',
        'DS_MERCHANT_GROUP' => 'group',
        'DS_MERCHANT_IDOPER' => 'idOper',
        'DS_MERCHANT_AUTHORISATIONCODE' => 'authorisationCode',
        'DS_MERCHANT_TRANSACTIONDATE' => 'transactionDate',
        'DS_MERCHANT_TAX_REFERENCE' => 'taxReference',
        'DS_MERCHANT_MERCHANTDESCRIPTOR' => 'merchantDescriptor',
        'DS_MERCHANT_PERSOCODE' => 'persoCode',
        'DS_MERCHANT_MATCHINGDATA' => 'matchingData',
        'DS_MERCHANT_MPIEXTERNAL' => 'externalMpi',
        'DS_MERCHANT_CUSTOMER_MOBILE' => 'customerMobile',
        'DS_MERCHANT_CUSTOMER_MAIL' => 'customerMail',
        'DS_MERCHANT_CUSTOMER_SMS_TEXT' => 'customerSmsText',
        'DS_MERCHANT_P2F_EXPIRYDATE' => 'p2fExpiryDate',
        'DS_MERCHANT_P2F_XMLDATA' => 'p2fXmlData',
        'DS_MERCHANT_FINANCING' => 'financing',
        'DS_MERCHANT_OTA' => 'ota',
        'DS_MERCHANT_CLIENTIP' => 'clientIp',
        'DS_MERCHANT_SHIPPINGADDRESSPYP' => 'shippingAddressPyp',
        'DS_MERCHANT_TOKENDATA' => 'tokenData',
        'DS_MERCHANT_BIZUM_DATA' => 'bizumData',
        'DS_MERCHANT_BIZUM_MOBILENUMBER' => 'bizumMobileNumber',
        'DS_XPAYTYPE' => 'xPayType',
        'DS_XPAYORIGEN' => 'xPayOrigin',
        'DS_XPAYDATA' => 'xPayData',
        'DS_XPAYDECODEDDATA' => 'xPayDecodedData',
        'DS_MERCHANT_MODULE' => 'module',
        'DS_MERCHANT_EMV3DS' => 'emv3ds',
    ];

    /** FUC code of the merchant (up to 9 digits). The gateway fills it from the merchant. */
    public ?string $merchantCode {
        set {
            $this->assigning('merchantCode', $value);
            $this->merchantCode = $value;
        }
    }

    /** Terminal number (up to 3 digits). The gateway fills it from the merchant. */
    public ?string $terminal {
        set {
            $this->assigning('terminal', $value);
            $this->terminal = $value;
        }
    }

    /** What the terminal is asked to do. The gateway methods (`authorise()`, `refund()`…) set it. */
    public ?\Redsys\TransactionType $transactionType;

    /** In the smallest unit of the currency: for euros the last two digits are cents (145 = 1.45 EUR). */
    public ?int $amount {
        set {
            $this->assigning('amount', $value);
            $this->amount = $value;
        }
    }

    /** ISO 4217 numeric code: 978 euros (the default), 840 dollars, 826 pounds, 392 yen. */
    public ?string $currency {
        set {
            $this->assigning('currency', $value);
            $this->currency = $value;
        }
    }

    /** Order number: up to 12 characters, the first four digits, the rest letters or digits. A confirmation must repeat the number of the operation it confirms. */
    public ?string $order {
        set {
            $this->assigning('order', $value);
            $this->order = $value;
        }
    }

    /** Where Redsys posts the on-line notification. */
    public ?string $merchantUrl {
        set {
            $this->assigning('merchantUrl', $value);
            $this->merchantUrl = $value;
        }
    }

    /** Where the payer returns after a successful payment. */
    public ?string $urlOk {
        set {
            $this->assigning('urlOk', $value);
            $this->urlOk = $value;
        }
    }

    /** Where the payer returns after a failed payment. */
    public ?string $urlKo {
        set {
            $this->assigning('urlKo', $value);
            $this->urlKo = $value;
        }
    }

    /** Shown to the payer on the confirmation page. */
    public ?string $productDescription {
        set {
            $this->assigning('productDescription', $value);
            $this->productDescription = $value;
        }
    }

    /** Name of the cardholder, shown on the confirmation page. */
    public ?string $cardholderName {
        set {
            $this->assigning('cardholderName', $value);
            $this->cardholderName = $value;
        }
    }

    /** Merchant name for the payer's receipt. */
    public ?string $merchantName {
        set {
            $this->assigning('merchantName', $value);
            $this->merchantName = $value;
        }
    }

    /** Free data the notification returns unchanged. */
    public ?string $merchantData {
        set {
            $this->assigning('merchantData', $value);
            $this->merchantData = $value;
        }
    }

    /** 0 unknown; 1 Spanish, 2 English, 3 Catalan, 4 French, 5 German, 6 Dutch, 7 Italian, 8 Swedish, 9 Portuguese, 10 Valencian, 11 Polish, 12 Galician, 13 Basque. */
    public ?string $consumerLanguage {
        set {
            $this->assigning('consumerLanguage', $value);
            $this->consumerLanguage = $value;
        }
    }

    /** Payment methods offered on the payment page (`z` is Bizum). */
    public ?string $payMethods;

    /** Card number, in the REST connection. Never log it. */
    public ?string $cardNumber {
        set {
            $this->assigning('cardNumber', $value);
            $this->cardNumber = $value;
        }
    }

    /** Expiry of the card as YYMM (the reverse of what is printed on it). */
    public ?string $cardExpiryDate {
        set {
            $this->assigning('cardExpiryDate', $value);
            $this->cardExpiryDate = $value;
        }
    }

    /** Card security code, in the REST connection. Never log it. */
    public ?string $cvv2 {
        set {
            $this->assigning('cvv2', $value);
            $this->cvv2 = $value;
        }
    }

    /** Reference of a card the merchant has stored with Redsys (one-click payments). */
    public ?string $tokenizedCard;

    /** Credential on file: `S` when this operation stores the card. */
    public ?string $cofIni;

    /** Credential on file: kind of operation (`I` instalments, `R` recurring, `H` reauthorisation, `E` resubmission, `D` delayed, `M` incremental, `N` no show, `C` other). */
    public ?string $cofType;

    /** Credential on file: identifier of the first operation. */
    public ?string $cofTxnId;

    /** Exemption from strong customer authentication that is asked for. */
    public ?string $excepSca;

    /** Dynamic currency conversion. */
    public ?string $dcc;

    /** Real-time settlement. */
    public ?string $rts;

    /** `true` for a payment without the payer's authentication; `moto` for mail and telephone orders. */
    public ?string $directPayment;

    /** Group of the card reference. */
    public ?string $group;

    /** Identifier of an operation started with InSite. */
    public ?string $idOper;

    /** Authorisation code, for operations that confirm another. */
    public ?string $authorisationCode;

    /** Date of the operation that is being confirmed or cancelled. */
    public ?string $transactionDate;

    /** Tax reference of the merchant. */
    public ?string $taxReference;

    /** Descriptor on the cardholder's statement. */
    public ?string $merchantDescriptor;

    /** Code of the customised payment page. */
    public ?string $persoCode;

    /** Matching data. */
    public ?string $matchingData;

    /** Result of an external 3-D Secure authentication. */
    public ?string $externalMpi;

    /** Mobile number of the customer (payment by link). */
    public ?string $customerMobile;

    /** E-mail of the customer (payment by link). */
    public ?string $customerMail;

    /** Text of the SMS sent to the customer (payment by link). */
    public ?string $customerSmsText;

    /** Expiry of a payment link. */
    public ?string $p2fExpiryDate;

    /** Data of a payment link. */
    public ?string $p2fXmlData;

    /** Financing data. */
    public ?string $financing;

    /** Online travel agency data. */
    public ?string $ota;

    /** IP address of the payer. */
    public ?string $clientIp;

    /** Shipping address (PayPal). */
    public ?string $shippingAddressPyp;

    /** Token data. */
    public ?string $tokenData;

    /** Bizum data. */
    public ?string $bizumData;

    /** Mobile number of the Bizum payer. */
    public ?string $bizumMobileNumber;

    /** Wallet payment type (Apple Pay, Google Pay). */
    public ?string $xPayType;

    /** Origin of a wallet payment. */
    public ?string $xPayOrigin;

    /** Encrypted data of a wallet payment. */
    public ?string $xPayData;

    /** Decrypted data of a wallet payment. */
    public ?string $xPayDecodedData;

    /** Name of the module that sends the operation. */
    public ?string $module;

    /** The 3-D Secure data of a REST operation. */
    public ?\Redsys\Dto\Emv3ds $emv3ds;


    /**
     * The parameters as Redsys reads them: every value a string, under the names of the manual; the 3-D Secure data stays
     * an object.
     *
     * @return array<string, mixed>
     */
    public function parameters(): array
    {
        $this->validate();
        $parameters = [];
        foreach ((array) json_decode(json_encode($this->jsonSerializeForRequest(), JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR) as $name => $value) {
            $parameters[$name] = is_array($value) ? $value : (is_bool($value) ? ($value ? 'true' : 'false') : (string) $value);
        }
        return $parameters;
    }
}
