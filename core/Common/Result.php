<?php

declare(strict_types=1);

namespace Phast\Core\Common;

/**
 * Result pattern para encapsular éxito/fallo de operaciones
 */
final class Result {
   private function __construct(
      private readonly bool $success,
      private readonly mixed $data = null,
      private readonly ?string $error = null,
      private readonly array $errors = []
   ) {
   }

   public static function success(mixed $data = null): self {
      return new self(true, $data);
   }

   public static function failure(string $error, array $errors = []): self {
      return new self(false, null, $error, $errors);
   }

   public function isSuccess(): bool {
      return $this->success;
   }

   public function isFailure(): bool {
      return !$this->success;
   }

   public function getData(): mixed {
      return $this->data;
   }

   public function getError(): ?string {
      return $this->error;
   }

   public function getErrors(): array {
      return $this->errors;
   }

   public function match(callable $onSuccess, callable $onFailure): mixed {
      return $this->success
         ? $onSuccess($this->data)
         : $onFailure($this->error, $this->errors);
   }
}
