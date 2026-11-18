<?php

// The first 15 cases match bbPress's admin parser PHPUnit data provider.
// The remaining cases exercise the property assignments in its converter callback.

$build = getenv('NBBC_TEST_BUILD');
if ($build === 'legacy') {
	$legacy_parser = getenv('NBBC_LEGACY_PARSER');
	if (!$legacy_parser || !is_file($legacy_parser)) {
		throw new RuntimeException('Set NBBC_LEGACY_PARSER to the bbPress parser.php path.');
	}
	require_once $legacy_parser;
} elseif ($build === 'single') {
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

function expect_compat_result($actual, $expected, $description) {
	if ($actual !== $expected) {
		throw new RuntimeException($description . ': expected ' . var_export($expected, true)
			. ', got ' . var_export($actual, true));
	}
}

$parser_cases = array(
	'bold' => array('[b]Bold[/b]', '<b>Bold</b>'),
	'italic' => array('[i]Italic[/i]', '<i>Italic</i>'),
	'underline' => array('[u]Under[/u]', '<u>Under</u>'),
	'strikethrough' => array('[s]Strike[/s]', '<strike>Strike</strike>'),
	'link' => array('[url=https://example.com]Example[/url]', '<a href="https://example.com" class="bbcode_url">Example</a>'),
	'email' => array('[email]a@example.com[/email]', '<a href="mailto:a@example.com" class="bbcode_email">a@example.com</a>'),
	'image' => array('[img]https://example.com/a.png[/img]', '<img src="https://example.com/a.png" alt="a.png" class="bbcode_img" />'),
	'quote' => array('[quote=Name]Q[/quote]', "\n<div class=\"bbcode_quote\">\n<div class=\"bbcode_quote_head\">Name wrote:</div>\n<div class=\"bbcode_quote_body\">Q</div>\n</div>\n"),
	'size' => array('[size=120]Big[/size]', '<span style="font-size:1.0em">Big</span>'),
	'font' => array('[font=Arial]Text[/font]', '<span style="font-family:\'Arial\'">Text</span>'),
	'list' => array('[list][*]a[*]b[/list]', "\n<ul class=\"bbcode_list\">\n<li>a</li>\n<li>b</li>\n</ul>\n"),
	'numbered list' => array('[list=01][*]a[*]b[/list]', "\n<ol class=\"bbcode_list\" style=\"list-style-type:decimal-leading-zero\">\n<li>a</li>\n<li>b</li>\n</ol>\n"),
	'wiki' => array('[[Page]]', '<a href="/?page=Page" class="bbcode_wiki">Page</a>'),
	'wiki title' => array('[[Page|Title]]', '<a href="/?page=Page" class="bbcode_wiki">Title</a>'),
	'newlines' => array("line1\nline2", "line1<br />\nline2"),
);

$parser = BBCode::getInstance();
foreach ($parser_cases as $name => $case) {
	expect_compat_result($parser->Parse($case[0]), $case[1], $name . ' parser fixture');
}

// Most bbPress converters copy false smiley paths onto the shared parser.
// False paths do not disable smiley parsing, so keep that output compatible.
$parser->AddSmiley(':nbbc-test:', 'nbbc-missing.gif');
$parser->smiley_url = false;
$parser->smiley_dir = false;
expect_compat_result(BBCode::getInstance() === $parser, true, 'Shared converter parser');
expect_compat_result($parser->Parse('[quote=Jane]A [b]bold[/b] reply[/quote]'),
	"\n<div class=\"bbcode_quote\">\n<div class=\"bbcode_quote_head\">Jane wrote:</div>\n<div class=\"bbcode_quote_body\">A <b>bold</b> reply</div>\n</div>\n",
	'Nested converter quote');
expect_compat_result($parser->Parse('A &amp; B'), 'A &amp;amp; B', 'Converter entity escaping');
expect_compat_result($parser->Parse('Hi :nbbc-test:'),
	'Hi <img src="/nbbc-missing.gif" width="" height="" alt=":nbbc-test:" title=":nbbc-test:" class="bbcode_smiley" />',
	'Converter with false smiley paths');

$parser->SetEnableSmileys(false);
expect_compat_result($parser->Parse('Hi :nbbc-test:'), 'Hi :nbbc-test:', 'Explicitly disabled smileys');
$parser->SetEnableSmileys(true);

// The default converter supplies WordPress smiley paths instead.
$parser->smiley_url = 'https://example.test/wp-includes/images/smilies';
$parser->smiley_dir = '/__nbbc_missing_smileys__';
expect_compat_result(BBCode::getInstance()->smiley_url, $parser->smiley_url,
	'Converter property persists on the shared parser');
expect_compat_result($parser->Parse('Hi :nbbc-test:'),
	'Hi <img src="https://example.test/wp-includes/images/smilies/nbbc-missing.gif" width="" height="" alt=":nbbc-test:" title=":nbbc-test:" class="bbcode_smiley" />',
	'Converter with WordPress smiley URL');

echo count($parser_cases) . " bbPress parser fixtures and converter property checks passed.\n";
