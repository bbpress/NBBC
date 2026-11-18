# NBBC

This is bbPress's fork of Sean Werkema's New BBCode Parser. It converts BBCode to HTML and is used by bbPress forum importers. This fork targets PHP 7.4 and later.

## Use

Only the generated `nbbc.php` file is needed at runtime:

```php
require_once __DIR__ . '/nbbc.php';

echo BBCode::getInstance()->Parse('[b]Hello[/b]');
```

`BBCode::getInstance()` returns the shared parser used by bbPress importers. Use `new BBCode()` when you need an independently configured parser. Configuration on either instance is retained for subsequent calls to that instance.

## Develop

Edit the files in `src/`, then regenerate `nbbc.php`:

```sh
cd tools
perl merge.pl > ../nbbc.php
```

Run the conformance suite against both source and generated builds:

```sh
sh tests/run.sh
```

Install the development dependencies and run PHPStan against `src/` with PHP 7.4 as the target version:

```sh
composer install
composer analyse
```

The generated `nbbc.php` is checked against `src/` by the conformance suite, so PHPStan analyzes the source files only. Composer and PHPStan are development tools; the single-file runtime does not require them.

The suite checks that the generated file matches the source, both builds produce identical output, and lexer failures are reported clearly. It requires PHP, Perl, and the included `smileys/` fixtures. The HTML test page remains available as `test_nbbc.php`.

The [original manual](readme.html) documents the parser's tags and configuration methods. Its `new BBCode` examples remain valid, but its old PHP requirements do not apply. The [license](LICENSE) applies to both source and generated files.
