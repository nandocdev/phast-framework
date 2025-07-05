<?php
/**
 * @package     phast/core
 * @subpackage  Exceptions/Domain
 * @file        EntityNotFoundException
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Exception for when an entity is not found
 */

declare(strict_types=1);

namespace Phast\Core\Exceptions\Domain;

/**
 * Thrown when an entity cannot be found
 */
class EntityNotFoundException extends DomainException
{
    public static function withId(string $entityType, int|string $id): self
    {
        return new self(
            message: "Entity '{$entityType}' with ID '{$id}' not found",
            context: [
                'entity_type' => $entityType,
                'entity_id' => $id,
            ]
        );
    }
    
    public static function withCriteria(string $entityType, array $criteria): self
    {
        $criteriaString = implode(', ', array_map(
            fn($key, $value) => "{$key}={$value}",
            array_keys($criteria),
            array_values($criteria)
        ));
        
        return new self(
            message: "Entity '{$entityType}' not found with criteria: {$criteriaString}",
            context: [
                'entity_type' => $entityType,
                'criteria' => $criteria,
            ]
        );
    }
    
    public function getUserMessage(): string
    {
        $entityType = $this->context['entity_type'] ?? 'Resource';
        return "The requested {$entityType} could not be found.";
    }
}
