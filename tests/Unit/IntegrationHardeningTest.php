<?php
declare(strict_types=1);
namespace Tests\Unit;
use Naf\Form\{Core\Validator, Events\CsrfListener, Support\Csrf, Support\DefaultRules};
use Naf\Exceptions\AbortException;
use Nyholm\Psr7\ServerRequest;
use Tests\NafTestCase;
use function Naf\Session\session;
final class IntegrationHardeningTest extends NafTestCase
{
    public function testTokenSurvivesMultiplePagesAndRotatesExplicitly(): void
    {
        session()->start(); session()->forget('_csrf');
        $csrf=new Csrf(); $first=$csrf->token();
        $this->assertSame($first,$csrf->token());
        $this->assertTrue($csrf->validate($first));
        $second=$csrf->generate();
        $this->assertNotSame($first,$second);
        $this->assertFalse($csrf->validate($first));
        $this->assertTrue($csrf->validate($second));
    }
    public function testUnsafeMethodsAndMalformedBodiesCannotBypassCsrf(): void
    {
        foreach (['POST','PUT','PATCH','DELETE'] as $method) {
            foreach ([null,['_csrf'=>['token']],['_csrf'=>1]] as $body) {
                try {
                    (new CsrfListener())->handle((new ServerRequest($method,'/test'))->withHeader('Authorization','Bearer invalid')->withParsedBody($body));
                    $this->fail('Must reject '.$method);
                } catch (AbortException $e) { $this->assertSame(400,$e->getCode()); }
            }
        }
    }
    public function testJsonHeaderUsesTheSameSessionToken(): void
    {
        $token=(new Csrf())->token();
        (new CsrfListener())->handle((new ServerRequest('PATCH','/test'))->withHeader('X-CSRF-Token',$token));
        $this->assertTrue((new Csrf())->validate($token));
    }
    public function testBuiltInRulesRejectWrongTypesAndAcceptZero(): void
    {
        DefaultRules::register(); $v=new Validator();
        $this->assertTrue($v->validate(['v'=>0],['v'=>'required|integer'])->isValid());
        foreach ([[],new \stdClass(),true,'1.2'] as $value) $this->assertFalse($v->validate(['v'=>$value],['v'=>'integer'])->isValid());
        $this->assertFalse($v->validate(['v'=>[]],['v'=>'string|min:1|max:20'])->isValid());
        $this->assertTrue($v->validate(['v'=>'2024-02-29'],['v'=>'date'])->isValid());
        $this->assertFalse($v->validate(['v'=>'2025-02-29'],['v'=>'date'])->isValid());
        $this->assertFalse($v->validate(['v'=>null],['v'=>'boolean'])->isValid());
    }
}
