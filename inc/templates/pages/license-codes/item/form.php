<?php
/**
 * License Codes
 */

$default_license_code = [
	'product_slug'    => 'unknown',
	'product_name'    => 'Unknown',
	'code'            => '',
	'post_status'     => '',
	'expire_date'     => '',
	'is_subscription' => false,
	'link'            => '',
];

$default_args = [
	'license_code' => $default_license_code,
];

// @phpstan-ignore-next-line
$args = \wp_parse_args( $args, $default_args );

[
	'license_code' => $license_code,
	'key'          => $key,
] = $args;

$license_code = \wp_parse_args( $license_code, $default_license_code );

[
	'product_slug'    => $product_slug,
	'product_name'    => $product_name,
	'code'            => $code,
	'post_status'     => $license_status,
	'expire_date'     => $expire_date,
	'is_subscription' => $is_subscription,
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
	<input type="hidden" name="product_slug" value="%1$s">
	<input type="hidden" name="code" value="%2$s">
	%3$s
	%4$s
</form>
',
$product_slug,
$code,
\wp_nonce_field("{$key}_action", "{$key}_nonce", true, false),
$code ? $deactivate_button : $activate_button,
);
