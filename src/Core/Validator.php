<?php

declare(strict_types=1);

namespace Naf\Form\Core;

use InvalidArgumentException;

class Validator
{
    protected static array $registry = [];
    protected array $errors          = [];

    public static function register(string $name, callable $callback, string $defaultMessage): void
    {
        self::$registry[$name] = [
            'callback' => $callback,
            'message'  => $defaultMessage,
        ];
    }

    public function validate(array $data, array $rules, array $messages = []): self
    {
        $this->errors = [];

        foreach ($rules as $field => $ruleSet) {
            $rulesForField = is_string($ruleSet) ? explode('|', $ruleSet) : (array) $ruleSet;

            foreach ($rulesForField as $rule) {
                $params = null;

                if (str_contains($rule, ':')) {
                    [$rule, $params] = explode(':', $rule, 2);
                }

                if (!isset(self::$registry[$rule])) {
                    throw new InvalidArgumentException("Validator '$rule' not found.");
                }

                $callback = self::$registry[$rule]['callback'];
                $result   = $callback($data[$field] ?? null, $params, $data);

                if (true !== $result) {
                    $message = $messages[$field][$rule]
                        ?? self::$registry[$rule]['message'];

                    $this->errors[$field][] = sprintf($message, $params);
                }
            }
        }

        return $this;
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function getErrorMessages(): array
    {
        return $this->errors;
    }

    public function getErrorMessage(string $field): ?array
    {
        return $this->errors[$field] ?? null;
    }
}
