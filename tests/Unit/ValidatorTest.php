<?php

declare(strict_types=1);

namespace Tests\Unit;

use Naf\Form\Core\Validator;
use Tests\NafTestCase;

class ValidatorTest extends NafTestCase
{
    public function testRegisterValidator()
    {
        $validator = new Validator();

        Validator::register('is_string', fn($value) => is_string($value), 'Test');

        $result = $validator->validate(
            ['field' => 'testResult'],
            ['field' => 'is_string'],
        )->isValid();

        $this->assertTrue($result);
    }

    public function testRequiredValidatorFailsOnEmptyValue()
    {
        $validator = new Validator();

        Validator::register('required', fn($val) => !empty($val), 'Field is required.');

        $validator->validate(['name' => ''], ['name' => 'required']);

        $this->assertFalse($validator->isValid());
        $this->assertNotEmpty($validator->getErrorMessage('name'));
    }

    public function testEmailValidator()
    {
        $validator = new Validator();

        Validator::register('email', fn($val) => (bool) filter_var($val, FILTER_VALIDATE_EMAIL), 'Invalid email.');

        $validator->validate(['email' => 'invalid'], ['email' => 'email']);

        $this->assertFalse($validator->isValid());
        $this->assertNotEmpty($validator->getErrorMessage('email'));
    }

    public function testMinValidator()
    {
        $validator = new Validator();

        Validator::register('min', fn($val, $p) => empty($val) || mb_strlen((string) $val) >= (int) $p, 'Min %d chars.');

        $validator->validate(['text' => 'hi'], ['text' => 'min:3']);

        $this->assertFalse($validator->isValid());
        $this->assertNotEmpty($validator->getErrorMessage('text'));
    }

    public function testMaxValidator()
    {
        $validator = new Validator();

        Validator::register('max', fn($val, $p) => empty($val) || mb_strlen((string) $val) <= (int) $p, 'Max %d chars.');

        $validator->validate(['text' => 'abcdef'], ['text' => 'max:3']);

        $this->assertFalse($validator->isValid());
        $this->assertNotEmpty($validator->getErrorMessage('text'));
    }

    public function testBooleanValidator()
    {
        $validator = new Validator();

        Validator::register('boolean', fn($val) => is_bool($val), 'Not boolean.');

        $validator->validate(['flag' => 'nope'], ['flag' => 'boolean']);

        $this->assertFalse($validator->isValid());
        $this->assertNotEmpty($validator->getErrorMessage('flag'));
    }

    public function testMultipleRulesOnSameField()
    {
        $validator = new Validator();

        Validator::register('required', fn($val) => !empty($val), 'Required.');
        Validator::register('min', fn($val, $p) => mb_strlen((string) $val) >= (int) $p, 'Min %d chars.');

        $validator->validate(
            ['username' => 'a'],
            ['username' => 'required|min:3'],
        );

        $this->assertFalse($validator->isValid());

        $errors = $validator->getErrorMessage('username');

        $this->assertCount(1, $errors);
    }

    public function testCustomErrorMessages()
    {
        $validator = new Validator();

        Validator::register('min', fn($val, $p) => mb_strlen((string) $val) >= (int) $p, 'Min %d chars.');

        $validator->validate(
            ['password' => 'xx'],
            ['password' => 'min:8'],
            ['password' => [
                'min' => 'Too short (need %s chars).',
            ]],
        );

        $this->assertFalse($validator->isValid());

        $messages = $validator->getErrorMessage('password');
        $this->assertSame('Too short (need 8 chars).', $messages[0]);
    }

    public function testMultipleFieldsValidation()
    {
        $validator = new Validator();

        Validator::register('email', fn($val) => (bool) filter_var($val, FILTER_VALIDATE_EMAIL), 'Invalid.');
        Validator::register('required', fn($val) => !empty($val), 'Required.');

        $validator->validate(
            ['email' => 'wrong', 'name' => ''],
            ['email' => 'email', 'name' => 'required'],
        );

        $this->assertFalse($validator->isValid());
        $this->assertNotEmpty($validator->getErrorMessage('email'));
        $this->assertNotEmpty($validator->getErrorMessage('name'));
    }
}
