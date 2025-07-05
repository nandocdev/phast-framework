<?php

declare(strict_types=1);

namespace Phast\Core\RateLimit\Strategies;

use Phast\Core\Http\Request;

/**
 * Estrategia para identificar requests en rate limiting
 */
interface IdentifierStrategyInterface {
   public function getIdentifier(Request $request): string;
}

/**
 * Identificación por IP
 */
class IpStrategy implements IdentifierStrategyInterface {
   public function getIdentifier(Request $request): string {
      return 'ip:' . ($request->getIp() ?? 'unknown');
   }
}

/**
 * Identificación por usuario autenticado
 */
class UserStrategy implements IdentifierStrategyInterface {
   public function getIdentifier(Request $request): string {
      // Asumiendo que tienes un método para obtener el usuario
      $userId = $request->getAttribute('user_id');

      return $userId ? "user:{$userId}" : 'ip:' . ($request->getIp() ?? 'unknown');
   }
}

/**
 * Identificación por API key
 */
class ApiKeyStrategy implements IdentifierStrategyInterface {
   public function getIdentifier(Request $request): string {
      $apiKey = $request->getHeader('X-API-Key');

      return $apiKey ? "api_key:{$apiKey}" : 'ip:' . ($request->getIp() ?? 'unknown');
   }
}

/**
 * Identificación combinada
 */
class CompositeStrategy implements IdentifierStrategyInterface {
   public function __construct(
      private readonly array $strategies = []
   ) {
   }

   public function getIdentifier(Request $request): string {
      $identifiers = [];

      foreach ($this->strategies as $strategy) {
         $identifiers[] = $strategy->getIdentifier($request);
      }

      return implode('|', $identifiers);
   }
}
