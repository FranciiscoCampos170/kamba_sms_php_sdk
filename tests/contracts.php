<?php
require_once __DIR__.'/../src/Exceptions/KambaException.php';
require_once __DIR__.'/../src/Exceptions/KambaValidationException.php';
require_once __DIR__.'/../src/Exceptions/KambaAPIException.php';
require_once __DIR__.'/../src/Validators.php';
foreach (glob(__DIR__.'/../src/Resources/*.php') as $file) require_once $file;
require_once __DIR__.'/../src/KambaSMS.php';

use KambaSMS\KambaSMS;
use KambaSMS\Exceptions\KambaValidationException;

class FakeKambaSMS extends KambaSMS {
    public array $calls = [];
    public function request(string $method, string $endpoint, ?array $data = null, array $options = []) {
        $this->calls[] = compact('method','endpoint','data','options'); return ['success'=>true];
    }
}

function check(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }

$client = new FakeKambaSMS('test-key', 'http://localhost:3001');
$client->sms->send(['to'=>'+244923456789','text'=>'Olá'], ['idempotency_key'=>'order:123']);
$client->verify->start(['phone'=>'+244923456789','metadata'=>['id'=>1]]);
$client->lookup->bulk(['923456789']);
$client->notify->send(['to'=>'+244923456789','template_key'=>'payment_due','variables'=>['name'=>'Ana']], ['idempotency_key'=>'notify:123']);
$client->email->send(['to'=>'ana@example.com','domain_id'=>'domain-1','from_local'=>'alertas','subject'=>'Assunto','text'=>'Conteúdo'], ['idempotency_key'=>'email:123']);
$client->transactions->createTemplate(['key'=>'meeting_notice','event_type'=>'meeting.scheduled','name'=>'Reunião','channels'=>['sms'],'sms_body'=>'Reunião {{date}}']);
$client->transactions->sendEvent(['event'=>'meeting.scheduled','template_key'=>'meeting_notice','external_reference'=>'meeting-1','customer'=>['phone'=>'+244923456789'],'data'=>['date'=>'10/10'],'channels'=>['sms']], ['idempotency_key'=>'transaction:123']);

check(array_column($client->calls,'endpoint') === ['/messages/send','/verify/start','/lookup/bulk','/notify/send','/email/send','/transactions/templates','/transactions/events'], 'Endpoints incorretos.');
check($client->calls[4]['data']['from_local'] === 'alertas', 'Mapeamento Email incorreto.');
check($client->calls[6]['data']['external_reference'] === 'meeting-1', 'Mapeamento Transactions incorreto.');
$before = count($client->calls);
try { $client->sms->send(['to'=>'923456789','text'=>'Olá']); throw new RuntimeException('Validação não executada.'); } catch (KambaValidationException $expected) {}
check(count($client->calls) === $before, 'Entrada inválida chegou à API.');
echo "SDK PHP contracts: OK\n";
