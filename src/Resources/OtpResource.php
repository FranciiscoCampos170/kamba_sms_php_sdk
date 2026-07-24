<?php
namespace KambaSMS\Resources;

use KambaSMS\KambaSMS;

class OtpResource {
    public function __construct(private KambaSMS $client) {}

    public function send(array $params): array {
        return $this->client->request('POST', '/otp/send', [
            'phone' => $params['phone'],
        ]);
    }

    public function verify(array $params): array {
        return $this->client->request('POST', '/otp/verify', [
            'phone' => $params['phone'],
            'code'  => $params['code'],
        ]);
    }
}