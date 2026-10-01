<?php
namespace KambaSMS\Resources;
use KambaSMS\KambaSMS; use KambaSMS\Validators; use KambaSMS\Exceptions\KambaValidationException;

class TransactionsResource {
    public function __construct(private KambaSMS $client) {}
    public function overview(): array { return $this->client->request('GET', '/transactions/overview'); }
    public function createTemplate(array $params): array {
        Validators::validateKey($params['key']??''); Validators::validateKey($params['event_type']??'', 'event_type'); Validators::requireText($params['name']??null, 'name'); $this->channels($params['channels']??[]);
        if (in_array('sms',$params['channels'],true)) Validators::requireText($params['sms_body']??null,'sms_body');
        if (in_array('email',$params['channels'],true)) { Validators::requireText($params['email_subject']??null,'email_subject'); Validators::requireText($params['email_html']??null,'email_html'); Validators::requireText($params['domain_id']??null,'domain_id'); Validators::requireText($params['from_local']??null,'from_local'); }
        return $this->client->request('POST', '/transactions/templates', $params);
    }
    public function setTemplateActive(string $id, bool $active): array { return $this->client->request('PATCH', '/transactions/templates/'.Validators::id($id), ['active'=>$active]); }
    public function sendEvent(array $params, array $options = []): array {
        Validators::validateKey($params['event']??'','event'); Validators::validateKey($params['template_key']??'','template_key'); Validators::requireText($params['external_reference']??null,'external_reference');
        $customer=$params['customer']??null; $data=$params['data']??[]; Validators::requireObject($customer,'customer'); Validators::requireObject($data,'data');
        if (isset($params['channels'])) { $this->channels($params['channels']); if (in_array('sms',$params['channels'],true)) Validators::validateAngolanPhone($customer['phone']??''); if (in_array('email',$params['channels'],true)) Validators::validateEmail($customer['email']??'','customer.email'); }
        return $this->client->request('POST', '/transactions/events', ['event'=>$params['event'],'template_key'=>$params['template_key'],'external_reference'=>$params['external_reference'],'customer'=>$customer,'data'=>$data,'channels'=>$params['channels']??null,'environment'=>$params['environment']??null], $options);
    }
    private function channels(array $channels): void { if (!$channels || array_diff($channels,['sms','email'])) throw new KambaValidationException('channels deve conter sms e/ou email.'); }
}
