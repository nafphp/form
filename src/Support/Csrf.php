<?php

declare(strict_types=1);

namespace Naf\Form\Support;

use function Naf\Session\session;

class Csrf
{

    public function generate(): string
    {
        session()->start();
        $csrfToken = bin2hex(random_bytes(16));
        session()->set('_csrf', $csrfToken);
        return $csrfToken;
    }

    public function validate(string $token): bool
    {
        session()->start();
        $csrfToken = session()->get('_csrf');
        return is_string($csrfToken) && hash_equals($csrfToken, $token);
    }

}