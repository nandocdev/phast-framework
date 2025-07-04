<?php
/**
 * @package     phast/core
 * @subpackage  Validation
 * @file        Validator
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Simple validation implementation using Respect\Validation
 */

declare(strict_types=1);

namespace Phast\Core\Validation;

use Respect\Validation\Validator as V;

class Validator implements ValidatorInterface
{
    private array $errors = [];

    public function validate(array $data, array $rules): array
    {
        $this->errors = [];
        $validated = [];

        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            $fieldRules = $this->parseRules($ruleSet);
            
            try {
                $this->validateField($field, $value, $fieldRules);
                $validated[$field] = $value;
            } catch (ValidationException $e) {
                $this->errors[$field] = $e->getErrors();
            }
        }

        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }

        return $validated;
    }

    public function passes(array $data, array $rules): bool
    {
        try {
            $this->validate($data, $rules);
            return true;
        } catch (ValidationException $e) {
            return false;
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function parseRules(string|array $rules): array
    {
        if (is_string($rules)) {
            return explode('|', $rules);
        }

        return $rules;
    }

    private function validateField(string $field, mixed $value, array $rules): void
    {
        $errors = [];

        foreach ($rules as $rule) {
            try {
                $this->applyRule($field, $value, $rule);
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        $parts = explode(':', $rule, 2);
        $ruleName = $parts[0];
        $parameter = $parts[1] ?? null;

        switch ($ruleName) {
            case 'required':
                if (!V::notEmpty()->validate($value)) {
                    throw new \Exception("The {$field} field is required.");
                }
                break;

            case 'email':
                if ($value !== null && !V::email()->validate($value)) {
                    throw new \Exception("The {$field} field must be a valid email address.");
                }
                break;

            case 'min':
                if ($value !== null && !V::length((int)$parameter, null)->validate($value)) {
                    throw new \Exception("The {$field} field must be at least {$parameter} characters.");
                }
                break;

            case 'max':
                if ($value !== null && !V::length(null, (int)$parameter)->validate($value)) {
                    throw new \Exception("The {$field} field may not be greater than {$parameter} characters.");
                }
                break;

            case 'numeric':
                if ($value !== null && !V::numeric()->validate($value)) {
                    throw new \Exception("The {$field} field must be numeric.");
                }
                break;

            case 'integer':
                if ($value !== null && !V::intVal()->validate($value)) {
                    throw new \Exception("The {$field} field must be an integer.");
                }
                break;

            case 'alpha':
                if ($value !== null && !V::alpha()->validate($value)) {
                    throw new \Exception("The {$field} field may only contain letters.");
                }
                break;

            case 'alpha_num':
                if ($value !== null && !V::alnum()->validate($value)) {
                    throw new \Exception("The {$field} field may only contain letters and numbers.");
                }
                break;

            case 'in':
                $options = explode(',', $parameter);
                if ($value !== null && !V::in($options)->validate($value)) {
                    throw new \Exception("The {$field} field must be one of: " . implode(', ', $options));
                }
                break;

            case 'url':
                if ($value !== null && !V::url()->validate($value)) {
                    throw new \Exception("The {$field} field must be a valid URL.");
                }
                break;

            case 'date':
                if ($value !== null && !V::date()->validate($value)) {
                    throw new \Exception("The {$field} field must be a valid date.");
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                // This would need access to all data, simplified for now
                break;

            default:
                throw new \Exception("Unknown validation rule: {$ruleName}");
        }
    }
}
