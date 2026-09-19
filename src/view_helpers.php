<?php

declare(strict_types=1);

namespace Naf\Form;

use Naf\Form\Core\Validator;
use Naf\Form\Support\Csrf;

use function Naf\app;
use function Naf\guard;
use function Naf\param;
use function Naf\request;

function memory(string $key, mixed $default = null): ?string
{
    return param()->get($key);
}

function memory_checked(string $key, mixed $value = 'on'): string
{
    $input = memory($key);

    return $input === $value ? 'checked' : '';
}

function memory_selected(string $key, mixed $expectedValue): string
{
    $input = memory($key);

    return $input == $expectedValue ? 'selected' : '';
}

function validator(): Validator
{
    return app()->container()->get(Validator::class);
}

function error($field, Validator $validator): ?string
{
    if (!is_post()) {
        return null;
    }

    if ($validator->getErrorMessage($field)) {
        return '<div class="error-msg">' . implode(PHP_EOL, $validator->getErrorMessage($field)) . '</div>';
    }

    return null;
}

function has_error($field, Validator $validator): bool
{
    return error($field, $validator) !== null;
}

function error_class($field, Validator $validator): string
{
    return has_error($field, $validator) ? 'error' : '';
}

function is_post(): bool
{
    return request()->getMethod() === 'POST';
}

function csrf(): Csrf
{
    return guard()->csrf();
}
