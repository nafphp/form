# Working on naf/form

NAF is a small PHP framework with optional Composer plugins. Its core owns boot,
configuration, the service container, routing, events and PSR-7 responses. Prefer existing
NAF helpers, services and extension interfaces; keep application business rules in the host.
This package declares `type: naf-plugin` and is discovered after installation in a NAF host.
The plugin repository itself is not the application's web root.

Before changing code, read the [shared contribution workflow](https://github.com/nafphp/docs/blob/main/AGENT_WORKFLOW.md)
and [release procedure](https://github.com/nafphp/docs/blob/main/RELEASING.md).
In the multi-repository workspace, the same documents are in the sibling `docs/` checkout;
use the linked copies when working from a standalone clone. Preserve other contributors' work.
Review and update user documentation with every behavior change. Source fixes use an RC branch;
verified documentation-only changes can be merged and published by the agent.

## What this plugin does

`naf/form` provides validation, form-memory helpers and a CSRF listener. Install with
`composer require naf/form`; it depends on `naf/session` and `ext-mbstring`. Import helpers
from `Naf\Form`, not the global namespace. HTML rendering/escaping belongs to `naf/view`.

## Use it

Inside a handler, check the input shape before passing a deliberately selected field set:

```php
<?php
use function Naf\{abort, param};
use function Naf\Form\validator;

$email = param()->get('email', '');
if (!is_string($email)) {
    abort(400, 'Email must be text.');
}
$check = validator()->validate(['email' => $email], ['email' => 'required|email']);
$errors = $check->getErrorMessage('email') ?? [];
```

Use `isValid()` to decide whether to perform the operation. Include a `_csrf` field generated
by `csrf()->generate()` in mutating forms. Generate once and reuse it for multiple forms on
a page. `memory()` reads current request input; it neither escapes nor persists a redirect.
Use `Naf\View\s()` for HTML. The shared validator's errors reset on each `validate()` call.

## Change it here

[Validator](src/Core/Validator.php), [helpers](src/view_helpers.php),
[CsrfListener](src/Events/CsrfListener.php), [Csrf](src/Support/Csrf.php) and [bootstrap](bootstrap.php)
are the primary entry points. Extend rules through `Validator::register()`.
The 0.2.3 candidate checks all methods except GET/HEAD/OPTIONS, including PATCH. A Bearer
header does not bypass CSRF; protocol endpoints need explicitly exempt routes and authentication.
Keep exceptions narrowly named in `csrf_exempt_routes`; do not disable validation globally.
Check the installed version before relying on helper fixes such as `is_post()` in 0.2.1.

## Verify

Run `composer test` and `composer validate --strict`. Extend [tests](tests/); check valid and
invalid data, error reset and scalar/array input. HTTP changes need valid, missing and invalid
CSRF tests with actual session cookies and escaped form redisplay. No `analyse` script exists.

User docs: [Forms](https://nafphp.github.io/docs/forms/),
[contact recipe](https://nafphp.github.io/docs/recipes/contact-form/).
