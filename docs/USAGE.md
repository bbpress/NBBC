# Using bbPress's NBBC fork

NBBC converts BBCode text to HTML. This fork supports PHP 7.4 and later and keeps `nbbc.php` as the only file needed at runtime.

```php
require_once __DIR__ . '/nbbc.php';

$parser = BBCode::getInstance();
$html = $parser->Parse('[b]Hello[/b]'); // <b>Hello</b>
```

Use `BBCode::getInstance()` for the shared parser used by bbPress importers. Use `new BBCode()` for an independently configured parser. The original HTML manual's `new BBCode` examples remain valid, but its PHP requirements are obsolete.

## Configuration

Settings, added rules, and smileys stay on each parser instance across calls to `Parse()`. The instance returned by `getInstance()` is shared, so set the options your application needs before parsing, especially when several importers share a PHP process.

```php
$parser = BBCode::getInstance();
$parser->SetDetectURLs(true);
$parser->SetIgnoreNewlines(false);
$parser->SetEnableSmileys(false);

$html = $parser->Parse('Read [url=https://example.com]this[/url].');
```

bbPress converters call `Parse()` through this singleton and set its public `smiley_url` and `smiley_dir` properties before importing content. Setting those paths to `false` does not disable smiley parsing; call `SetEnableSmileys(false)` if that is what your application needs. The default rules support common tags such as `[b]`, `[i]`, `[url]`, `[email]`, `[img]`, `[quote]`, `[code]`, and `[list]`, as well as wiki links. See the [historical manual](../readme.html) for the larger tag and rule reference.

bbPress trunk still loads its own bundled `src/includes/admin/parser.php` from the converter callback. Updating this repository alone does not update that copy; it needs a separate bbPress integration change.

## Output compatibility

The conformance suite checks source and generated builds. The first 15 cases in `tests/bbpress_compat.php` mirror [bbPress's admin parser test provider](https://github.com/bbpress/bbPress/blob/trunk/tests/phpunit/testcases/admin/parser.php). Additional cases cover the shared parser and smiley property assignments used by the [converter callback](https://github.com/bbpress/bbPress/blob/trunk/src/includes/admin/classes/class-bbp-converter-base.php). They pin output for links, images, quotes, lists, wiki links, newlines, entities, and smileys.

To compare the same cases with bbPress's bundled parser in a separate PHP process, run `NBBC_TEST_BUILD=legacy NBBC_LEGACY_PARSER=/path/to/bbpress/src/includes/admin/parser.php php tests/bbpress_compat.php`.

Some escaping still depends on PHP's default `htmlspecialchars()` flags. The legacy conformance suite allows the apostrophe difference introduced in PHP 8.1, so matching results within each PHP version does not yet guarantee byte-for-byte identical output across versions. Check imported content on each PHP version you support before changing the escaping rules.

## Development

Edit `src/`, then regenerate `nbbc.php` with `cd tools && perl merge.pl > ../nbbc.php`. Run `sh tests/run.sh` to check that the generated file matches the source and that both builds pass the same tests. Run `composer install` and `composer analyse` for static analysis. CI runs the parser suite on PHP 7.4 through 8.5 and PHPStan against PHP 7.4 as the target version.
