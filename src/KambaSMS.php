<?php
namespace KambaSMS;

use KambaSMS\Exceptions\KambaAPIException;
use KambaSMS\Exceptions\KambaException;
use KambaSMS\Resources\SmsResource;
use KambaSMS\Resources\AccountResource;

class KambaSMS {
    private string $apiKey;
    private string $baseUrl;

    public readonly SmsResource $sms;
    public readonly AccountResource $account;

    public function __construct(string $apiKey, string $baseUrl = 'https://nexasms-api.onrender.com') {
        if (empty($apiKey)) {
            throw new KambaException('A apiKey é obrigatória para inicializar o KambaSMS.');
        }
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');

        $this->sms = new SmsResource($this);
        $this->account = new AccountResource($this);
    }

    /**
     * @throws KambaAPIException
     * @throws KambaException
     */
    public function request(string $method, string $endpoint, array $data = []): array {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init();

        $headers = [
            'Content-Type: application/json',
            'x-api-key: ' . $this->apiKey
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_TIMEOUT => 30,
        ]);

        if ($method === 'POST' || $method === 'PUT') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new KambaException("Erro de rede: " . $curlError);
        }

        $result = json_decode($response, true);

        if ($httpCode >= 400) {
            $message = $result['error'] ?? 'Erro desconhecido na API KambaSMS';
            throw new KambaAPIException($message, $httpCode, $result);
        }

        return $result ?? [];
    }
}