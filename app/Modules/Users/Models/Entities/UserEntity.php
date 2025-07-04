<?php
/**
 * @package     phast/app
 * @subpackage  Modules/Users/Models/Entities
 * @file        UserEntity
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description User entity
 */

declare(strict_types=1);

namespace Phast\App\Modules\Users\Models\Entities;

use Carbon\Carbon;

class UserEntity
{
    private ?int $id = null;
    private string $name;
    private string $email;
    private ?string $password = null;
    private ?Carbon $emailVerifiedAt = null;
    private ?Carbon $createdAt = null;
    private ?Carbon $updatedAt = null;

    public function __construct(
        string $name,
        string $email,
        ?string $password = null,
        ?int $id = null
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->id = $id;
        
        if ($id === null) {
            $this->createdAt = Carbon::now();
        }
        $this->updatedAt = Carbon::now();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        $this->touch();
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        $this->touch();
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        $this->touch();
        return $this;
    }

    public function verifyPassword(string $password): bool
    {
        return $this->password && password_verify($password, $this->password);
    }

    public function getEmailVerifiedAt(): ?Carbon
    {
        return $this->emailVerifiedAt;
    }

    public function markEmailAsVerified(): self
    {
        $this->emailVerifiedAt = Carbon::now();
        $this->touch();
        return $this;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    public function getCreatedAt(): ?Carbon
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?Carbon
    {
        return $this->updatedAt;
    }

    public function touch(): self
    {
        $this->updatedAt = Carbon::now();
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->emailVerifiedAt?->toISOString(),
            'created_at' => $this->createdAt?->toISOString(),
            'updated_at' => $this->updatedAt?->toISOString(),
        ];
    }

    public static function fromArray(array $data): self
    {
        $user = new self(
            $data['name'],
            $data['email'],
            $data['password'] ?? null,
            $data['id'] ?? null
        );

        if (isset($data['email_verified_at'])) {
            $user->emailVerifiedAt = Carbon::parse($data['email_verified_at']);
        }

        if (isset($data['created_at'])) {
            $user->createdAt = Carbon::parse($data['created_at']);
        }

        if (isset($data['updated_at'])) {
            $user->updatedAt = Carbon::parse($data['updated_at']);
        }

        return $user;
    }
}
