<?php
/**
 * License Codes
 */

$default_args = [
	'license_code' => [
		'product_key'  => 'unknown',
		'product_name' => 'Unknown',
		'code'         => '',
		'status'       => '',
		'expire_date'  => '',
		'type'         => 'normal',
	],
];

// @phpstan-ignore-next-line
$args = \wp_parse_args( $args, $default_args );

[
	'license_code' => $license_code,
	'key'          => $key,
] = $args;

[
	'product_key'  => $product_key,
	'product_name' => $product_name,
	'code'         => $code,
	'status'       => $license_status,
	'expire_date' => $expire_date,
	'type'         => $license_type,
] = $license_code;

$deactivate_button = '<sl-button type="submit" name="submit_button" value="deactivate" variant="default" size="small" class="w-full mt-4">棄用授權</sl-button>';


$activate_button = '
<sl-button-group label="Alignment" class="mt-4 w-full">
	<sl-input class="w-full" placeholder="請輸入授權碼 xxxxxx-xxxxxx-xxxxxx-xxxxxx" size="small" name="code" clearable></sl-input>
	<sl-button type="submit" name="submit_button" value="activate" variant="primary" size="small">啟用</sl-button>
</sl-button-group>
';


printf(
/*html*/'
<form method="post" action="">
	<input type="hidden" name="product_key" value="%1$s">
	<input type="hidden" name="code" value="%2$s">
	%3$s
	%4$s
</form>
',
$product_key,
$code,
\wp_nonce_field("{$key}_action", "{$key}_nonce", true, false),
$code ? $deactivate_button : $activate_button,
);
