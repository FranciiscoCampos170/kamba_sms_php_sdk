<?php
namespace KambaSMS\Resources;

use KambaSMS\KambaSMS;

class AccountResource {
    public function __construct(private KambaSMS $client) {}

    public function getBalance(): array {
        return $this->client->request('GET', '/credits/balance');
    }

    public function getHistory(int $limit = 100): array {
        $endpoint = '/messages?limit=' . $limit;
        return $this->client->request('GET', $endpoint);
    }
}