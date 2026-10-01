<?php
namespace KambaSMS;

use KambaSMS\Exceptions\KambaAPIException;
use KambaSMS\Exceptions\KambaException;
use KambaSMS\Exceptions\KambaValidationException;
use KambaSMS\Resources\AccountResource;
use KambaSMS\Resources\EmailResource;
use KambaSMS\Resources\LookupResource;
use KambaSMS\Resources\NotifyResource;
use KambaSMS\Resources\OtpResource;
use KambaSMS\Resources\SmsResource;
use KambaSMS\Resources\TransactionsResource;
use KambaSMS\Resources\VerifyResource;

class KambaSMS {
    private string $apiKey;
    private string $baseUrl;
    private int $timeout;
    public SmsResource $sms;
    public AccountResource $account;
    public OtpResource $otp;
    public VerifyResource $verify;
    public LookupResource $lookup;
    public NotifyResource $notify;
    public EmailResource $email;
    public TransactionsResource $transactions;

    public function __construct(string $apiKey, string $baseUrl = 'https://api.kambasms.ao', int $timeout = 30) {
        if (trim($apiKey) === '') throw new KambaValidationException('A apiKey é obrigatória.');
        $parts = parse_url($baseUrl);
        if (!$parts || !in_array($parts['scheme'] ?? '', ['http', 'https'], true) || empty($parts['host']) || isset($parts['query'], $parts['fragment'], $parts['user'])) {
            throw new KambaValidationException('baseUrl deve ser uma URL HTTP(S) sem credenciais, query ou fragmento.');
        }
        if ($timeout < 1) throw new KambaValidationException('timeout deve ser positivo.');
        $this->apiKey = $apiKey; $this->baseUrl = rtrim($baseUrl, '/'); $this->timeout = $timeout;
        $this->sms = new SmsResource($this); $this->account = new AccountResource($this); $this->otp = new OtpResource($this);
        $this->verify = new VerifyResource($this); $this->lookup = new LookupResource($this); $this->notify = new NotifyResource($this);
        $this->email = new EmailResource($this); $this->transactions = new TransactionsResource($this);
    }

    public function request(string $method, string $endpoint, ?array $data = null, array $options = []) {
        if (!str_starts_with($endpoint, '/') || str_starts_with($endpoint, '//')) throw new KambaValidationException('Endpoint inválido.');
        $headers = ['Content-Type: application/json', 'x-api-key: ' . $this->apiKey];
        foreach ([['Idempotency-Key', $options['idempotency_key'] ?? null, 200], ['X-Request-Id', $options['request_id'] ?? null, 100]] as [$name, $value, $max]) {
            if ($value !== null) {
                if (!is_string($value) || strlen($value) < 8 || strlen($value) > $max || !preg_match('/^[A-Za-z0-9._:-]+$/', $value)) throw new KambaValidationException("{$name} inválido.");
                $headers[] = $name . ': ' . $value;
            }
        }
        $responseHeaders = [];
        $ch = curl_init();
        curl_setopt_array($ch, [CURLOPT_URL => $this->baseUrl . $endpoint, CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers, CURLOPT_CUSTOMREQUEST => strtoupper($method), CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => false, CURLOPT_HEADERFUNCTION => function ($curl, string $line) use (&$responseHeaders): int {
                $parts = explode(':', $line, 2); if (count($parts) === 2) $responseHeaders[trim($parts[0])] = trim($parts[1]); return strlen($line);
            }]);
        if ($data !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        $raw = curl_exec($ch); $status = curl_getinfo($ch, CURLINFO_HTTP_CODE); $curlError = curl_error($ch); curl_close($ch);
        if ($raw === false || $curlError) throw new KambaAPIException('Erro de rede ao comunicar com a KambaSMS.', 0, $curlError ?: null);
        if ($raw === '') $result = null;
        else { try { $result = json_decode($raw, true, 512, JSON_THROW_ON_ERROR); } catch (\JsonException $error) { throw new KambaAPIException('Resposta não JSON da API KambaSMS.', $status, $raw, $responseHeaders); } }
        if ($status >= 400) {
            $message = is_array($result) ? ($result['error'] ?? $result['message'] ?? 'Erro na API KambaSMS.') : 'Erro na API KambaSMS.';
            throw new KambaAPIException((string)$message, $status, $result, $responseHeaders);
        }
        return $result;
    }
}
