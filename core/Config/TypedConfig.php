<?php

declare(strict_types=1);

namespace Phast\Core\Config;

/**
 * Configuration manager con tipado fuerte y validación
 */
class TypedConfig
{
    public function __construct(
        private readonly array $config = []
    ) {}

    /**
     * Obtiene un valor con tipo específico
     */
    public function getString(string $key, string $default = ''): string
    {
        $value = $this->get($key, $default);
        return is_string($value) ? $value : (string) $value;
    }

    public function getInt(string $key, int $default = 0): int
    {
        $value = $this->get($key, $default);
        return is_int($value) ? $value : (int) $value;
    }

    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default);
        
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            return in_array(strtolower($value), ['true', '1', 'yes', 'on'], true);
        }
        
        return (bool) $value;
    }

    public function getArray(string $key, array $default = []): array
    {
        $value = $this->get($key, $default);
        return is_array($value) ? $value : $default;
    }

    /**
     * Obtiene valor con validación
     */
    public function getRequired(string $key): mixed
    {
        if (!$this->has($key)) {
            throw new \InvalidArgumentException("Required configuration key '{$key}' not found");
        }
        
        return $this->get($key);
    }

    /**
     * Valida que existan todas las claves requeridas
     */
    public function validateRequired(array $requiredKeys): void
    {
        $missing = [];
        
        foreach ($requiredKeys as $key) {
            if (!$this->has($key)) {
                $missing[] = $key;
            }
        }
        
        if ($missing) {
            throw new \InvalidArgumentException(
                'Missing required configuration keys: ' . implode(', ', $missing)
            );
        }
    }

    /**
     * Crea configuración específica de sección
     */
    public function section(string $section): self
    {
        return new self($this->getArray($section));
    }

    private function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        
        return $value;
    }

    private function has(string $key): bool
    {
        return $this->get($key) !== null;
    }
}
