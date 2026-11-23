<?php
declare(strict_types=1);

namespace Redsys\Tests;

use Dmorenof\SdkRuntime\Configuration;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Redsys\Dto\Parameters;
use Redsys\Environment;
use Redsys\Gateway;
use Redsys\InvalidSignatureException;
use Redsys\Merchant;
use Redsys\RedsysException;
use Redsys\Signature;
use Redsys\SignatureVersion;
use Redsys\TransactionType;

/** Needs PHP 8.4. The HTTP side is a mock: what is checked is what the gateway sends and how it reads what comes back. */
final class GatewayTest extends TestCase
{
    /** @var list<RequestInterface> */
    private array $sent = [];

    private function gateway(Response ...$answers): Gateway
    {
        $this->sent = [];
        $stack = HandlerStack::create(new MockHandler($answers));
        $stack->push(Middleware::mapRequest(function (RequestInterface $request): RequestInterface {
            $this->sent[] = $request;
            return $request;
        }));
        $configuration = new Configuration();
        $configuration->httpClient = new Guzzle(['handler' => $stack]);
        return new Gateway(new Merchant('999008881', 1, Support::KEY, Environment::Test), $configuration);
    }

    private static function json(array $body): Response
    {
        return new Response(200, ['Content-Type' => 'application/json;charset=UTF-8'], json_encode($body, JSON_THROW_ON_ERROR));
    }

    private static function payment(): Parameters
    {
        $payment = new Parameters();
        $payment->order = '1446117555';
        $payment->amount = 145;
        return $payment;
    }

