<?php
namespace KambaSMS\Resources;
use KambaSMS\KambaSMS; use KambaSMS\Validators; use KambaSMS\Exceptions\KambaValidationException;

class VerifyResource {
    public function __construct(private KambaSMS $client) {}
    public function start(array $params, array $options = []): array {
        Validators::validateAngolanPhone($params['phone'] ?? ''); if (isset($params['sender_id'])) Validators::validateSenderId($params['sender_id']);
        if (isset($params['metadata'])) Validators::requireObject($params['metadata'], 'metadata');
        return $this->client->request('POST', '/verify/start', ['phone'=>$params['phone'],'sender_id'=>$params['sender_id']??null,'metadata'=>$params['metadata']??null], $options);
    }
    public function check(string $verificationId, string $code): array {
        if (!preg_match('/^\d{6}$/', $code)) throw new KambaValidationException('code deve ter 6 dígitos.');
        return $this->client->request('POST', '/verify/check', ['verification_id'=>$verificationId,'code'=>$code]);
    }
    public function resend(string $id, array $options = []): array { return $this->client->request('POST', '/verify/resend', ['verification_id'=>$id], $options); }
    public function list(): array { return $this->client->request('GET', '/verify'); }
    public function get(string $id): array { return $this->client->request('GET', '/verify/'.Validators::id($id)); }
    public function events(?string $id = null): array { return $this->client->request('GET', '/verify/events'.($id ? '?verification_id='.rawurlencode($id) : '')); }
    public function stats(int $days = 30, string $environment = 'all'): array { return $this->client->request('GET', '/verify/stats?'.http_build_query(['days'=>$days,'environment'=>$environment])); }
}
