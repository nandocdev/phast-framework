<?php
/**
 * @package     phast/core
 * @subpackage  Http
 * @file        Response
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description HTTP response handler
 */

declare(strict_types=1);

namespace Phast\Core\Http;

class Response {
   private mixed $content;
   private int $statusCode;
   private array $headers;
   private static array $statusTexts = [
      200 => 'OK',
      201 => 'Created',
      204 => 'No Content',
      301 => 'Moved Permanently',
      302 => 'Found',
      304 => 'Not Modified',
      400 => 'Bad Request',
      401 => 'Unauthorized',
      403 => 'Forbidden',
      404 => 'Not Found',
      405 => 'Method Not Allowed',
      422 => 'Unprocessable Entity',
      500 => 'Internal Server Error',
      503 => 'Service Unavailable',
   ];

   public function __construct(
      mixed $content = '',
      int $statusCode = 200,
      array $headers = []
   ) {
      $this->content = $content;
      $this->statusCode = $statusCode;
      $this->headers = $headers;
   }

   public function getContent(): mixed {
      return $this->content;
   }

   public function setContent(mixed $content): self {
      $this->content = $content;
      return $this;
   }

   public function getStatusCode(): int {
      return $this->statusCode;
   }

   public function setStatusCode(int $statusCode): self {
      $this->statusCode = $statusCode;
      return $this;
   }

   public function getHeaders(): array {
      return $this->headers;
   }

   public function setHeader(string $name, string $value): self {
      $this->headers[$name] = $value;
      return $this;
   }

   public function setHeaders(array $headers): self {
      $this->headers = array_merge($this->headers, $headers);
      return $this;
   }

   public function json(mixed $data, int $statusCode = 200): self {
      $this->setContent(json_encode($data, JSON_THROW_ON_ERROR))
         ->setStatusCode($statusCode)
         ->setHeader('Content-Type', 'application/json');

      return $this;
   }

   public function html(string $html, int $statusCode = 200): self {
      $this->setContent($html)
         ->setStatusCode($statusCode)
         ->setHeader('Content-Type', 'text/html; charset=utf-8');

      return $this;
   }

   public function redirect(string $url, int $statusCode = 302): self {
      $this->setStatusCode($statusCode)
         ->setHeader('Location', $url);

      return $this;
   }

   public function download(string $filePath, string $filename = null): self {
      if (!file_exists($filePath)) {
         throw new \InvalidArgumentException("File not found: {$filePath}");
      }

      $filename = $filename ?: basename($filePath);
      $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

      $this->setContent(file_get_contents($filePath))
         ->setHeader('Content-Type', $mimeType)
         ->setHeader('Content-Disposition', "attachment; filename=\"{$filename}\"")
         ->setHeader('Content-Length', (string) filesize($filePath));

      return $this;
   }

   public function send(): void {
      $this->sendHeaders();
      $this->sendContent();
   }

   protected function sendHeaders(): void {
      if (headers_sent()) {
         return;
      }

      // Send status line
      $statusText = self::$statusTexts[$this->statusCode] ?? 'Unknown';
      header("HTTP/1.1 {$this->statusCode} {$statusText}");

      // Send headers
      foreach ($this->headers as $name => $value) {
         header("{$name}: {$value}");
      }
   }

   protected function sendContent(): void {
      echo $this->content;
   }

   public static function make(mixed $content = '', int $statusCode = 200, array $headers = []): self {
      return new self($content, $statusCode, $headers);
   }

   public static function jsonResponse(mixed $data, int $statusCode = 200, array $headers = []): self {
      return (new self('', $statusCode, $headers))->json($data, $statusCode);
   }

   public static function htmlResponse(string $html, int $statusCode = 200, array $headers = []): self {
      return (new self('', $statusCode, $headers))->html($html, $statusCode);
   }

   public static function redirectResponse(string $url, int $statusCode = 302): self {
      return (new self())->redirect($url, $statusCode);
   }

   public function __toString(): string {
      return (string) $this->content;
   }
}
