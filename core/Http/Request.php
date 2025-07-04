<?php
/**
 * @package     phast/core
 * @subpackage  Http
 * @file        Request
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description HTTP request handler
 */

declare(strict_types=1);

namespace Phast\Core\Http;

class Request {
   private array $server;
   private array $query;
   private array $post;
   private array $files;
   private array $headers;
   private string $body;
   private array $routeParams = [];

   public function __construct(
      array $server = null,
      array $query = null,
      array $post = null,
      array $files = null
   ) {
      $this->server = $server ?? $_SERVER;
      $this->query = $query ?? $_GET;
      $this->post = $post ?? $_POST;
      $this->files = $files ?? $_FILES;
      $this->headers = $this->parseHeaders();
      $this->body = $this->getBody();
   }

   public function getMethod(): string {
      return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
   }

   public function getUri(): string {
      return $this->server['REQUEST_URI'] ?? '/';
   }

   public function getPath(): string {
      $uri = $this->getUri();
      $path = parse_url($uri, PHP_URL_PATH);
      return $path ?: '/';
   }

   public function getQuery(string $key = null, mixed $default = null): mixed {
      if ($key === null) {
         return $this->query;
      }

      return $this->query[$key] ?? $default;
   }

   public function getPost(string $key = null, mixed $default = null): mixed {
      if ($key === null) {
         return $this->post;
      }

      return $this->post[$key] ?? $default;
   }

   public function getInput(string $key = null, mixed $default = null): mixed {
      $input = array_merge($this->query, $this->post);

      if ($key === null) {
         return $input;
      }

      return $input[$key] ?? $default;
   }

   public function getHeader(string $name, mixed $default = null): mixed {
      $name = strtolower($name);
      return $this->headers[$name] ?? $default;
   }

   public function getHeaders(): array {
      return $this->headers;
   }

   public function getBody(): string {
      if (!isset($this->body)) {
         $this->body = file_get_contents('php://input') ?: '';
      }

      return $this->body;
   }

   public function getFiles(): array {
      return $this->files;
   }

   public function getFile(string $key): ?array {
      return $this->files[$key] ?? null;
   }

   public function isMethod(string $method): bool {
      return $this->getMethod() === strtoupper($method);
   }

   public function isPost(): bool {
      return $this->isMethod('POST');
   }

   public function isGet(): bool {
      return $this->isMethod('GET');
   }

   public function isPut(): bool {
      return $this->isMethod('PUT');
   }

   public function isDelete(): bool {
      return $this->isMethod('DELETE');
   }

   public function isAjax(): bool {
      return $this->getHeader('x-requested-with') === 'XMLHttpRequest';
   }

   public function isJson(): bool {
      $contentType = $this->getHeader('content-type', '');
      return str_contains($contentType, 'application/json');
   }

   public function getJson(): ?array {
      if (!$this->isJson()) {
         return null;
      }

      $data = json_decode($this->getBody(), true);
      return json_last_error() === JSON_ERROR_NONE ? $data : null;
   }

   public function getIp(): ?string {
      $headers = [
         'HTTP_X_FORWARDED_FOR',
         'HTTP_X_REAL_IP',
         'HTTP_CLIENT_IP',
         'REMOTE_ADDR'
      ];

      foreach ($headers as $header) {
         if (!empty($this->server[$header])) {
            $ip = trim(explode(',', $this->server[$header])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
               return $ip;
            }
         }
      }

      return null;
   }

   private function parseHeaders(): array {
      $headers = [];

      foreach ($this->server as $key => $value) {
         if (str_starts_with($key, 'HTTP_')) {
            $name = strtolower(str_replace('_', '-', substr($key, 5)));
            $headers[$name] = $value;
         } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
            $name = strtolower(str_replace('_', '-', $key));
            $headers[$name] = $value;
         }
      }

      return $headers;
   }

   public function setRouteParams(array $params): void {
      $this->routeParams = $params;
   }

   public function getRouteParam(string $key, mixed $default = null): mixed {
      return $this->routeParams[$key] ?? $default;
   }

   public function getRouteParams(): array {
      return $this->routeParams;
   }

   public function __get(string $name): mixed {
      return $this->getRouteParam($name);
   }
}
