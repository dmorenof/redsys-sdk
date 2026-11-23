<?php
declare(strict_types=1);

namespace Redsys;

use Dmorenof\SdkRuntime\ApiClient;
use Redsys\Dto\Envelope;

/**
 * The two REST operations of the TPV Virtual, on the runtime's client: the transport, retries, middleware and timeouts of
 * `Configuration` are the runtime's. Most code uses {@see Gateway}, which signs and reads the envelopes.
 */
class Client extends ApiClient
{
    /** One server per environment, chosen with `Configuration::$server` or {@see Environment}. */
    protected const array SERVERS = [
        ['url' => 'https://sis-d.redsys.es', 'name' => 'dev'],
        ['url' => 'https://sis-i.redsys.es:25443', 'name' => 'integration'],
        ['url' => 'https://sis-t.redsys.es:25443', 'name' => 'test'],
        ['url' => 'https://sis.redsys.es', 'name' => 'live'],
    ];

    private const array INIT = [
        'method' => 'POST',
        'path' => '/sis/rest/iniciaPeticionREST',
        'requestBody' => ['type' => '\\Redsys\\Dto\\Envelope', 'nullable' => false, 'required' => true, 'rules' => []],
        'responseType' => '\\Redsys\\Dto\\Envelope',
    ];

    private const array TREAT = [
        'method' => 'POST',
        'path' => '/sis/rest/trataPeticionREST',
        'requestBody' => ['type' => '\\Redsys\\Dto\\Envelope', 'nullable' => false, 'required' => true, 'rules' => []],
        'responseType' => '\\Redsys\\Dto\\Envelope',
    ];

    /** Asks which verification a card needs (3-D Secure), before the payment: `iniciaPeticionREST`. */
    public function init(Envelope $request): Envelope
    {
        /** @var Envelope $reply */
        $reply = $this->executeBody(self::operation(self::INIT), $request);
        return $reply;
    }

    /** Carries out an operation: `trataPeticionREST`. */
    public function treat(Envelope $request): Envelope
    {
        /** @var Envelope $reply */
        $reply = $this->executeBody(self::operation(self::TREAT), $request);
        return $reply;
    }
}
