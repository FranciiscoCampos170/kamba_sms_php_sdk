<?php
require 'vendor/autoload.php';

use KambaSMS\KambaSMS;
use KambaSMS\Exceptions\KambaValidationException;
use KambaSMS\Exceptions\KambaAPIException;

// 1. Inicialização
$client = new KambaSMS(
    apiKey: 'kamba_abcdef123456...', // A tua chave API
    // baseUrl: 'http://localhost:3001' // Descomenta para testar localmente
);

try {
    // 2. Verificar Saldo
    $balance = $client->account->getBalance();
    echo "✅ Saldo atual: " . $balance['balance'] . " SMS\n";

    // 3. Enviar SMS Único
    $sms = $client->sms->send([
        'to' => '+244923456789',
        'text' => 'O seu pedido foi recebido e está em processamento.',
        'sender_id' => 'KAMBA'
    ], ['idempotency_key' => 'order:example:123']);
    echo "✅ SMS Enviado! ID: " . $sms['message_id'] . " | Saldo restante: " . $sms['remaining_balance'] . "\n";

    // 4. Testar Validação do SDK (Isto vai falhar ANTES de chamar a API)
    try {
        $client->sms->send([
            'to' => '923456789', // Falta o +244
            'text' => 'Visite www.kambasms.ao para mais info 🚀', // Tem URL e Emoji
            'sender_id' => 'KAMBA'
        ]);
    } catch (KambaValidationException $e) {
        echo "🛡️ Validação do SDK funcionou: " . $e->getMessage() . "\n";
    }

} catch (KambaAPIException $e) {
    echo "❌ Erro da API: " . $e->getStatusCode() . " - " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Erro inesperado: " . $e->getMessage() . "\n";
}
