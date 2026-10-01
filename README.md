# KambaSMS PHP SDK

SDK oficial e sem dependências externas para SMS, OTP, Verify, Lookup, Notify, Email e Transactions.

Requer PHP 8.0+, `ext-curl` e `ext-json`.

## Instalação

```bash
composer require kambasms/php-sdk
```

## Configuração

```php
use KambaSMS\KambaSMS;

$client = new KambaSMS(apiKey: $_ENV['KAMBA_API_KEY']);
```

A URL padrão é `https://api.kambasms.ao`. O construtor também aceita `baseUrl` e `timeout`.

## SMS e OTP

```php
$sms = $client->sms->send([
    'to' => '+244923456789',
    'text' => 'O seu pedido foi recebido.',
], ['idempotency_key' => 'order:123:sms']);

$otp = $client->otp->send(['phone' => '+244923456789']);
$result = $client->otp->verify(['phone' => '+244923456789', 'code' => '123456']);
```

O recurso SMS também oferece `sendBulk`, `schedule`, `listScheduled`, `cancelScheduled`, `listBulk` e `getBulk`.

## Verify

Verify gere código, expiração, tentativas, reenvio e estado de cada sessão.

```php
$session = $client->verify->start([
    'phone' => '+244923456789',
    'metadata' => ['customer_id' => '123'],
], ['idempotency_key' => 'verify:customer:123']);

$result = $client->verify->check($session['verification_id'], '123456');
```

Também podes usar `list`, `get`, `events`, `stats` e `resend`.

## Lookup

```php
$number = $client->lookup->lookup('923 456 789');
$batch = $client->lookup->bulk(['923456789', '+244933123456']);
```

A operadora é estimada pelo prefixo e não confirmada em tempo real.

## Notify

A chave API precisa do scope `notify`.

```php
$client->notify->createTemplate([
    'key' => 'appointment_reminder',
    'name' => 'Lembrete',
    'body' => 'Olá {{name}}, a sua consulta está marcada para {{date}}.',
]);

$client->notify->send([
    'to' => '+244923456789',
    'template_key' => 'appointment_reminder',
    'variables' => ['name' => 'Ana', 'date' => '10/10 às 09:00'],
], ['idempotency_key' => 'appointment:123:reminder']);
```

## Kamba Email

Email usa volume próprio, separado dos créditos SMS. Em produção, adiciona e verifica um domínio. A chave API precisa do scope `email`.

```php
$domain = $client->email->createDomain('example.ao');
$client->email->verifyDomain($domain['id']);

$client->email->send([
    'to' => 'cliente@example.com',
    'domain_id' => $domain['id'],
    'from_local' => 'alertas',
    'from_name' => 'Minha Empresa',
    'subject' => 'Pedido recebido',
    'html' => '<p>O pedido <strong>#123</strong> foi recebido.</p>',
], ['idempotency_key' => 'order:123:email']);
```

O recurso inclui domínios, templates, renderização, Sandbox e bulk de até 500 destinatários por pedido.

## Kamba Transactions

Transactions recebe eventos e entrega comunicações por SMS e/ou email. Não processa pagamentos, cartões ou dinheiro. A chave API precisa do scope `transactions`.

```php
$client->transactions->createTemplate([
    'key' => 'meeting_notice',
    'event_type' => 'meeting.scheduled',
    'name' => 'Reunião agendada',
    'channels' => ['sms'],
    'sms_body' => 'A sua reunião foi agendada para {{date}}.',
]);

$event = $client->transactions->sendEvent([
    'event' => 'meeting.scheduled',
    'template_key' => 'meeting_notice',
    'external_reference' => 'meeting-123',
    'customer' => ['phone' => '+244923456789'],
    'data' => ['date' => '10/10 às 09:00'],
    'channels' => ['sms'],
], ['idempotency_key' => 'meeting:123']);
```

## Conta e Sandbox

```php
$balance = $client->account->getBalance();
$history = $client->account->getHistory(20);
$stats = $client->account->getStats();
$usage = $client->account->getUsage();
```

Usa uma chave Sandbox para simular operações sem consumir créditos reais.

## Tratamento de erros

```php
use KambaSMS\Exceptions\KambaAPIException;
use KambaSMS\Exceptions\KambaValidationException;

try {
    $client->sms->send(['to' => '923456789', 'text' => 'Mensagem']);
} catch (KambaValidationException $error) {
    echo $error->getMessage();
} catch (KambaAPIException $error) {
    echo $error->getStatusCode();
    echo $error->getAPIErrorCode();
    print_r($error->getDetails());
}
```

O estado `0` indica falha de rede ou timeout. O SDK não repete pedidos automaticamente. Reutiliza a mesma chave de idempotência quando precisares de confirmar uma operação incerta.

Documentação: [kambasms.ao/dashboard/docs](https://www.kambasms.ao/dashboard/docs)
