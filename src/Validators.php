<?php
namespace KambaSMS;

use KambaSMS\Exceptions\KambaValidationException;

class Validators {
    public static function validateAngolanPhone(string $phone): void {
        if (!preg_match('/^\+244[0-9]{9}$/', $phone)) {
            throw new KambaValidationException(
                "Número de telefone inválido: '{$phone}'. Deve ser +244 seguido de 9 dígitos (ex: +244923456789)."
            );
        }
    }

    public static function validateMessageContent(string $text): void {
        if (preg_match('/https?:\/\/|www\.|\.com\b|\.ao\b|\.net\b|\.org\b|\.co\b|\.io\b/i', $text)) {
            throw new KambaValidationException(
                'Mensagens com links ou URLs não são permitidas. As operadoras angolanas filtram este conteúdo como spam.'
            );
        }

        if (mb_strlen($text) > 160) {
            throw new KambaValidationException(
                "Mensagem demasiado longa (" . mb_strlen($text) . "/160 caracteres). O limite é de 160 caracteres por SMS."
            );
        }

        // Regex para emojis
        if (preg_match('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $text)) {
            throw new KambaValidationException(
                'Emojis não são suportados. As operadoras angolanas podem bloquear ou cobrar múltiplos SMS por mensagens com emojis.'
            );
        }
    }
}