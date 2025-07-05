<?php

declare(strict_types=1);

namespace Phast\Core\Database;

use Doctrine\ORM\EntityManagerInterface;
use Throwable;

/**
 * Decorator para manejar transacciones de forma declarativa
 */
class TransactionalService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    /**
     * Ejecuta una operación dentro de una transacción
     */
    public function execute(callable $operation): mixed
    {
        $this->entityManager->beginTransaction();

        try {
            $result = $operation();
            $this->entityManager->commit();
            
            return $result;
        } catch (Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Ejecuta múltiples operaciones en una sola transacción
     */
    public function batch(array $operations): array
    {
        return $this->execute(function() use ($operations) {
            $results = [];
            
            foreach ($operations as $operation) {
                $results[] = $operation();
            }
            
            return $results;
        });
    }
}
