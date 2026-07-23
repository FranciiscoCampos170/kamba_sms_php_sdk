<?php
namespace KambaSMS\Resources;

use KambaSMS\KambaSMS;
use KambaSMS\Validators;

class SmsResource {
    public function __construct(private KambaSMS $client) {}

    public function send(array $params): array {
        Validators::validateAngolanPhone($params['to']);
        Validators::validateMessageContent($params['text']);

        return $this->client->request('POST', '/messages/send', [
            'to' => $params['to'],
            'text' => $params['text'],
            'sender_id' => $params['sender_id'] ?? null,
        ]);
    }

    public function sendBulk(array $params): array {
        Validators::validateMessageContent($params['text']);
        
        foreach ($params['recipients'] as $phone) {
            Validators::validateAngolanPhone($phone);
        }

        if (count($params['recipients']) > 1000) {
            throw new \InvalidArgumentException('O limite máximo é de 1000 destinatários por envio em massa.');
        }

        return $this->client->request('POST', '/messages/bulk', [
            'name' => $params['name'],
            'sender_id' => $params['sender_id'],
            'text' => $params['text'],
            'recipients' => $params['recipients'],
        ]);
    }

    public function schedule(array $params): array {
        Validators::validateAngolanPhone($params['to']);
        Validators::validateMessageContent($params['text']);

        $scheduledAt = $params['scheduled_at'] instanceof \DateTimeInterface 
            ? $params['scheduled_at']->format('c') 
            : $params['scheduled_at'];

        return $this->client->request('POST', '/messages/schedule', [
            'to' => $params['to'],
            'text' => $params['text'],
            'sender_id' => $params['sender_id'],
            'scheduled_at' => $scheduledAt,
        ]);
    }
}