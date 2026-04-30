<?php

namespace NotificationChannels\WhatsAppBridge;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use NotificationChannels\WhatsAppBridge\Exceptions\CouldNotSendNotification;

/**
 * Class WhatsAppClient
 *
 * HTTP client that communicates with the Baileys WhatsApp bridge REST API.
 *
 * Bridge endpoints used:
 *   POST /send  → { jid: string, text: string }
 *   GET  /status → { ok: bool, connected: bool, sessions: int }
 */
final class WhatsAppClient
{
    private Client $http;
    private string $bridgeUrl;
    private ?string $apiKey;
    private int $timeout;

    public function __construct(
        Client $http,
        string $bridgeUrl,
        ?string $apiKey = null,
        int $timeout = 10
    ) {
        $this->http      = $http;
        $this->bridgeUrl = rtrim($bridgeUrl, '/');
        $this->apiKey    = $apiKey;
        $this->timeout   = $timeout;
    }

    /**
     * Send a WhatsApp message.
     *
     * @param string $jid  Recipient JID (e.g. 905551234567@s.whatsapp.net)
     * @param string $text Message text
     *
     * @return array Bridge response: { ok: true }
     *
     * @throws CouldNotSendNotification
     */
    public function send(string $jid, string $text): array
    {
        try {
            $response = $this->http->post(
                $this->bridgeUrl . '/send',
                [
                    'timeout' => $this->timeout,
                    'headers' => $this->buildHeaders(),
                    'json'    => [
                        'to'   => $jid,
                        'text' => $text,
                    ],
                ]
            );

            $body = json_decode((string) $response->getBody(), true) ?? [];

            if (empty($body['ok'])) {
                throw CouldNotSendNotification::bridgeReturnedError(
                    $body['error'] ?? 'Unknown bridge error'
                );
            }

            return $body;

        } catch (GuzzleException $e) {
            throw CouldNotSendNotification::bridgeConnectionFailed($e->getMessage());
        }
    }

    /**
     * Check bridge connection status.
     *
     * @return array { ok: bool, connected: bool, sessions: int }
     *
     * @throws CouldNotSendNotification
     */
    public function status(): array
    {
        try {
            $response = $this->http->get(
                $this->bridgeUrl . '/status',
                [
                    'timeout' => $this->timeout,
                    'headers' => $this->buildHeaders(),
                ]
            );

            return json_decode((string) $response->getBody(), true) ?? [];

        } catch (GuzzleException $e) {
            throw CouldNotSendNotification::bridgeConnectionFailed($e->getMessage());
        }
    }

    /**
     * Build request headers.
     */
    private function buildHeaders(): array
    {
        $headers = ['Accept' => 'application/json'];

        if ($this->apiKey !== null && $this->apiKey !== '') {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }

        return $headers;
    }
}
