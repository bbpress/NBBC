#!/bin/sh
set -eu

cd "$(dirname "$0")/.."

nbbc_generated=$(mktemp)
nbbc_source_output=$(mktemp)
nbbc_source_log=$(mktemp)
nbbc_single_output=$(mktemp)
nbbc_single_log=$(mktemp)
trap 'rm -f "$nbbc_generated" "$nbbc_source_output" "$nbbc_source_log" "$nbbc_single_output" "$nbbc_single_log"' EXIT

(cd tools && perl merge.pl) > "$nbbc_generated"
cmp "$nbbc_generated" nbbc.php

if ! php test_nbbc.php > "$nbbc_source_output" 2> "$nbbc_source_log"; then
	cat "$nbbc_source_log"
	sed -n '/Test Results:/p' "$nbbc_source_output"
	exit 1
fi

if ! NBBC_TEST_BUILD=single php test_nbbc.php > "$nbbc_single_output" 2> "$nbbc_single_log"; then
	cat "$nbbc_single_log"
	sed -n '/Test Results:/p' "$nbbc_single_output"
	exit 1
fi

cmp "$nbbc_source_log" "$nbbc_single_log"
sed -n '/Test Results:/p' "$nbbc_source_output"
sed -n '/Test Results:/p' "$nbbc_single_output"
cat "$nbbc_source_log"

php tests/lexer_failure.php
NBBC_TEST_BUILD=single php tests/lexer_failure.php

php tests/review_regressions.php
NBBC_TEST_BUILD=single php tests/review_regressions.php

php tests/static_analysis_regressions.php
NBBC_TEST_BUILD=single php tests/static_analysis_regressions.php
