<?php

declare(strict_types=1);

namespace Tests\Unit;

use Naf\Support\RequestParameter;
use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\RequestInterface;
use Tests\NafTestCase;

use function Naf\app;
use function Naf\Form\is_post;
use function Naf\Form\memory;
use function Naf\Form\memory_checked;

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

    public function testMemoryReturnsTheSubmittedString(): void
    {
        $this->withInput(['email' => 'ada@example.com', 'age' => 36]);

        self::assertSame('ada@example.com', memory('email'));
        self::assertSame('36', memory('age'));
    }

    public function testMemoryFallsBackToTheDefault(): void
    {
        $this->withInput([]);

        self::assertSame('Germany', memory('country', 'Germany'));
        self::assertNull(memory('country'));
    }

    public function testMemoryDoesNotFailForAnArrayValue(): void
    {
        // email[]=x used to end in a TypeError and a 500 page while refilling the form.
        $this->withInput(['email' => ['x'], 'agree' => ['on']]);

        self::assertNull(memory('email'));
        self::assertSame('', memory('email', ''));
        self::assertSame('', memory_checked('agree'));
    }

    private function withInput(array $input): void
    {
        $request = (new ServerRequest('POST', '/form'))->withParsedBody($input);
        app()->container()->set(RequestParameter::class, new RequestParameter($request));
    }

    private function withRequest(string $method): void
    {
        app()->container()->set(
            RequestInterface::class,
            new ServerRequest($method, '/test'),
        );
    }
}
