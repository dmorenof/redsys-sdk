# Redsys SDK

Payments with Redsys (TPV Virtual) from PHP 8.4: a payment form signed the way Redsys requires, and notifications whose
signature is verified before anything in them is read. Built on [sdk-runtime](https://github.com/dmorenof/sdk-runtime):
the payment is a typed model whose every value is checked where it is assigned, against the lengths and formats of
Redsys's manual.

Redsys publishes no machine-readable contract (no OpenAPI), so this SDK is written by hand, which is what the runtime is
for as well: the models, validation and errors are the runtime's; only what is specific to Redsys is here.

## Pay

```php
use Redsys\{Environment, PaymentForm, PaymentRequest, TransactionType};

$payment = new PaymentRequest();
$payment->merchantCode = '999008881';
$payment->terminal = '1';
$payment->transactionType = TransactionType::Authorisation;
$payment->amount = 145;                    // 1.45 EUR: the last two digits are cents
$payment->currency = '978';                // euros
$payment->order = '1446117555';            // 12 characters at most, the first four digits
$payment->merchantUrl = 'https://shop.example/redsys/notification';
$payment->urlOk = 'https://shop.example/paid';
$payment->urlKo = 'https://shop.example/failed';

echo PaymentForm::create($payment, $secretKey, Environment::Test)->html();   // sends the payer to Redsys
```

A value that breaks the manual (`$payment->order = 'abc'`, an amount below zero, a terminal of four digits) is refused
at that line, not by Redsys's server later with `SIS0431`.

## Be told

```php
use Redsys\{InvalidNotification, Notification};

try {
    $result = Notification::fromPost($_POST, $secretKey);
} catch (InvalidNotification $error) {
    http_response_code(400);               // not signed by Redsys: ignore it
    exit;
}
if ($result->authorised()) {
    markAsPaid($result->order, $result->amount);   // Ds_Response 0000-0099 (900 refunds, 400 cancellations)
}
```

Nothing in a notification is available before its signature has been verified, in either Base64 alphabet, and a name in
capitals (`DS_ORDER`) or in camel case (`Ds_Order`) reads the same.

## What is verified, and what is not

- **The signature** (3DES key derivation from the order number, HMAC SHA256): the example of the manual's connection by
  redirection gives, with Redsys's generic test key, the same signature in this code, in the `openssl` command line and in
  Python's `cryptography`. The manual's own signature example is a placeholder, so there is no Redsys-published vector:
  the proof that Redsys accepts it is a payment in the test environment (`Environment::Test`), which needs network access
  to `sis-t.redsys.es:25443` and is not part of the automated tests.
- **The parameters and their limits**: Annex 8.1 and 8.2 of the manual *Integración utilizando HMAC SHA256* (version 2.1).
  Only the parameters most shops use are modelled; ask for the rest.
- **Not covered yet**: the direct (REST/Web Service) connection, recurring and reference payments, InSite. The test
  data of the manual (merchant 999008881, terminal 1, key `sq7HjrUOBfKmC576ILgskD5srU870gJ7`, card 4548 8120 4940 0004,
  12/20, CVV2 123, 3-D Secure code 123456) is Redsys's, public, and for the test environment only.

## Requirements

PHP 8.4 or later, `ext-openssl`, `dmorenof/sdk-runtime`.
