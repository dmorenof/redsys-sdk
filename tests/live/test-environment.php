<?php
declare(strict_types=1);

/**
 * Talks to Redsys's TEST environment with the generic test data Redsys publishes (merchant 999008881, terminal 1, test card):
 * the first REST call of a card payment, signed by the SDK. If Redsys accepts the signature it answers with the verification
 * the card needs (and its own signature, which the SDK verifies); a wrong signature is answered with SIS0042.
 *
 * Usage: php tests/live/test-environment.php
 */
require __DIR__ . '/../../vendor/autoload.php';

use Redsys\{Environment, Gateway, Merchant, RedsysException};
use Redsys\Dto\Parameters;

$gateway = new Gateway(new Merchant('999008881', 1, 'sq7HjrUOBfKmC576ILgskD5srU870gJ7', Environment::Test));

$payment = new Parameters();
$payment->order = substr((string) time(), -10) . 'ab';
$payment->amount = 999;
$payment->cardNumber = '4548810000000003';
$payment->cardExpiryDate = '4912';
$payment->cvv2 = '123';

try {
    $result = $gateway->init($payment);
    echo "ANSWER VERIFIED. response=", var_export($result->response ?? null, true), ' order=', $result->order ?? '?', "\n";
    echo 'emv3ds: ', json_encode($result->emv3ds?->jsonSerialize()), "\n";
    exit(0);
} catch (RedsysException $error) {
    echo 'REDSYS ERROR ', $error->errorCode ?? '?', ': ', $error->getMessage(), "\n";
    exit(2);
}
