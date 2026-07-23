# 🇦🇴 KambaSMS PHP SDK

[![Packagist Version](https://img.shields.io/packagist/v/kambasms/php-sdk.svg)](https://packagist.org/packages/kambasms/php-sdk)
[![PHP Version](https://img.shields.io/packagist/php-v/kambasms/php-sdk.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

SDK oficial e leve da **KambaSMS** para integração de envio de mensagens SMS em Angola. Desenvolvido com tipagem forte (PHP 8.0+) e **zero dependências externas** (utiliza cURL nativo), garantindo máxima compatibilidade e performance.

## ✨ Funcionalidades

- 🚀 **Zero Dependências**: Não obriga a instalar pacotes pesados como Guzzle. Usa o cURL nativo do PHP.
- 🛡️ **Validação no Cliente**: Deteta números inválidos, URLs ou emojis *antes* de fazer a chamada à API, poupando tempo e créditos.
- 💎 **Totalmente Tipado**: Suporte nativo a PHP 8.0+ com retorno de arrays estruturados.
- 📦 **MVP Completo**: Envio único, envio em massa, agendamento e gestão de saldo/histórico.

## 📦 Instalação

Instala o pacote via Composer:

```bash
composer require kambasms/php-sdk

```

**Nota:** Requer PHP **8.0** ou superior e a extensão `curl` ativada (ativada por padrão na maioria dos servidores).

## ⚡ Início Rápido

### 1. Inicialização

Obtém a tua chave API no [Dashboard da KambaSMS](https://kambasms.ao/dashboard/keys) e inicializa o cliente:

```php
<?php
require 'vendor/autoload.php';

use KambaSMS\KambaSMS;
use KambaSMS\Exceptions\KambaAPIException;
use KambaSMS\Exceptions\KambaValidationException;

$client = new KambaSMS(
	apiKey: 'kamba_sua_chave_aqui', // A tua chave (ex: kamba_xxxxx...)
	// baseUrl: 'http://localhost:3001' // Opcional: para testes locais
);
```

### 2. Enviar um SMS Único

```php
try {
	$response = $client->sms->send([
		'to' => '+244923456789',
		'text' => 'O seu código de verificação é 1234. Não partilhe com ninguém.',
		'sender_id' => 'KAMBA' // Opcional: se omitido, usa o Sender ID da tua API Key
	]);

	echo "✅ SMS Enviado com sucesso!\n";
	echo "ID da Mensagem: " . $response['message_id'] . "\n";
	echo "Saldo Restante: " . $response['remaining_balance'] . "\n";
} catch (KambaValidationException $e) {
	echo "Dados inválidos: " . $e->getMessage();
} catch (KambaAPIException $e) {
	echo "Erro da API (" . $e->getStatusCode() . "): " . $e->getMessage();
}
```

### 3. Envio em Massa (Bulk)

Ideal para campanhas de marketing ou notificações para múltiplos contactos (máximo de 1000 destinatários por pedido).

```php
try {
	$response = $client->sms->sendBulk([
		'name' => 'Campanha Natal 2024',
		'sender_id' => 'PROMO',
		'text' => 'Feliz Natal! Aproveite 20% de desconto na sua próxima compra.',
		'recipients' => [
			'+244923456789',
			'+244933123456',
			'+244943987654'
		]
	]);

	echo "✅ Job de envio em massa criado!\n";
	echo "ID do Job: " . $response['job_id'] . "\n";
	echo "Total de destinatários: " . $response['total'] . "\n";

} catch (Exception $e) {
	echo "Falha no envio em massa: " . $e->getMessage() . "\n";
}

```

### 4. Agendar um SMS

Ideal para campanhas de marketing ou notificações para múltiplos contactos (máximo de 1000 destinatários por pedido).

```php
try {
    // Agendar para daqui a 2 horas
    $dataFutura = new DateTime('+2 hours');

    $response = $client->sms->schedule([
        'to' => '+244923456789',
        'text' => 'Lembrete: A sua consulta está marcada para amanhã.',
        'sender_id' => 'CLINICA',
        'scheduled_at' => $dataFutura
    ]);

    echo "✅ SMS agendado com sucesso!\n";
    echo "ID da Mensagem: " . $response['message_id'] . "\n";

} catch (Exception $e) {
    echo "Falha ao agendar o SMS: " . $e->getMessage() . "\n";
}


```

### 5. Consultar Saldo e Histórico

Ideal para campanhas de marketing ou notificações para múltiplos contactos (máximo de 1000 destinatários por pedido).

```php
<?php
// Verificar saldo
$balance = $client->account->getBalance();

echo "Saldo atual: " . $balance['balance'] . " SMS\n";

// Ver histórico de envios
// Retorna os últimos 100 registos por padrão
$history = $client->account->getHistory(limit: 10);

print_r($history);
```
## 🛡️ Regras de Validação (Específicas para Angola)

O SDK faz validações automáticas no lado do cliente para garantir que a tua mensagem não seja bloqueada pelas operadoras (Unitel, Africell, Movicel). Se estas regras forem violadas, o SDK lança uma `KambaValidationException` **sem sequer fazer a chamada à API**.

1. **Formato do Número**: Deve começar obrigatoriamente com `+244` seguido de exatamente 9 dígitos (ex: `+244923456789`).

2. **Sem URLs**: Mensagens contendo `http://`, `https://`, `www.` ou domínios como `.com`, `.ao` são rejeitadas (filtradas como spam pelas operadoras).

3. **Sem Emojis**: Caracteres emoji não são suportados e podem causar cobrança de múltiplos segmentos ou bloqueio.

4. **Limite de Caracteres**: Máximo de 160 caracteres por SMS.


## ⚠️ Tratamento de Erros Robusto

O SDK exporta classes de exceção específicas para que possas tratar falhas de forma elegante e segura no teu código:

```php
use KambaSMS\KambaSMS;
use KambaSMS\Exceptions\KambaValidationException;
use KambaSMS\Exceptions\KambaAPIException;

$client = new KambaSMS('kamba_...');

try {
    $client->sms->send([
        'to' => '923456789', // Erro: Falta o +244
        'text' => 'Acesse www.kambasms.ao 🚀', // Erro: Tem URL e Emoji
        'sender_id' => 'KAMBA'
    ]);
} catch (KambaValidationException $e) {
    // Erro de validação do SDK (o desenvolvedor precisa corrigir os dados)
    echo "🚫 Dados inválidos: " . $e->getMessage() . "\n";

} catch (KambaAPIException $e) {
    // Erro retornado pelo servidor da KambaSMS (ex: saldo insuficiente, rate limit)
    echo "🔌 Erro da API (" . $e->getStatusCode() . "): " . $e->getMessage() . "\n";
    // echo "Detalhes: " . print_r($e->getDetails(), true) . "\n";

} catch (Exception $e) {
    // Erro de rede (cURL) ou inesperado do PHP
    echo "💥 Erro inesperado: " . $e->getMessage() . "\n";
}
```
### 💡 Dica para Laravel

Se estiveres a usar Laravel, podes registar o cliente no teu `AppServiceProvider` para injeção de dependência:

```php
// app/Providers/AppServiceProvider.php
public function register()
{
    $this->app->singleton(\KambaSMS\KambaSMS::class, function () {
        return new \KambaSMS\KambaSMS(
            config('services.kambasms.api_key')
        );
    });
}
```
E depois usar em qualquer Controller: public function __construct(private \KambaSMS\KambaSMS $sms) {}


## 📚 Documentação Completa

Para mais detalhes sobre endpoints avançados, webhooks de entrega e gestão de conta, consulta a [Documentação Oficial da KambaSMS](https://www.kambasms.ao/dashboard/docs).

## 🆘 Suporte

Encontraste um bug ou tens uma sugestão?

- Abre uma [Issue neste repositório](https://github.com/FranciiscoCampos170/kamba_sms_php_sdk/issues).
- Contacta a nossa equipa: [support@kambasms.ao](mailto:support@kambasms.ao).

## 📄 Licença

Este projeto está licenciado sob a [Licença MIT](LICENSE).
