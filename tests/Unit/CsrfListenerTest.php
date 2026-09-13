<?php

declare(strict_types=1);

namespace Tests\Unit;

use Naf\Core\Config;
use Naf\Core\Route;
use Nyholm\Psr7\ServerRequest;
use Naf\Form\Events\CsrfListener;
use Naf\Exceptions\AbortException;
use Tests\NafTestCase;
use function Naf\app;
use function Naf\Session\session;

class CsrfListenerTest extends NafTestCase
{

    public function testSuccessful()
    {
        $this->expectNotToPerformAssertions();

        session()->start();
        $_SESSION['_csrf'] = 'test';
        $requestMock = new ServerRequest('POST', '/test');
        $requestMock = $requestMock->withParsedBody(['_csrf' => 'test']);
        $listener = new CsrfListener();
        $listener->handle($requestMock);
    }

    public function testShouldNotInterceptWithDifferentMethod()
    {
        $this->expectNotToPerformAssertions();

        $request = new ServerRequest('GET', '/test');
        $listener = new CsrfListener();
        $listener->handle($request);
    }

    public function testMissingCsrfToken()
    {
        $this->expectException(AbortException::class);

        $requestMock = new ServerRequest('POST', '/test');
        $listener = new CsrfListener();
        $listener->handle($requestMock);
    }

    public function testWrongCsrfToken()
    {
        $this->expectException(AbortException::class);

        session()->start();
        $_SESSION['_csrf'] = 'other';
        $requestMock = new ServerRequest('POST', '/test');
        $requestMock = $requestMock->withParsedBody(['_csrf' => 'test']);
        $listener = new CsrfListener();
        $listener->handle($requestMock);
    }

    public function testShouldIgnoreWhenAuthorizationHeaderIsPresent()
    {
        $this->expectNotToPerformAssertions();

        $requestMock = new ServerRequest('POST', '/test');
        $requestMock = $requestMock->withHeader('Authorization', 'Bearer test');
        $listener = new CsrfListener();
        $listener->handle($requestMock);
    }


    public function testShouldNotIgnoreABrowserSuppliedAuthorizationHeader()
    {
        $this->expectException(AbortException::class);

        // A browser attaches Basic credentials on its own, so a request carrying
        // them is exactly what CSRF protects against. Only a Bearer token, which
        // nothing attaches automatically, stands for a deliberate caller.
        $request = (new ServerRequest('POST', '/test'))
            ->withHeader('Authorization', 'Basic ' . base64_encode('user:pass'));

        (new CsrfListener())->handle($request);
    }

    public function testAnExemptRouteIsNotAskedForAToken()
    {
        $this->expectNotToPerformAssertions();

        // A token endpoint is called by a program: no session to ride on, no form
        // to carry a token, nothing for CSRF to protect.
        $this->matchRoute('oauth.token', ['oauth.token' => true]);

        (new CsrfListener())->handle(new ServerRequest('POST', '/oauth/token'));
    }

    public function testAnyOtherRouteStillNeedsOne()
    {
        $this->expectException(AbortException::class);

        $this->matchRoute('oauth.authorize.decide', ['oauth.token' => true]);

        (new CsrfListener())->handle(new ServerRequest('POST', '/oauth/authorize'));
    }

    public function testAnExemptionCanBeSwitchedBackOff()
    {
        $this->expectException(AbortException::class);

        $this->matchRoute('oauth.token', ['oauth.token' => false]);

        (new CsrfListener())->handle(new ServerRequest('POST', '/oauth/token'));
    }

    private function matchRoute(string $name, array $exempt): void
    {
        $container = app()->container();

        $route = new Route();
        $route->add('POST', '/matched', static fn() => null, $name);
        $route->find('/matched', 'POST');

        $container->set(Route::class, $route);
        $container->set(Config::class, new Config(['csrf_exempt_routes' => $exempt]));
    }
}
