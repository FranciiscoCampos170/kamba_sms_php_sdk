<?php
namespace KambaSMS\Resources;
use KambaSMS\KambaSMS; use KambaSMS\Validators; use KambaSMS\Exceptions\KambaValidationException;

class EmailResource {
    public function __construct(private KambaSMS $client) {}
    public function overview(): array { return $this->client->request('GET', '/email/overview'); }
    public function createDomain(string $domain): array {
        $domain = strtolower(trim($domain)); if (!preg_match('/^(?=.{4,253}$)(?!-)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/', $domain)) throw new KambaValidationException('domain inválido.');
        return $this->client->request('POST', '/email/domains', ['domain'=>$domain]);
    }
    public function verifyDomain(string $id): array { return $this->client->request('POST', '/email/domains/'.Validators::id($id).'/verify'); }
    public function deleteDomain(string $id): void { $this->client->request('DELETE', '/email/domains/'.Validators::id($id)); }
    public function send(array $params, array $options = []): array {
        Validators::validateEmail($params['to']??'', 'to'); $this->validateLocal($params['from_local']??''); $this->validateContent($params);
        if (isset($params['reply_to'])) Validators::validateEmail($params['reply_to'], 'reply_to');
        return $this->client->request('POST', '/email/send', ['to'=>$params['to'],'domain_id'=>$params['domain_id']??'','from_local'=>$params['from_local'],'from_name'=>$params['from_name']??null,'subject'=>$params['subject'],'html'=>$params['html']??null,'text'=>$params['text']??null,'reply_to'=>$params['reply_to']??null], $options);
    }
    public function createTemplate(array $params): array {
        if (!preg_match('/^[a-z0-9_-]{2,60}$/', $params['key']??'')) throw new KambaValidationException('key inválida.'); Validators::requireText($params['name']??null, 'name'); $this->validateContent($params);
        return $this->client->request('POST', '/email/templates', $params);
    }
    public function updateTemplate(string $id, array $fields): array {
        if (!$fields) throw new KambaValidationException('Indica pelo menos um campo para atualizar.');
        return $this->client->request('PATCH', '/email/templates/'.Validators::id($id), $fields);
    }
    public function deleteTemplate(string $id): void { $this->client->request('DELETE', '/email/templates/'.Validators::id($id)); }
    public function renderTemplate(string $id, array $variables = []): array {
        Validators::requireObject($variables, 'variables'); return $this->client->request('POST', '/email/templates/'.Validators::id($id).'/render', ['variables'=>$variables]);
    }
    public function sendBulk(array $params, array $options = []): array {
        $recipients = $params['recipients']??[]; if (count($recipients)<1 || count($recipients)>500) throw new KambaValidationException('recipients deve conter 1-500 destinatários.');
        foreach ($recipients as $item) Validators::validateEmail($item['email']??''); $this->validateLocal($params['from_local']??'');
        if (empty($params['template_id'])) $this->validateContent($params);
        return $this->client->request('POST', '/email/bulk', ['domain_id'=>$params['domain_id']??'','template_id'=>$params['template_id']??null,'name'=>$params['name']??null,'from_local'=>$params['from_local'],'from_name'=>$params['from_name']??null,'reply_to'=>$params['reply_to']??null,'subject'=>$params['subject']??null,'html'=>$params['html']??null,'text'=>$params['text']??null,'recipients'=>$recipients], $options);
    }
    public function getBulk(string $id): array { return $this->client->request('GET', '/email/bulk/'.Validators::id($id)); }
    public function cancelBulk(string $id): array { return $this->client->request('POST', '/email/bulk/'.Validators::id($id).'/cancel'); }
    public function sandboxSend(array $params = []): array { if (isset($params['to'])) Validators::validateEmail($params['to'], 'to'); return $this->client->request('POST', '/email/sandbox/send', $params); }
    private function validateLocal(string $value): void { if (!preg_match('/^[a-z0-9._+-]{1,64}$/', $value)) throw new KambaValidationException('from_local inválido.'); }
    private function validateContent(array $params): void {
        Validators::requireText($params['subject']??null, 'subject'); if (strlen($params['subject'])>200 || (empty($params['html']) && empty($params['text']))) throw new KambaValidationException('Revê subject e indica html ou text.');
    }
}