    /** @return array<string, mixed> The parameters of the request that was sent, once its signature has been checked. */
    private function sentParameters(int $index = 0): array
    {
        $body = json_decode((string) $this->sent[$index]->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $decoded = json_decode(base64_decode(strtr($body['Ds_MerchantParameters'], '-_', '+/'), true), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('HMAC_SHA512_V2', $body['Ds_SignatureVersion']);
        self::assertTrue(Signature::verify($body['Ds_Signature'], $body['Ds_MerchantParameters'], $decoded['DS_MERCHANT_ORDER'], Support::KEY, SignatureVersion::HmacSha512V2));
        return $decoded;
    }

    public function testAnAuthorisationIsSignedFilledInAndSentToTheTestServer(): void
    {
        $gateway = $this->gateway(self::json(Support::signed(['Ds_Order' => '1446117555', 'Ds_Amount' => '145', 'Ds_Response' => '0000', 'Ds_AuthorisationCode' => '123456'])));
        $result = $gateway->authorise(self::payment());

        self::assertSame('POST', $this->sent[0]->getMethod());
        self::assertSame('https://sis-t.redsys.es:25443/sis/rest/trataPeticionREST', (string) $this->sent[0]->getUri());
        self::assertEqualsCanonicalizing([
            'DS_MERCHANT_ORDER' => '1446117555', 'DS_MERCHANT_AMOUNT' => '145', 'DS_MERCHANT_MERCHANTCODE' => '999008881',
            'DS_MERCHANT_TERMINAL' => '1', 'DS_MERCHANT_CURRENCY' => '978', 'DS_MERCHANT_TRANSACTIONTYPE' => '0',
        ], $this->sentParameters());
        self::assertTrue($result->authorised());
        self::assertSame('123456', $result->authorisationCode);
        self::assertSame('145', $result->amount);
    }

    public function testWhatTheCallerSetWinsOverWhatTheGatewayFillsIn(): void
    {
        $gateway = $this->gateway(self::json(Support::signed(['Ds_Order' => '1446117555', 'Ds_Response' => '0000'])));
        $payment = self::payment();
        $payment->currency = '840';
        $payment->terminal = '2';
        $gateway->authorise($payment);
        $sent = $this->sentParameters();
        self::assertSame('840', $sent['DS_MERCHANT_CURRENCY']);
        self::assertSame('2', $sent['DS_MERCHANT_TERMINAL']);
    }

    public function testTheCallersParametersAreLeftAsTheyWere(): void
    {
        $gateway = $this->gateway(self::json(Support::signed(['Ds_Order' => '1446117555', 'Ds_Response' => '0000'])));
        $payment = self::payment();
        $gateway->refund($payment);
        self::assertEquals(['DS_MERCHANT_ORDER' => '1446117555', 'DS_MERCHANT_AMOUNT' => '145'], $payment->parameters());
    }

    public function testEachOperationCarriesItsTransactionType(): void
    {
        $answers = array_fill(0, 7, self::json(Support::signed(['Ds_Order' => '1446117555', 'Ds_Response' => '0000'])));
        $gateway = $this->gateway(...$answers);
        $payment = self::payment();
        $gateway->preauthorise($payment);
        $gateway->confirmPreauthorisation($payment);
        $gateway->refund($payment);
        $gateway->void($payment);
        $gateway->cancel($payment);
        $gateway->partiallyConfirm($payment);
        $gateway->authenticate($payment);
        self::assertSame(['1', '2', '3', '9', '45', '48', '7'], array_map(fn (int $i): string => $this->sentParameters($i)['DS_MERCHANT_TRANSACTIONTYPE'], range(0, 6)));
    }

    public function testTheFirstCallOfARestPaymentAsksWhichVerificationTheCardNeeds(): void
    {
        $gateway = $this->gateway(self::json(Support::signed(['Ds_Order' => '1446117555', 'Ds_Response' => '', 'Ds_EMV3DS' => ['protocolVersion' => '2.2.0', 'threeDSMethodURL' => 'https://acs.example/method']])));
        $payment = self::payment();
        $payment->cardNumber = '4548810000000003';
        $payment->cardExpiryDate = '4912';
        $payment->cvv2 = '123';
        $result = $gateway->init($payment);

        self::assertSame('https://sis-t.redsys.es:25443/sis/rest/iniciaPeticionREST', (string) $this->sent[0]->getUri());
        self::assertSame(['threeDSInfo' => 'CardData'], $this->sentParameters()['DS_MERCHANT_EMV3DS']);
        self::assertSame('2.2.0', $result->emv3ds->protocolVersion);
        self::assertSame('https://acs.example/method', $result->emv3ds->threeDSMethodURL);
    }

    public function testTheChallengeAnswerCarriesTheOrderTheProtocolVersionAndTheIssuersResponse(): void
    {
        $gateway = $this->gateway(self::json(Support::signed(['Ds_Order' => '1446117555', 'Ds_Response' => '0000'])));
        $result = $gateway->challengeResponse('1446117555', '2.2.0', 'eyJ0aHJlZURTU2VydmVyVHJhbnNJRCI6IngifQ');
        $sent = $this->sentParameters();
        self::assertEquals(['threeDSInfo' => 'ChallengeResponse', 'protocolVersion' => '2.2.0', 'cres' => 'eyJ0aHJlZURTU2VydmVyVHJhbnNJRCI6IngifQ'], $sent['DS_MERCHANT_EMV3DS']);
        self::assertArrayNotHasKey('DS_MERCHANT_TRANSACTIONTYPE', $sent);
        self::assertTrue($result->authorised());
    }

    public function testAStepOfTheFlowThisSdkDoesNotNameIsKept(): void
    {
        $gateway = $this->gateway();
        $result = $gateway->notification(Support::signed(['Ds_Order' => '1446117555', 'Ds_Response' => '', 'Ds_EMV3DS' => ['threeDSInfo' => 'CardConfiguration', 'protocolVersion' => '2.2.0']]));
        self::assertSame('CardConfiguration', $result->emv3ds->threeDSInfo);
    }

    public function testAnAnswerThatAsksForAChallengeGivesTheFormThatSendsThePayerToTheIssuer(): void
    {
        $gateway = $this->gateway(self::json(Support::signed(['Ds_Order' => '1446117555', 'Ds_Response' => '', 'Ds_EMV3DS' => ['acsURL' => 'https://acs.example/challenge', 'creq' => 'abc', 'protocolVersion' => '2.2.0']])));
        $result = $gateway->authorise(self::payment());
        $form = $gateway->challengeForm($result);
        self::assertSame('https://acs.example/challenge', $form->action);
        self::assertSame(['CReq' => 'abc'], $form->fields);
        self::assertFalse($result->authorised());
    }

    public function testAnAnswerWithoutAChallengeCannotGiveAChallengeForm(): void
    {
        $gateway = $this->gateway(self::json(Support::signed(['Ds_Order' => '1446117555', 'Ds_Response' => '0000'])));
        $this->expectException(RedsysException::class);
        $gateway->challengeForm($gateway->authorise(self::payment()));
    }

    public function testARefusalOfRedsysIsAnExceptionWithItsCode(): void
    {
        $gateway = $this->gateway(self::json(['errorCode' => 'SIS0057']));
        try {
            $gateway->authorise(self::payment());
            self::fail('A refusal must be an exception.');
        } catch (RedsysException $error) {
            self::assertSame('SIS0057', $error->errorCode);
            self::assertNotInstanceOf(InvalidSignatureException::class, $error);
        }
    }

    public function testAnAnswerSignedWithAnotherKeyIsNotTrusted(): void
    {
        $gateway = $this->gateway(self::json(Support::signed(['Ds_Order' => '1446117555', 'Ds_Response' => '0000'], base64_encode(str_repeat('k', 24)))));
        $this->expectException(InvalidSignatureException::class);
        $gateway->authorise(self::payment());
    }

    public function testAnAnswerWhoseParametersWereChangedIsNotTrusted(): void
    {
        $fields = Support::signed(['Ds_Order' => '1446117555', 'Ds_Amount' => '1', 'Ds_Response' => '0000']);
        $fields['Ds_MerchantParameters'] = rtrim(strtr(base64_encode('{"Ds_Order":"1446117555","Ds_Amount":"999","Ds_Response":"0000"}'), '+/', '-_'), '=');
        $gateway = $this->gateway(self::json($fields));
        $this->expectException(InvalidSignatureException::class);
        $gateway->authorise(self::payment());
    }

    public function testAnHttpErrorIsTheRuntimesException(): void
    {
        $gateway = $this->gateway(new Response(503, [], 'unavailable'));
        $this->expectException(\Dmorenof\SdkRuntime\ApiException::class);
        $gateway->authorise(self::payment());
    }

    public function testNotificationsAreVerifiedAndNamesInCapitalsReadTheSame(): void
    {
        $gateway = $this->gateway();
        foreach ([['Ds_Order' => '1446117555', 'Ds_Response' => '0000'], ['DS_ORDER' => '1446117555', 'DS_RESPONSE' => '0000', 'DS_AUTHORISATIONCODE' => '77']] as $parameters) {
            $result = $gateway->notification(Support::signed($parameters));
            self::assertSame('1446117555', $result->order);
            self::assertTrue($result->authorised());
        }
        self::assertSame('77', $result->authorisationCode);
    }

    public function testANotificationOfAnOlderSignatureSchemeIsVerifiedToo(): void
    {
        $gateway = $this->gateway();
        $result = $gateway->notification(Support::signed(['Ds_Order' => '1446117555', 'Ds_Response' => '0101'], Support::KEY, SignatureVersion::HmacSha256V1));
        self::assertFalse($result->authorised());
        self::assertSame('Card expired', $result->describeResponse());
    }

    public function testAnUnknownParameterOfAnAnswerIsKept(): void
    {
        $gateway = $this->gateway();
        $result = $gateway->notification(Support::signed(['Ds_Order' => '1446117555', 'Ds_Response' => '0000', 'Ds_SomethingNew' => 'x']));
        self::assertSame('x', $result->getAdditionalProperty('DS_SOMETHINGNEW'));
    }

    public function testAMalformedNotificationIsRefused(): void
    {
        $gateway = $this->gateway();
        foreach ([[], ['Ds_SignatureVersion' => 'HMAC_SHA512_V2'], ['Ds_SignatureVersion' => 'NOPE', 'Ds_MerchantParameters' => 'e30', 'Ds_Signature' => 'x'], ['Ds_SignatureVersion' => 'HMAC_SHA512_V2', 'Ds_MerchantParameters' => '%%%', 'Ds_Signature' => 'x'], ['Ds_SignatureVersion' => 'HMAC_SHA512_V2', 'Ds_MerchantParameters' => 'e30', 'Ds_Signature' => 'x']] as $fields) {
            try {
                $gateway->notification($fields);
                self::fail('A malformed notification must be refused: ' . json_encode($fields));
            } catch (InvalidSignatureException) {
                self::addToAssertionCount(1);
            }
        }
    }

    public function testTheRedirectFormCarriesTheSignedParametersToTheEnvironmentOfTheMerchant(): void
    {
        $form = $this->gateway()->redirectForm(self::payment());
        self::assertSame('https://sis-t.redsys.es:25443/sis/realizarPago', $form->action);
        self::assertSame(['Ds_SignatureVersion', 'Ds_MerchantParameters', 'Ds_Signature'], array_keys($form->fields));
        $parameters = json_decode(base64_decode(strtr($form->fields['Ds_MerchantParameters'], '-_', '+/'), true), true);
        self::assertSame('0', $parameters['DS_MERCHANT_TRANSACTIONTYPE']);
        self::assertTrue(Signature::verify($form->fields['Ds_Signature'], $form->fields['Ds_MerchantParameters'], '1446117555', Support::KEY));
    }

    public function testTheRedirectFormTakesTheTypeOfTheOperation(): void
    {
        $form = $this->gateway()->redirectForm(self::payment(), TransactionType::Preauthorisation);
        $parameters = json_decode(base64_decode(strtr($form->fields['Ds_MerchantParameters'], '-_', '+/'), true), true);
        self::assertSame('1', $parameters['DS_MERCHANT_TRANSACTIONTYPE']);
    }

    public function testTheHtmlEscapesEveryValueAndCanWaitForTheClick(): void
    {
        $payment = self::payment();
        $payment->productDescription = 'Tom & "Jerry" <b>';
        $form = $this->gateway()->redirectForm($payment);
        self::assertStringNotContainsString('Tom & "Jerry"', $form->html(false));
        self::assertStringNotContainsString('<script>', $form->html(false));
        self::assertStringContainsString('.submit();</script>', $form->html());
    }

    public function testTheLiveEnvironmentIsAnotherServer(): void
    {
        $live = new Gateway(new Merchant('999008881', 1, Support::KEY, Environment::Live));
        self::assertSame('https://sis.redsys.es/sis/realizarPago', $live->redirectForm(self::payment())->action);
        self::assertSame('https://sis-d.redsys.es/sis/realizarPago', Environment::Development->redirectUrl());
        self::assertSame('https://sis-i.redsys.es:25443/sis/realizarPago', Environment::Integration->redirectUrl());
    }
}
