<?php
declare(strict_types=1);

namespace Redsys\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redsys\Dto\Emv3ds;
use Redsys\Dto\Parameters;
use Redsys\ThreeDsInfo;
use Redsys\TransactionType;

/** Needs PHP 8.4: the model checks every value where it is assigned, with property hooks. */
final class ParametersTest extends TestCase
{
    public function testTheParametersAreStringsUnderTheNamesOfTheManual(): void
    {
        $payment = new Parameters();
        $payment->order = '1446117555';
        $payment->amount = 145;
        $payment->currency = '978';
        $payment->transactionType = TransactionType::Authorisation;
        $payment->urlOk = 'https://shop.example/ok';
        self::assertEquals([
            'DS_MERCHANT_ORDER' => '1446117555', 'DS_MERCHANT_AMOUNT' => '145', 'DS_MERCHANT_CURRENCY' => '978',
            'DS_MERCHANT_TRANSACTIONTYPE' => '0', 'DS_MERCHANT_URLOK' => 'https://shop.example/ok',
        ], $payment->parameters());
    }

    public function testTheThreeDSecureDataStaysAnObjectAndKeepsItsTypes(): void
    {
        $payment = new Parameters();
        $payment->order = '1446117555';
        $emv3ds = new Emv3ds();
        $emv3ds->threeDSInfo = ThreeDsInfo::AuthenticationData;
        $emv3ds->browserJavascriptEnabled = true;
        $emv3ds->browserTZ = '-60';
        $payment->emv3ds = $emv3ds;
        self::assertSame(
            ['threeDSInfo' => 'AuthenticationData', 'browserJavascriptEnabled' => true, 'browserTZ' => '-60'],
            $payment->parameters()['DS_MERCHANT_EMV3DS'],
        );
    }

    public function testAParameterTheModelDoesNotDeclareIsSentUnderItsName(): void
    {
        $payment = new Parameters();
        $payment->order = '1446117555';
        $payment->setAdditionalProperty('DS_MERCHANT_SOMETHINGNEW', 'x');
        self::assertSame('x', $payment->parameters()['DS_MERCHANT_SOMETHINGNEW']);
    }

    /** @return iterable<string, array{string, mixed}> */
    public static function refused(): iterable
    {
        yield 'an order that does not start with four digits' => ['order', 'abc1234567'];
        yield 'an order of 13 characters' => ['order', '1234567890123'];
        yield 'an order with a symbol' => ['order', '1234-567'];
        yield 'a merchant code of 10 digits' => ['merchantCode', '1234567890'];
        yield 'a terminal of 4 digits' => ['terminal', '1234'];
        yield 'a negative amount' => ['amount', -1];
        yield 'a currency in letters' => ['currency', 'EUR'];
        yield 'a description of 126 characters' => ['productDescription', str_repeat('x', 126)];
        yield 'a card number of letters' => ['cardNumber', 'abcdefghijkl'];
        yield 'an expiry date of the wrong length' => ['cardExpiryDate', '12/49'];
        yield 'a security code of two digits' => ['cvv2', '12'];
        yield 'a language of letters' => ['consumerLanguage', 'es'];
    }

    #[DataProvider('refused')]
    public function testAValueThatBreaksTheManualIsRefusedWhereItIsAssigned(string $property, mixed $value): void
    {
        $payment = new Parameters();
        $this->expectException(\Throwable::class);
        $payment->{$property} = $value;
    }

    public function testAnOperationWithoutAnOrderNumberIsRefused(): void
    {
        $payment = new Parameters();
        $payment->amount = 145;
        $this->expectException(\Throwable::class);
        $payment->parameters();
    }
}
