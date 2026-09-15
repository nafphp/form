<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\RequestInterface;
use Tests\NafTestCase;

use function Naf\app;
use function Naf\Form\is_post;

/**
 * The view helpers reach for the current request, and how they reach for it is
 * the thing that broke: is_post() asked the container for a service named
 * 'request', which nothing has registered since the framework moved to keying
 * it by RequestInterface. It threw on every call rather than answering false,
 * so the starter application's own contact page was a 500.
 */
class ViewHelpersTest extends NafTestCase
{
    public function testIsPostAnswersTrueForAPostRequest(): void
    {
        $this->withRequest('POST');

        self::assertTrue(is_post());
    }

    public function testIsPostAnswersFalseForAGetRequest(): void
    {
        $this->withRequest('GET');

        self::assertFalse(is_post());
    }

    private function withRequest(string $method): void
    {
        app()->container()->set(
            RequestInterface::class,
            new ServerRequest($method, '/test'),
        );
    }
}
