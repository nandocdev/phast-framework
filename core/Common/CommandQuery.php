<?php

declare(strict_types=1);

namespace Phast\Core\Common;

/**
 * Base para Commands (operaciones que modifican estado)
 */
abstract class Command
{
    public function __construct(protected readonly array $data = []) {}

    public function toArray(): array
    {
        return $this->data;
    }
}

/**
 * Base para Queries (operaciones de lectura)
 */
abstract class Query
{
    public function __construct(protected readonly array $criteria = []) {}

    public function getCriteria(): array
    {
        return $this->criteria;
    }
}
