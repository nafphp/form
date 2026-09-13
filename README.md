<div style="text-align: center;">

![NAF](assets/naf-logo-small-square.png)

[![NAF Form Plugin](https://github.com/nafphp/form/actions/workflows/php.yml/badge.svg)](https://github.com/nafphp/form/actions/workflows/php.yml)

</div>

[← Back to NAF](https://github.com/nafphp/framework)

---

# naf/form

> **Form handling the NAF way — minimal, secure, intuitive, extendable.**

This plugin provides **form memory**, **CSRF protection**, a flexible **Validator system**,
and a full set of **view helpers** for easy form handling in your NAF applications.

Everything is registered automatically and works without configuration.

---

## 📦 Features

* ✔️ **Form input memory** (`memory()`, `memory_checked()`, `memory_selected()`)
* ✔️ **CSRF protection** via automatic event listener
* ✔️ **Validator system** with dynamic rule registry
* ✔️ **Built-in rules:** `required`, `email`, `min`, `max`, `boolean`
* ✔️ **Custom rules** via `Validator::register()`
* ✔️ **View helpers** (`error()`, `has_error()`, `error_class()`, `validator()`)
* ✔️ Automatically integrates into `guard()` and the event system
* ✔️ Zero configuration — plug and play

---

## 📥 Installation

```bash
composer require naf/form
```

The plugin registers itself. No additional setup needed.

---

## 🚀 Usage

### 🧠 Form Memory

### `memory($key, $default = null)`

Restores previous user input:

```php
<input name="email" value="<?= memory('email') ?>">
```

### `memory_checked($key, $value = 'on')`

Works for checkboxes:

```php
<input type="checkbox" name="terms" <?= memory_checked('terms') ?>>
```

### `memory_selected($key, $expected)`

Works for selects:

```php
<option value="de" <?= memory_selected('country', 'de') ?>>Germany</option>
```

Memory is powered by `param()` and persists automatically after POST requests.

---

## 🧪 Validation

Create a Validator and run rules:

```php
validator()->validate(request()->getParsedBody(), [
    'email'    => 'required|email',
    'password' => 'required|min:8',
]);
```

Check validity:

```php
if (validator()->isValid()) {
    // continue...
}
```

Custom messages:

```php
validator()->validate($request->getParsedBody(), [
    'name' => 'required|min:3'
], [
    'name' => [
        'required' => 'Please enter your name.',
        'min'      => 'At least %s characters.'
    ]
]);
```

Get errors:

```php
validator()->getErrorMessages();
validator()->getErrorMessage('email');
```

---

### 🧩 Built-in Validation Rules

The plugin registers these rules automatically:

```php
Validator::register('required', fn($val) => !empty($val), 'Field is required.');
Validator::register('email', fn($val) => (bool)filter_var($val, FILTER_VALIDATE_EMAIL), 'Please enter a valid email address.');
Validator::register('min', fn($val, $p) => empty($val) || mb_strlen((string)$val) >= (int)$p, 'At least %d characters.');
Validator::register('max', fn($val, $p) => empty($val) || mb_strlen((string)$val) <= (int)$p, 'Maximum of %d characters.');
Validator::register('boolean', fn($val) => filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null, 'Is not a boolean value.');

### Adding your own rule:

```php
Validator::register('starts_with', function ($value, $param) {
    return str_starts_with((string)$value, $param);
}, "Value must start with '%s'.");
```

---

## 🎨 View Helpers for Errors

### `error($field, Validator $validator)`

Outputs error messages wrapped in `<div class="error-msg">`.

```php
<?= error('email', $validator) ?>
```

### `has_error($field, $validator)`

Useful for conditional styling:

```php
<div class="<?= error_class('email', $validator) ?>">
```

### `error_class($field, $validator)`

Returns `"error"` if the field has validation errors.

### `validator()`

Returns the Validator instance from the container:

```php
$validator = validator();
```

### `is_post()`

Detects if the request method is POST.

---

## 🛡️ CSRF Protection

CSRF is enforced automatically for:

* POST
* PUT
* DELETE

unless an `Authorization` header exists.

Add the token to your form:

```php
<form method="post">
    <input type="hidden" name="_csrf" value="<?= csrf()->generate() ?>">
</form>
```

Invalid tokens immediately trigger a 400 response before controller execution.

---

## 🔍 Internals

The plugin automatically:

* Registers built-in validator rules via the container
* Hooks CSRF validation into `Event::CONTROLLER_CALLING`
* Extends the guard with a CSRF service
* Provides global view helpers for forms
* Uses `param()` to manage form memory state

All without configuration.

---

## 📁 Requirements

* `naf/framework` ≥ 0.1.0
* `naf/session` ≥ 0.1.0 (required for CSRF + memory)

---

## 📄 License

MIT License.