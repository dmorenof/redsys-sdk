<?php
declare(strict_types=1);

namespace Redsys\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redsys\Signature;
use Redsys\SignatureVersion;

/**
 * Every expected value here was produced by Redsys's own PHP library (`redsys-lib` 2.0.1) for the same input, which the
 * code under test does not use. The key is Redsys's generic test key.
 */
final class SignatureTest extends TestCase
{
    private const PARAMETERS = 'eyJEU19NRVJDSEFOVF9BTU9VTlQiOiIxNDUiLCJEU19NRVJDSEFOVF9PUkRFUiI6IjE0NDYxMTc1NTUifQ';

    /** @return iterable<string, array{string, string, string}> */
    public static function vectors(): iterable
    {
        yield 'SHA-256 V1' => ['HMAC_SHA256_V1', '1446117555', 'kVMizxx8WDiMn4QvgF7E0bm_wdql4Ln_-JhfGE-qZUU='];
        yield 'SHA-512 V1' => ['HMAC_SHA512_V1', '1446117555', 'Evsd9vQrI2pPRzkdM6P24ZDARodjCN3cWALUOx83rJWYTeOHVE3ECl8J5s3L0ZVKt8BvR1Rj8vLn9QDQmgjcPA=='];
        yield 'SHA-512 V2' => ['HMAC_SHA512_V2', '1446117555', 'Evsd9vQrI2pPRzkdM6P24ZDARodjCN3cWALUOx83rJWYTeOHVE3ECl8J5s3L0ZVKt8BvR1Rj8vLn9QDQmgjcPA'];
        yield 'V2, an order of one character' => ['HMAC_SHA512_V2', '1', 'ZxVPC-PkOsjJcj6ZFZMElTyEgw2MAUFhOxkUuZQW1v18JW-MzBI4b8SkFDJmNHLPF6eXM_vaCo9rLUXPn0l1OA'];
        yield 'V2, an order of one block' => ['HMAC_SHA512_V2', '12345678', 'sPlTn01AXNQPQ9b38Q1GfgMWoz7Y59nZgKfZ7jOmkiNylU1CruhcceP7EwhDfLSZnIl6t32pukXQUI6_cHCdiQ'];
        yield 'V2, an order of twelve digits' => ['HMAC_SHA512_V2', '123456789012', '2iBYvtdxUaTK2CgZ8U25WYLLcvFfOeD5KEvTJNWDE47DshM9fzusmuSd6Isv0KJWqLlo73mch-ahCIIL90Gp-A'];
        yield 'V2, an order with letters' => ['HMAC_SHA512_V2', 'abcd1234EFGH', 'R3nwxWws1oYVorCPnJf5chtofvT_eJmkBV-GLgMtVNHUstTV8CDF0sB8M0rFODqonHBInm7OeGDGLYyfWZw9rg'];
    }

    #[DataProvider('vectors')]
    public function testItGivesTheSignatureOfRedsysOwnLibrary(string $version, string $order, string $expected): void
    {
        self::assertSame($expected, Signature::sign(self::PARAMETERS, $order, Support::KEY, SignatureVersion::from($version)));
    }

    public function testVerifyAcceptsEverySpellingOfTheSameSignature(): void
    {
        $url = 'Evsd9vQrI2pPRzkdM6P24ZDARodjCN3cWALUOx83rJWYTeOHVE3ECl8J5s3L0ZVKt8BvR1Rj8vLn9QDQmgjcPA';
        foreach ([$url, $url . '==', strtr($url, '-_', '+/')] as $received) {
            self::assertTrue(Signature::verify($received, self::PARAMETERS, '1446117555', Support::KEY), $received);
        }
    }

    public function testVerifyRefusesAnotherSignatureParametersOrderKeyOrScheme(): void
    {
        $good = Signature::sign(self::PARAMETERS, '1446117555', Support::KEY);
        self::assertFalse(Signature::verify('A' . substr($good, 1), self::PARAMETERS, '1446117555', Support::KEY));
        self::assertFalse(Signature::verify($good, self::PARAMETERS . 'A', '1446117555', Support::KEY));
        self::assertFalse(Signature::verify($good, self::PARAMETERS, '1446117556', Support::KEY));
        self::assertFalse(Signature::verify($good, self::PARAMETERS, '1446117555', 'another key of 32 characters.....'));
        self::assertFalse(Signature::verify('', self::PARAMETERS, '1446117555', Support::KEY));
    }

    public function testTheOlderSchemeNeedsTheKeyInBase64Of24Bytes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Signature::sign(self::PARAMETERS, '1446117555', 'not a key', SignatureVersion::HmacSha256V1);
    }
}
