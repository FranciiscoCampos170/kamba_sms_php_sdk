<?php
namespace KambaSMS\Resources;
use KambaSMS\KambaSMS; use KambaSMS\Validators; use KambaSMS\Exceptions\KambaValidationException;

class OtpResource {
    public function __construct(private KambaSMS $client) {}
    public function send(array $params, array $options = []): array {
        Validators::validateAngolanPhone($params['phone'] ?? ''); if (isset($params['sender_id'])) Validators::validateSenderId($params['sender_id']);
        return $this->client->request('POST', '/otp/send', ['phone'=>$params['phone'],'sender_id'=>$params['sender_id']??null], $options);
    }
    public function verify(array $params): array {
        Validators::validateAngolanPhone($params['phone'] ?? ''); if (!preg_match('/^\d{6}$/', $params['code'] ?? '')) throw new KambaValidationException('code deve ter 6 dígitos.');
        return $this->client->request('POST', '/otp/verify', ['phone'=>$params['phone'],'code'=>$params['code']]);
    }
}
