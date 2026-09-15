<?php

declare(strict_types=1);

namespace Naf\Form\Support;

use DateTimeImmutable;
use Naf\Form\Core\Validator;

/** Built-in scalar/shape rules. Domain validation stays with the application. */
final class DefaultRules
{
    public static function register(): void
    {
        Validator::register(
            'required',
            static fn($v) => $v !== null && $v !== '' && $v !== [],
            'Field is required.',
        );
        Validator::register('string', static fn($v) => is_string($v), 'Must be text.');
        Validator::register('array', static fn($v) => is_array($v), 'Must be an array.');
        Validator::register(
            'integer',
            static fn($v) => (is_int($v) || is_string($v))
                && filter_var($v, FILTER_VALIDATE_INT) !== false,
            'Must be an integer.',
        );
        Validator::register(
            'email',
            static fn($v) => is_string($v) && filter_var($v, FILTER_VALIDATE_EMAIL) !== false,
            'Please enter a valid email address.',
        );
        Validator::register(
            'min',
            static fn($v, $p) => $v === null
                || $v === ''
                || (is_scalar($v) && mb_strlen((string) $v) >= (int) $p),
            'At least %d characters.',
        );
        Validator::register(
            'max',
            static fn($v, $p) => $v === null
                || $v === ''
                || (is_scalar($v) && mb_strlen((string) $v) <= (int) $p),
            'Maximum of %d characters.',
        );
        Validator::register(
            'boolean',
            static fn($v) => is_scalar($v)
                && filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null,
            'Is not a boolean value.',
        );
        Validator::register(
            'date',
            static function ($v): bool {
                if (!is_string($v) || str_contains($v, "\0")) {
                    return false;
                }
                $date = DateTimeImmutable::createFromFormat('!Y-m-d', $v);

                return $date !== false && $date->format('Y-m-d') === $v;
            },
            'Must be a valid date (YYYY-MM-DD).',
        );
    }
}
