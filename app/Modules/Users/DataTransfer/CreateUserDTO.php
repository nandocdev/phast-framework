<?php
/**
 * @package     phast/app
 * @subpackage  Modules/Users/DataTransfer
 * @file        CreateUserDTO
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description DTO for creating a new user
 */

declare(strict_types=1);

namespace Phast\App\Modules\Users\DataTransfer;

use Phast\Core\DataTransfer\DataTransferObject;
use Phast\Core\Validation\ValidationResult;
use Phast\Core\Validation\SimpleValidator;

/**
 * Data Transfer Object for creating a user
 */
class CreateUserDTO extends DataTransferObject
{
    public readonly string $name;
    public readonly string $email;
    public readonly string $password;
    
    protected function validate(array $data): ValidationResult
    {
        $validator = new SimpleValidator();
        return $validator->validateWithResult($data, $this->getRules());
    }
    
    protected function populate(array $data): static
    {
        $instance = clone $this;
        $instance->name = $data['name'];
        $instance->email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $instance->password = $data['password'];
        
        return $instance;
    }
    
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
    
    protected function getRules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'max:255'],
        ];
    }
    
    /**
     * Get password hash for storage
     */
    public function getHashedPassword(): string
    {
        return password_hash($this->password, PASSWORD_DEFAULT);
    }
}
