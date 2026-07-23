<?php
namespace KambaSMS\Exceptions;

class KambaAPIException extends KambaException {
    private int $statusCode;
    private ?array $details;

    public function __construct(string $message, int $statusCode, ?array $details = null) {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->details = $details;
    }

    public function getStatusCode(): int { return $this->statusCode; }
    public function getDetails(): ?array { return $this->details; }
}