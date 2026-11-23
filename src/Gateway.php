<?php
declare(strict_types=1);

namespace Redsys;

use Dmorenof\SdkRuntime\Configuration;
use Redsys\Dto\Emv3ds;
use Redsys\Dto\Envelope;
use Redsys\Dto\Parameters;
use Redsys\Dto\Result;

/**
 * Everything a shop does with the TPV Virtual, for one merchant: take a payment by redirection or by REST, confirm,
 * refund, cancel, pass the 3-D Secure steps and verify notifications.
 *
 *     $gateway = new Gateway(Merchant::fromApiKey($apiKey));
 *     $payment = new Parameters();
 *     $payment->amount = 145;                       // 1.45 EUR
 *     $payment->order = '1446117555';
 *     echo $gateway->redirectForm($payment)->html();    // sends the payer to Redsys
 *
 * The merchant code, terminal and currency (euros) are filled in; a request is signed with the merchant's key; an answer
 * is verified before it is read. The HTTP side is {@see Client}'s: pass a `Configuration` to set timeouts, a proxy, retries or middleware.
 */
final class Gateway
{
    private readonly Client $client;

    public function __construct(public readonly Merchant $merchant, ?Configuration $configuration = null)
    {
        $configuration = $configuration === null ? new Configuration() : clone $configuration;
        if ($configuration->baseUrl === '') {
            $configuration->baseUrl = Client::serverUrl($merchant->environment->serverName());
        }
        $this->client = Client::connect($configuration);
    }

    // -- Redirection: the payer's browser goes to Redsys ------------------------------------------------------------

    /** The form that sends the payer to the payment page. */
    public function redirectForm(Parameters $parameters, TransactionType $type = TransactionType::Authorisation): RedirectForm
    {
        $envelope = $this->envelope($parameters, $type);
        return new RedirectForm($this->merchant->environment->redirectUrl(), [
            'Ds_SignatureVersion' => $envelope->signatureVersion,
            'Ds_MerchantParameters' => $envelope->merchantParameters,
            'Ds_Signature' => $envelope->signature,
        ]);
    }

    /** The form that sends the payer to the card issuer's challenge page, from the answer that asked for it. */
    public function challengeForm(Result $result): RedirectForm
    {
        $emv3ds = $result->emv3ds ?? null;
        if ($emv3ds === null || !isset($emv3ds->acsURL, $emv3ds->creq)) {
            throw new RedsysException('The answer does not ask for a challenge: it carries no acsURL and creq.');
        }
        return new RedirectForm($emv3ds->acsURL, ['CReq' => $emv3ds->creq]);
    }

    // -- REST: the shop talks to Redsys ---------------------------------------------------------------------------

    /** The first call of a card payment: which verification the card needs (3-D Secure). */
    public function init(Parameters $parameters, TransactionType $type = TransactionType::Authorisation): Result
    {
        return $this->open($this->client->init($this->envelope($parameters, $type, ['DS_MERCHANT_EMV3DS' => ['threeDSInfo' => ThreeDsInfo::CardData->value]])));
    }

    /** Any operation, by its type (null: the parameters carry none). The others are this with the type filled in. */
    public function operate(Parameters $parameters, ?TransactionType $type): Result
    {
        return $this->open($this->client->treat($this->envelope($parameters, $type)));
    }

    public function authorise(Parameters $parameters): Result
    {
        return $this->operate($parameters, TransactionType::Authorisation);
    }

    public function preauthorise(Parameters $parameters): Result
    {
        return $this->operate($parameters, TransactionType::Preauthorisation);
    }

    /** Confirms a preauthorisation: the order number must be the one of the preauthorisation. */
    public function confirmPreauthorisation(Parameters $parameters): Result
    {
        return $this->operate($parameters, TransactionType::PreauthorisationConfirmation);
    }

    public function partiallyConfirm(Parameters $parameters): Result
    {
        return $this->operate($parameters, TransactionType::PartialConfirmation);
    }

    public function refund(Parameters $parameters): Result
    {
        return $this->operate($parameters, TransactionType::Refund);
    }

    public function authenticate(Parameters $parameters): Result
    {
        return $this->operate($parameters, TransactionType::Authentication);
    }

    public function confirmAuthentication(Parameters $parameters): Result
    {
        return $this->operate($parameters, TransactionType::AuthenticationConfirmation);
    }

