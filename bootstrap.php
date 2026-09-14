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

    \Naf\Form\Support\DefaultRules::register();

    return new Validator();

});

app()->container()->get(EventManager::class)
    ->listen(Event::CONTROLLER_CALLING, [CsrfListener::class, 'handle']);
