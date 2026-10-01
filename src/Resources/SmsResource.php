<?php
namespace KambaSMS\Resources;
use KambaSMS\KambaSMS; use KambaSMS\Validators; use KambaSMS\Exceptions\KambaValidationException;

class SmsResource {
    public function __construct(private KambaSMS $client) {}
    public function send(array $params, array $options = []): array {
        Validators::validateAngolanPhone($params['to'] ?? ''); Validators::validateMessageContent($params['text'] ?? '');
        if (isset($params['sender_id'])) Validators::validateSenderId($params['sender_id']);
        return $this->client->request('POST', '/messages/send', ['to'=>$params['to'],'text'=>$params['text'],'sender_id'=>$params['sender_id']??null], $options);
    }
    public function sendBulk(array $params, array $options = []): array {
        Validators::validateMessageContent($params['text'] ?? ''); Validators::validateSenderId($params['sender_id'] ?? '');
        if (empty($params['recipients']) || !is_array($params['recipients'])) throw new KambaValidationException('Indica pelo menos um destinatário.');
        foreach ($params['recipients'] as $phone) Validators::validateAngolanPhone($phone);
        return $this->client->request('POST', '/messages/bulk', ['name'=>$params['name']??'Envio em massa','sender_id'=>$params['sender_id'],'text'=>$params['text'],'recipients'=>$params['recipients']], $options);
    }
    public function schedule(array $params, array $options = []): array {
        Validators::validateAngolanPhone($params['to'] ?? ''); Validators::validateMessageContent($params['text'] ?? ''); Validators::validateSenderId($params['sender_id'] ?? '');
        $date = $params['scheduled_at'] ?? null; $value = $date instanceof \DateTimeInterface ? $date->format(DATE_ATOM) : $date;
        if (!is_string($value) || strtotime($value) === false || strtotime($value) <= time()) throw new KambaValidationException('scheduled_at deve ser uma data futura.');
        return $this->client->request('POST', '/messages/schedule', ['to'=>$params['to'],'text'=>$params['text'],'sender_id'=>$params['sender_id'],'scheduled_at'=>$value], $options);
    }
    public function listScheduled(): array { return $this->client->request('GET', '/messages/scheduled'); }
    public function cancelScheduled(string $id): array { return $this->client->request('DELETE', '/messages/scheduled/'.Validators::id($id)); }
    public function listBulk(): array { return $this->client->request('GET', '/messages/bulk'); }
    public function getBulk(string $id): array { return $this->client->request('GET', '/messages/bulk/'.Validators::id($id)); }
}
