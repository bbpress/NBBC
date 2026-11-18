# NBBC

This is bbPress's fork of Sean Werkema's New BBCode Parser. It converts BBCode to HTML and is used by bbPress forum importers. This fork targets PHP 7.4 and later.

## Use

Only the generated `nbbc.php` file is needed at runtime:

```php
require_once __DIR__ . '/nbbc.php';

echo BBCode::getInstance()->Parse('[b]Hello[/b]');
```

`BBCode::getInstance()` returns a shared parser. Configuration set on that instance is retained for subsequent calls.

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

The suite checks that the generated file matches the source and that both builds produce identical output. It requires PHP, Perl, and the included `smileys/` fixtures. The HTML test page remains available as `test_nbbc.php`.

The [original manual](readme.html) documents the parser's tags and configuration methods. The [license](LICENSE) applies to both source and generated files.
