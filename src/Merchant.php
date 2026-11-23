<?php
declare(strict_types=1);

namespace Redsys;

/** Who is paid and where: the merchant code (FUC), the terminal, the signing key and the environment. */
final readonly class Merchant
{
    public function __construct(
        public string $code,
        public int $terminal,
        #[\SensitiveParameter] public string $key,
        public Environment $environment = Environment::Live,
    ) {
        if (preg_match('/^[0-9]{1,9}$/', $code) !== 1 || $terminal < 1 || $terminal > 999) {
            throw new \InvalidArgumentException('The merchant code is up to 9 digits and the terminal a number from 1 to 999.');
        }
    }

    /**
     * From the API key of the Redsys portal, `ENV_` and the Base64 of `code_terminal_key`: the environment, the merchant and the key in one
     * string. It is as secret as the key.
     */
    public static function fromApiKey(#[\SensitiveParameter] string $apiKey): self
    {
        $parts = explode('_', $apiKey, 2);
        $payload = count($parts) === 2 && strlen($parts[0]) === 4 ? base64_decode(strtr($parts[1], '-_', '+/') . str_repeat('=', (4 - strlen($parts[1]) % 4) % 4), true) : false;
        $fields = $payload === false ? [] : explode('_', $payload, 3);
        if (count($fields) !== 3 || !ctype_digit($fields[1])) {
            throw new \InvalidArgumentException('That is not an API key of the Redsys portal: expected ENV_ followed by the Base64 of merchant_terminal_key.');
        }
        return new self($fields[0], (int) $fields[1], $fields[2], Environment::fromApiKeyPrefix($parts[0]));
    }

    public function __debugInfo(): array
    {
        return ['code' => $this->code, 'terminal' => $this->terminal, 'key' => '***', 'environment' => $this->environment];
    }
}
