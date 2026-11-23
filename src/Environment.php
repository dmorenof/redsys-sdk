<?php
declare(strict_types=1);

namespace Redsys;

/** The TPV Virtual a payment goes to. The test environment is identical to the live one, but its payments have no accounting value. */
enum Environment: string
{
    case Development = 'dev';
    case Integration = 'integration';
    case Test = 'test';
    case Live = 'live';

    /** The prefix Redsys puts before the credentials of an API key (`TEST_…`). */
    public static function fromApiKeyPrefix(string $prefix): self
    {
        return match ($prefix) {
            'DESA' => self::Development,
            'INTE' => self::Integration,
            'TEST' => self::Test,
            'PROD' => self::Live,
            default => throw new \InvalidArgumentException("Unknown environment '$prefix' in the API key: expected DESA, INTE, TEST or PROD."),
        };
    }

    /** The name this environment has among the servers of {@see Client}. */
    public function serverName(): string
    {
        return $this->value;
    }

    /** Where the payer's browser posts a payment form (connection by redirection). */
    public function redirectUrl(): string
    {
        return Client::serverUrl($this->value) . '/sis/realizarPago';
    }
}
