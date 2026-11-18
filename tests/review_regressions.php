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

function expect_review_result($actual, $expected, $description) {
	if ($actual !== $expected) {
		throw new RuntimeException($description . ': expected ' . var_export($expected, true)
			. ', got ' . var_export($actual, true));
	}
}

$first = new BBCode();
$second = new BBCode();
$first->SetDetectURLs(true);
expect_review_result($first !== $second, true, 'Independent parser instances');
expect_review_result($second->GetDetectURLs(), false, 'Independent parser configuration');
expect_review_result(BBCode::getInstance() === BBCode::getInstance(), true, 'Shared parser accessor');

$limited = new BBCode();
$limited->SetLimitPrecision(0);
$limited->SetLimit(12);
expect_review_result($limited->Parse('[code][b]abc[/b]'), '[code]<b>abc</b>', 'Unclosed code tag with a limit');
expect_review_result($limited->WasLimited(), false, 'Unclosed code tag should fit within the limit');

$expected_quote = "\n<div class=\"bbcode_quote\">\n"
	. "<div class=\"bbcode_quote_head\">A [B] wrote:</div>\n"
	. "<div class=\"bbcode_quote_body\">text</div>\n"
	. "</div>\n";
foreach (array('[quote="A [B]"]text[/quote]', "[quote='A [B]']text[/quote]") as $input) {
	expect_review_result($second->Parse($input), $expected_quote, 'Quoted attribute containing a bracket');
}

echo "Three post-merge review regressions passed.\n";
