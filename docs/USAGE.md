# Using bbPress's NBBC fork

NBBC converts BBCode text to HTML. This fork supports PHP 7.4 and later. `nbbc.php` is the only required PHP file at runtime; smiley images are optional assets. Smileys are enabled by default, so a deployment that renders them must also serve the image files or configure another smiley directory and URL.

```php
require_once __DIR__ . '/nbbc.php';

$parser = BBCode::getInstance();
$html = $parser->Parse('[b]Hello[/b]'); // <b>Hello</b>
```

This example is for standalone use outside bbPress. `BBCode::getInstance()` returns a parser shared within that PHP process; use `new BBCode()` for an independently configured parser. The original HTML manual's `new BBCode` examples remain valid, but its PHP requirements are obsolete.

## Configuration

Settings, added rules, and smileys stay on each parser instance across calls to `Parse()`. The instance returned by `getInstance()` is shared, so set the options your application needs before parsing, especially when several importers share a PHP process.

```php
$parser = BBCode::getInstance();
$parser->SetDetectURLs(true);
$parser->SetIgnoreNewlines(false);
$parser->SetEnableSmileys(false);

$html = $parser->Parse('Read [url=https://example.com]this[/url].');
```

To render smileys, serve the optional `smileys/` files and configure both the filesystem directory used to read image dimensions and the URL used in HTML output:

```php
$parser->SetSmileyDir(__DIR__ . '/smileys');
$parser->SetSmileyURL('https://example.com/smileys');
```

The defaults for both settings are `smileys`. If only `nbbc.php` is deployed, call `SetEnableSmileys(false)` as shown above. Setting the paths to `false` does not disable smiley parsing.

bbPress intentionally bundles its own copy at `src/includes/admin/parser.php`. Its converter callback loads that file and copies `$bbcode_parser_properties`, including `smiley_url` and `smiley_dir`, onto the parser singleton before calling `Parse()`. Configure that property in a converter when needed. Both parser files declare the same global classes, so do not also require this fork's `nbbc.php` in the converter process. Changes to this fork reach bbPress when its bundled copy is updated separately.

The default rules support common tags such as `[b]`, `[i]`, `[url]`, `[email]`, `[img]`, `[quote]`, `[code]`, and `[list]`, as well as wiki links. See the [historical manual](../readme.html) for the larger tag and rule reference.

## Output compatibility

The conformance suite checks source and generated builds. The first 15 cases in `tests/bbpress_compat.php` mirror [bbPress's admin parser test provider](https://github.com/bbpress/bbPress/blob/trunk/tests/phpunit/testcases/admin/parser.php). Additional cases cover the shared parser and smiley property assignments used by the [converter callback](https://github.com/bbpress/bbPress/blob/trunk/src/includes/admin/classes/class-bbp-converter-base.php). They pin output for links, images, quotes, lists, wiki links, newlines, entities, and smileys.

To compare the same cases with bbPress's bundled parser in a separate PHP process, run `NBBC_TEST_BUILD=legacy NBBC_LEGACY_PARSER=/path/to/bbpress/src/includes/admin/parser.php php tests/bbpress_compat.php`.

Some escaping still depends on PHP's default `htmlspecialchars()` flags. The legacy conformance suite allows the apostrophe difference introduced in PHP 8.1, so matching results within each PHP version does not yet guarantee byte-for-byte identical output across versions. Check imported content on each PHP version you support before changing the escaping rules.

## Development

Edit `src/`, then regenerate `nbbc.php` with `cd tools && perl merge.pl > ../nbbc.php`. Run `sh tests/run.sh` to check that the generated file matches the source and that both builds pass the same tests. Run `composer install` and `composer analyse` for static analysis. CI runs the parser suite on PHP 7.4 through 8.6 and PHPStan against PHP 7.4 as the target version.
