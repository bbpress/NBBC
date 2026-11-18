<?php

if (getenv('NBBC_TEST_BUILD') === 'single') {
	require_once __DIR__ . '/../nbbc.php';
} else {
	require_once __DIR__ . '/../src/nbbc_main.php';
}

error_reporting(E_ALL);
set_error_handler(function ($severity, $message) {
	if (error_reporting() & $severity) {
		throw new ErrorException($message, 0, $severity);
	}
	return false;
});

$parser = new BBCode();
$parser->debug = true;
ob_start();
$smiley_output = $parser->Parse(':)');
$debug_output = ob_get_clean();
if (strpos($smiley_output, 'class="bbcode_smiley"') === false) {
	throw new RuntimeException('Smiley parsing failed.');
}
// The generated build omits debug output, but the source build retains it.
if (getenv('NBBC_TEST_BUILD') !== 'single'
	&& strpos($debug_output, 'Internal_RebuildSmileys:') === false) {
	throw new RuntimeException('Smiley debug output was not generated.');
}

$library = new BBCodeLibrary();
$list_output = $library->DoList($parser, BBCODE_OUTPUT, 'list', 'unknown', array(), 'item');
if ($list_output !== "\n<ul class=\"bbcode_list\">\nitem</ul>\n") {
	throw new RuntimeException('An unsupported list style did not use the default list element.');
}

echo "Static analysis regressions passed.\n";
