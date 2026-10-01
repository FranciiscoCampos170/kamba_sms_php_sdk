<?php
namespace KambaSMS\Resources;
use KambaSMS\KambaSMS; use KambaSMS\Exceptions\KambaValidationException;

class LookupResource {
    public function __construct(private KambaSMS $client) {}
    public function lookup(string $phone): array { return $this->client->request('POST', '/lookup', ['phone'=>$phone]); }
    public function bulk(array $phones): array {
        if (count($phones) < 1 || count($phones) > 500) throw new KambaValidationException('phones deve conter 1-500 números.');
        return $this->client->request('POST', '/lookup/bulk', ['phones'=>$phones]);
    }
}
