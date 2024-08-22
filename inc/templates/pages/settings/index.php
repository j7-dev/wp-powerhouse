<?php
/**
 * Settings Page
 * array{fields: array<string>} $args
 */

use J7\Powerhouse\Plugin;

Plugin::get(
	'settings/checkout',
	$args
);

Plugin::get(
	'settings/account',
	$args
);
