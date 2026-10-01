<?php
namespace KambaSMS\Resources;
use KambaSMS\KambaSMS; use KambaSMS\Exceptions\KambaValidationException;

class AccountResource {
    public function __construct(private KambaSMS $client) {}
    public function getBalance(): array { return $this->client->request('GET', '/credits/balance'); }
    public function getHistory(int $limit = 100): array {
        if ($limit < 1 || $limit > 100) throw new KambaValidationException('limit deve estar entre 1 e 100.');
        return array_slice($this->client->request('GET', '/messages'), 0, $limit);
    }
    public function getStats(): array { return $this->client->request('GET', '/messages/stats'); }
    public function getUsage(): array { return $this->client->request('GET', '/usage/overview'); }
}
