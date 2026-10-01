<?php
namespace KambaSMS;

use KambaSMS\Exceptions\KambaValidationException;

class Validators {
    public static function requireText($value, string $field): void { if (!is_string($value) || trim($value) === '') throw new KambaValidationException("{$field} é obrigatório."); }
    public static function requireObject($value, string $field): void { if (!is_array($value) || ($value !== [] && array_keys($value) === range(0, count($value) - 1))) throw new KambaValidationException("{$field} deve ser um objecto associativo."); }
    public static function validateAngolanPhone(string $phone): void { if (!preg_match('/^\+244[0-9]{9}$/', $phone)) throw new KambaValidationException('Número inválido. Usa +244 seguido de 9 dígitos.'); }
    public static function validateEmail(string $email, string $field = 'email'): void { if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new KambaValidationException("{$field} inválido."); }
    public static function validateKey(string $value, string $field = 'key', int $max = 80): void { if (strlen($value) > $max || !preg_match('/^[a-z][a-z0-9_.-]{2,79}$/', $value)) throw new KambaValidationException("{$field} inválido."); }
    public static function validateSenderId(string $value): void { if (!preg_match('/^[A-Za-z0-9 ]{3,11}$/', $value)) throw new KambaValidationException('sender_id deve conter 3-11 letras, números ou espaços.'); }
    public static function validateMessageContent(string $text): void {
        self::requireText($text, 'text');
        if (preg_match('/https?:\/\/|www\.|\.com\b|\.ao\b|\.net\b|\.org\b|\.co\b|\.io\b/i', $text)) throw new KambaValidationException('Mensagens com links não são permitidas.');
        $length = function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);
        if ($length > 160) throw new KambaValidationException('Mensagem demasiado longa. O limite é 160 caracteres.');
        if (preg_match('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{2600}-\x{27BF}]/u', $text)) throw new KambaValidationException('Emojis não são suportados.');
    }
    public static function id(string $value): string { self::requireText($value, 'id'); return rawurlencode($value); }
}
