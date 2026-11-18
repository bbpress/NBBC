<?php

if (getenv('NBBC_TEST_BUILD') === 'single') {
	require_once __DIR__ . '/../nbbc.php';
} else {
	require_once __DIR__ . '/../src/nbbc_main.php';
}

ini_set('pcre.jit', '0');
ini_set('pcre.backtrack_limit', '1');

try {
	new BBCodeLexer('[b]hello[/b]');
} catch (RuntimeException $error) {
	if (strpos($error->getMessage(), 'Unable to lex BBCode: ') === 0) {
		echo "PCRE failure reported by lexer.\n";
		exit(0);
	}
	throw $error;
}

throw new RuntimeException('Expected a PCRE failure from the lexer.');
