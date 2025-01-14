<?php
/**
 * Settings Page
 * array{fields: array<string>} $args
 */

use J7\Powerhouse\Plugin;

Plugin::safe_get(
	'settings/checkout',
	$args // @phpstan-ignore-line
);

Plugin::safe_get(
	'settings/account',
	$args // @phpstan-ignore-line
);
