<?php

declare(strict_types=1);

use Naf\Form\Core\Validator;
use Naf\Form\Events\CsrfListener;
use Naf\Form\Support\Csrf;
use Naf\Core\EventManager;
use Naf\Core\Event;
use function Naf\app;
use function Naf\guard;

guard()->register('csrf', function() {
    return new Csrf();
});

app()->container()->set(Validator::class, function() {

    Validator::register('required', fn($val) => !empty($val), 'Field is required.');
    Validator::register('email', fn($val) => (bool)filter_var($val, FILTER_VALIDATE_EMAIL), 'Please enter a valid email address.');
    Validator::register('min', fn($val, $p) => empty($val) || mb_strlen((string)$val) >= (int)$p, 'At least %d characters.');
    Validator::register('max', fn($val, $p) => empty($val) || mb_strlen((string)$val) <= (int)$p, 'Maximum of %d characters.');
    Validator::register('boolean', fn($val) => filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null, 'Is not a boolean value.');

    return new Validator();

});

app()->container()->get(EventManager::class)
    ->listen(Event::CONTROLLER_CALLING, [CsrfListener::class, 'handle']);