    /** Voids a preauthorisation. */
    public function void(Parameters $parameters): Result
    {
        return $this->operate($parameters, TransactionType::Void);
    }

    /** Cancels an authorised payment. */
    public function cancel(Parameters $parameters): Result
    {
        return $this->operate($parameters, TransactionType::Cancellation);
    }

    /** The last step of 3-D Secure: gives Redsys what the issuer posted back after the payer's challenge. */
    public function challengeResponse(string $order, string $protocolVersion, string $cres): Result
    {
        $parameters = new Parameters();
        $parameters->order = $order;
        $emv3ds = self::emv3ds(ThreeDsInfo::ChallengeResponse);
        $emv3ds->protocolVersion = $protocolVersion;
        $emv3ds->cres = $cres;
        $parameters->emv3ds = $emv3ds;
        return $this->operate($parameters, null);
    }

    // -- Notifications ----------------------------------------------------------------------------------------------

    /**
     * Verifies a notification (what Redsys posts to `merchantUrl`, or hands back to the payer) and reads it. Nothing in it is
     * available before its signature has been verified.
     *
     * @param array<string, mixed> $fields `Ds_SignatureVersion`, `Ds_MerchantParameters` and `Ds_Signature`, as received.
     * @throws InvalidSignatureException
     */
    public function notification(array $fields): Result
    {
        return $this->open(Envelope::fromFields($fields));
    }

    // -- Internals --------------------------------------------------------------------------------------------------

    private static function emv3ds(ThreeDsInfo $info): Emv3ds
    {
        $emv3ds = new Emv3ds();
        $emv3ds->threeDSInfo = $info->value;
        return $emv3ds;
    }

    /**
     * The request completed with what the merchant already says, and signed. What the caller set wins over the defaults, and the
     * caller's parameters are not changed.
     *
     * @param array<string, mixed> $defaults More defaults, by the name Redsys reads.
     */
    private function envelope(Parameters $parameters, ?TransactionType $type, array $defaults = []): Envelope
    {
        $values = $parameters->parameters();
        $values += [
            'DS_MERCHANT_MERCHANTCODE' => $this->merchant->code,
            'DS_MERCHANT_TERMINAL' => (string) $this->merchant->terminal,
            'DS_MERCHANT_CURRENCY' => '978',
        ] + ($type === null ? [] : ['DS_MERCHANT_TRANSACTIONTYPE' => $type->value]) + $defaults;
        return Envelope::sign($values, $this->merchant->key);
    }

    /** An answer or notification, once it is known to come from Redsys. */
    private function open(Envelope $reply): Result
    {
        if (isset($reply->errorCode) && $reply->errorCode !== '') {
            throw new RedsysException('Redsys refused the request with ' . $reply->errorCode . '.', $reply->errorCode);
        }
        if (!isset($reply->merchantParameters, $reply->signature, $reply->signatureVersion)) {
            throw new InvalidSignatureException('The answer lacks Ds_MerchantParameters, Ds_Signature or Ds_SignatureVersion.');
        }
        $version = SignatureVersion::tryFrom($reply->signatureVersion)
            ?? throw new InvalidSignatureException('The answer is signed with an unknown scheme: ' . $reply->signatureVersion . '.');
        $json = base64_decode(strtr($reply->merchantParameters, '-_', '+/'), true);
        $decoded = $json === false ? null : json_decode($json);
        if (!$decoded instanceof \stdClass) {
            throw new InvalidSignatureException('Ds_MerchantParameters is not Base64 JSON.');
        }
        // Redsys writes the names in capitals or in camel case, depending on the channel; the model reads capitals.
        $values = new \stdClass();
        foreach (get_object_vars($decoded) as $name => $value) {
            $values->{strtoupper((string) $name)} = is_object($value) ? $value : (is_scalar($value) ? (string) $value : null);
        }
        $order = $values->DS_ORDER ?? null;
        if (!is_string($order) || $order === '') {
            throw new InvalidSignatureException('The answer has no Ds_Order.');
        }
        if (!Signature::verify($reply->signature, $reply->merchantParameters, $order, $this->merchant->key, $version)) {
            throw new InvalidSignatureException('The signature of the answer does not match: it was not signed by Redsys with this merchant\'s key.');
        }
        return Result::fromValue($values);
    }
}
