<?php
namespace KambaSMS\Exceptions;

class KambaAPIException extends KambaException {
    public function __construct(string $message, private int $statusCode, private $details = null, private array $headers = []) { parent::__construct($message); }
    public function getStatusCode(): int { return $this->statusCode; }
    public function getDetails() { return $this->details; }
    public function getHeaders(): array { return $this->headers; }
    public function getAPIErrorCode(): ?string { return is_array($this->details) ? ($this->details['code'] ?? null) : null; }
    public function getRequestId(): ?string { return $this->headers['X-Request-Id'] ?? $this->headers['x-request-id'] ?? null; }
    public function getRetryAfter(): ?string { return $this->headers['Retry-After'] ?? $this->headers['retry-after'] ?? null; }
}
