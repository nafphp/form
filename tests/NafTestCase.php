<?php

declare(strict_types=1);

namespace Tests;

use Naf\Form\Core\Validator;
use PHPUnit\Framework\TestCase;

class NafTestCase extends TestCase
{

    protected function tearDown(): void
    {
        // Reset the static registry after each test
        $reflection = new \ReflectionClass(Validator::class);
        $property = $reflection->getProperty('registry');
        // setAccessible() has done nothing since PHP 8.1 and is deprecated in 8.5,
        // where a deprecation fails this suite outright.
        $property->setValue(null, []);
        parent::tearDown();
    }

}