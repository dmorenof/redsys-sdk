# Redsys SDK

Redsys (TPV Virtual) payments from PHP 8.4, on [sdk-runtime](https://github.com/dmorenof/sdk-runtime): redirection and
REST (with 3-D Secure), the operations a shop uses, requests signed with `HMAC_SHA512_V2`, and answers and notifications
verified before anything in them is read.

Redsys publishes no machine-readable contract (no OpenAPI), so this SDK is written by hand, which is what the runtime is
also for: the models, their validation, the HTTP client, servers, retries, middleware and errors are the runtime's, and
only what is specific to Redsys is here.

## Pay by redirection

```php
use Redsys\{Gateway, Merchant};
use Redsys\Dto\Parameters;

$gateway = new Gateway(Merchant::fromApiKey($apiKey));   // the API key of the Redsys portal: merchant, terminal, key, environment

$payment = new Parameters();
$payment->amount = 145;                      // 1.45 EUR: the last two digits are cents
$payment->order = '1446117555';              // up to 12 characters, the first four digits
$payment->merchantUrl = 'https://shop.example/redsys/notification';
$payment->urlOk = 'https://shop.example/paid';
$payment->urlKo = 'https://shop.example/failed';

echo $gateway->redirectForm($payment)->html();   // sends the payer to Redsys
```

The merchant code, the terminal and the currency (euros) are filled in. A value that breaks the manual
(`$payment->order = 'abc'`, a negative amount, a terminal of four digits) is refused at that line, not later by Redsys's
server with `SIS0431`.

## Be told

```php
use Redsys\InvalidSignatureException;

try {
    $result = $gateway->notification($_POST);
} catch (InvalidSignatureException $error) {
    http_response_code(400);                 // not signed by Redsys with this merchant's key: ignore it
    exit;
}
if ($result->authorised()) {
    markAsPaid($result->order, (int) $result->amount);
}
```

The redirect back to `urlOk` only says the payer finished; the notification is what settles the order.

## Pay by REST

```php
$payment->cardNumber = $card;  $payment->cardExpiryDate = '4912';  $payment->cvv2 = $cvv;   // YYMM

$init = $gateway->init($payment);            // which verification the card needs (iniciaPeticionREST)
// …the payer's browser data goes into $payment->emv3ds (see Dto\Emv3ds)…
$result = $gateway->authorise($payment);     // trataPeticionREST

if (!$result->authorised() && $result->emv3ds?->acsURL !== null) {
    echo $gateway->challengeForm($result)->html();             // the issuer's challenge
}
// the issuer posts back to notificationURL:
$result = $gateway->challengeResponse($order, $protocolVersion, $_POST['cres']);
```

Operations: `authorise`, `preauthorise`, `confirmPreauthorisation`, `partiallyConfirm`, `refund`, `void`, `cancel`,
`authenticate`, `confirmAuthentication`, and `operate($parameters, TransactionType)` for any other.
A refusal of Redsys (`errorCode`) is a `RedsysException`; an HTTP failure is the runtime's `ApiException`.

HTTP is the runtime's: pass a `Dmorenof\SdkRuntime\Configuration` to `new Gateway(...)` for timeouts, a proxy, retries or middleware.

## What is verified, and what is not

- **Against Redsys's test environment** (merchant 999008881, the public test data): the first REST call of a card payment, signed by
  this SDK with `HMAC_SHA512_V2`, was accepted, and the signature of Redsys's answer was verified by `Gateway` (an answer
  with `protocolVersion 2.2.0` and a real `threeDSServerTransID`). That run is `tests/live/test-environment.php`
  (workflow *Redsys test environment*, manual); a wrong signature would have been answered with `SIS0042`. It also showed that
  Redsys answers with `threeDSInfo` values beyond the three of the manual (`CardConfiguration`), which the model keeps as text.
  A complete payment, with a 3-D Secure challenge, was not made.
- **The signature**, all three schemes: the unit tests compare it with values produced by Redsys's own PHP library
  (`redsys-lib` 2.0.1, which this SDK does not use) for the same input.
- **The parameters and their limits**: the manual *Integración utilizando HMAC SHA256* (Annex 8.1) for the length and format of
  the common ones; the others are typed but not constrained. The names come from Redsys's library and manuals.
- **Not covered yet**: the operation query API (`apis.redsys.es`), InSite, Bizum flows beyond the parameters, and the
  descriptions of every response code (the common ones are described; see Redsys's table).

The test data of the manual (merchant 999008881, terminal 1, key `sq7HjrUOBfKmC576ILgskD5srU870gJ7`, card 4548 8120 4940 0004, 12/20,
CVV2 123, 3-D Secure code 123456) is Redsys's, public, and for the test environment only.

## Requirements

PHP 8.4 or later, `ext-openssl`, `dmorenof/sdk-runtime` 1.12 or later.
