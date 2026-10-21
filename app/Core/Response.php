<?php

namespace App\Core;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];

    public function __construct()
    {
        // Apply default security headers
        $this->setHeader('X-Frame-Options', 'SAMEORIGIN');
        $this->setHeader('X-Content-Type-Options', 'nosniff');
        $this->setHeader('X-XSS-Protection', '1; mode=block');
        $this->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function status(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function sendHeaders(): void
    {
        if (!headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $name => $value) {
                header("$name: $value");
            }
        }
    }

    public function json(array $data, int $status = 200): void
    {
        $this->status($status);
        $this->setHeader('Content-Type', 'application/json; charset=utf-8');
        $this->sendHeaders();
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function html(string $content, int $status = 200): void
    {
        $this->status($status);
        $this->setHeader('Content-Type', 'text/html; charset=utf-8');
        $this->sendHeaders();
        echo $content;
        exit;
    }

    public function redirect(string $url, int $status = 302): void
    {
        $this->status($status);
        $this->setHeader('Location', $url);
        $this->sendHeaders();
        exit;
    }
}
