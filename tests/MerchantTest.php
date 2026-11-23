<?php
declare(strict_types=1);

namespace Redsys\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redsys\Environment;
use Redsys\Merchant;

final class MerchantTest extends TestCase
{
    /** `TEST_` and the Base64 of `999008881_1_sq7HjrUOBfKmC576ILgskD5srU870gJ7`: what Redsys's library reads. */
    private const API_KEY = 'TEST_OTk5MDA4ODgxXzFfc3E3SGpyVU9CZkttQzU3NklMZ3NrRDVzclU4NzBnSjc';

    public function testAnApiKeyCarriesTheMerchantTheTerminalTheKeyAndTheEnvironment(): void
    {
        $merchant = Merchant::fromApiKey(self::API_KEY);
        self::assertSame('999008881', $merchant->code);
        self::assertSame(1, $merchant->terminal);
        self::assertSame(Support::KEY, $merchant->key);
        self::assertSame(Environment::Test, $merchant->environment);
    }

    /** @return iterable<string, array{string, Environment}> */
    public static function prefixes(): iterable
    {
        yield 'DESA' => ['DESA', Environment::Development];
        yield 'INTE' => ['INTE', Environment::Integration];
        yield 'TEST' => ['TEST', Environment::Test];
        yield 'PROD' => ['PROD', Environment::Live];
    }

    #[DataProvider('prefixes')]
    public function testEveryEnvironmentOfRedsysIsKnown(string $prefix, Environment $environment): void
    {
        self::assertSame($environment, Merchant::fromApiKey($prefix . '_' . rtrim(strtr(base64_encode('999008881_1_' . Support::KEY), '+/', '-_'), '='))->environment);
    }

    /** @return iterable<string, array{string}> */
    public static function notKeys(): iterable
    {
        yield 'empty' => [''];
        yield 'no prefix' => ['OTk5MDA4ODgxXzFfa2V5'];
        yield 'an unknown environment' => ['PRE_' . 'OTk5MDA4ODgxXzFfa2V5'];
        yield 'not Base64' => ['TEST_%%%'];
        yield 'two fields only' => ['TEST_' . 'OTk5MDA4ODgxXzE'];
    }

    #[DataProvider('notKeys')]
    public function testSomethingThatIsNotAnApiKeyIsRefused(string $apiKey): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Merchant::fromApiKey($apiKey);
    }

    public function testTheKeyIsNotShownWhenTheMerchantIsDumped(): void
    {
        $dump = print_r(Merchant::fromApiKey(self::API_KEY), true);
        self::assertStringNotContainsString(Support::KEY, $dump);
        self::assertStringContainsString('999008881', $dump);
    }

    public function testTheCodeAndTheTerminalHaveTheShapeRedsysGives(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Merchant('99900888100', 1, Support::KEY);
    }
}
