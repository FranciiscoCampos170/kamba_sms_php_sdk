<?php
namespace KambaSMS\Resources;
use KambaSMS\KambaSMS; use KambaSMS\Validators; use KambaSMS\Exceptions\KambaValidationException;

class NotifyResource {
    public function __construct(private KambaSMS $client) {}
    public function createTemplate(array $params): array {
        Validators::validateKey($params['key']??'', 'key', 50); Validators::requireText($params['name']??null, 'name'); Validators::requireText($params['body']??null, 'body');
        if (strlen($params['body']) > 1000) throw new KambaValidationException('body aceita até 1000 caracteres.');
        return $this->client->request('POST', '/notify/templates', $params);
    }
    public function listTemplates(): array { return $this->client->request('GET', '/notify/templates'); }
    public function disableTemplate(string $id): array { return $this->client->request('DELETE', '/notify/templates/'.Validators::id($id)); }
    public function render(string $templateKey, array $variables = []): array {
        Validators::validateKey($templateKey, 'template_key', 50); Validators::requireObject($variables, 'variables');
        return $this->client->request('POST', '/notify/render', ['template_key'=>$templateKey,'variables'=>$variables]);
    }
    public function send(array $params, array $options = []): array {
        Validators::validateAngolanPhone($params['to']??''); Validators::validateKey($params['template_key']??'', 'template_key', 50);
        if (isset($params['sender_id'])) Validators::validateSenderId($params['sender_id']); $variables = $params['variables']??[]; Validators::requireObject($variables, 'variables');
        return $this->client->request('POST', '/notify/send', ['to'=>$params['to'],'template_key'=>$params['template_key'],'variables'=>$variables,'sender_id'=>$params['sender_id']??null], $options);
    }
    public function listDeliveries(): array { return $this->client->request('GET', '/notify/deliveries'); }
}
