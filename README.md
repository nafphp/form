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

## Documentation

**[Forms and validation →](https://nafphp.github.io/docs/forms/)**

Everything about this package — what it does, how it is configured and what it needs — lives
in the [NAF documentation](https://nafphp.github.io/docs/). Not sure which packages you need?
[Start here](https://nafphp.github.io/docs/choosing-packages/).

## Install

```bash
composer require naf/form
```

## License

MIT. Part of [NAF](https://github.com/nafphp/framework).


## Behavior notes

The `date` rule rejects malformed values, including NUL bytes, as validation failures.

csrf()->token() returns a stable per-session token; generate() explicitly rotates it. Unsafe methods including PATCH require a form token or X-CSRF-Token, even with an Authorization header. Explicit named-route exceptions remain available. Missing, malformed or oversized tokens return the established 400 response. Default rules now validate supported scalar/container types and strict calendar dates; required accepts numeric zero and false.

## PHP code style

Source, tests and PHP templates follow the shared [NAF code style](https://github.com/nafphp/docs/blob/main/CODE_STYLE.md)
(PER Coding Style 3.0 with the Nafinity readability rules). After `composer install`, run
`composer style:check` to verify formatting or `composer style:fix` to apply it. The formatter
is a development dependency. Review template output and run the package checks after changes.
