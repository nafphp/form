<?php

declare(strict_types=1);

namespace Naf\Form\Events;

use Naf\Core\Route;
use Naf\Exceptions\AbortException;
use Psr\Http\Message\ServerRequestInterface;

use function Naf\abort;
use function Naf\app;
use function Naf\config;
use function Naf\Form\csrf;

class CsrfListener
{
    /**
     * @param ServerRequestInterface $request
     *
     * @return void
     * @throws AbortException
     */
    public function handle(ServerRequestInterface $request): void
    {
        if (config('csrf_validation', true) === false) {
            return;
        }

        if (\in_array(strtoupper($request->getMethod()), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return;
        }

        if ($this->isExempt()) {
            return;
        }

        $body      = $request->getParsedBody();
        $csrfToken = is_array($body) && array_key_exists('_csrf', $body)
                ? $body['_csrf']
                : $request->getHeaderLine('X-CSRF-Token');

        if (!is_string($csrfToken) || $csrfToken === '' || strlen($csrfToken) > 1024) {
            abort(400, 'CSRF token missing or malformed.');
        }

        if (false === csrf()->validate($csrfToken)) {
            abort(400, 'CSRF token invalid.');
        }
    }

    /**
     * Whether this route authenticates its callers some other way.
     *
     * A protocol endpoint called by a program carries no session to ride on and
     * no form to put a token in, so a CSRF check there refuses legitimate
     * requests while protecting nothing. Such routes are named one at a time,
     * never guessed from a path.
     *
     * The list is a map rather than an array of names so that several plugins can
     * contribute to it without array_replace_recursive letting one overwrite
     * another by position — and so an application can switch one back off:
     *
     *     'csrf_exempt_routes' => ['oauth.token' => true, 'some.other' => false],
     *
     * @return bool
     */
    private function isExempt(): bool
    {
        $name = app()->container()->get(Route::class)->current();

        if ($name === null) {
            return false;
        }

        $exempt = config('csrf_exempt_routes', []);

        return is_array($exempt) && ($exempt[$name] ?? false) === true;
    }
}
