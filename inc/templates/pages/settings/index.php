<?php
/**
 * Settings Page
 * array{fields: array<string>} $args
 */

use J7\Powerhouse\Plugin;

Plugin::safe_get(
	'settings/checkout',
	$args
);

Plugin::safe_get(
	'settings/account',
	$args
);
