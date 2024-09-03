<?php
/**
 * License Codes
 */

use J7\Powerhouse\Plugin;

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

$args = \wp_parse_args( $args, $default_args );

[
	'license_code' => $license_code,
] = $args;

[
	'product_key'  => $product_key,
	'product_name' => $product_name,
	'code'         => $code,
	'status'       => $license_status,
	'expire_date' => $expire_date,
	'type'         => $license_type,
] = $license_code;

$display_expire_date = match ( $expire_date ) {
	0    => '無限期',
	''     => '',
	default => \wp_date( 'Y-m-d', $expire_date ),
};

$status_label = match ( $license_status ) {
	'available'    => '可用',
	'activated'    => '已啟用',
	'deactivated'  => '已停用',
	'expired'      => '已過期',
	default        => '未啟用',
};

$status_color = match ( $license_status ) {
	'available'    => 'primary',
	'activated'    => 'success',
	'deactivated'  => 'neutral',
	'expired'      => 'danger',
	default        => 'neutral',
};

$display_license_type = match ( $license_type ) {
	'subscription' => '訂閱',
	'normal'       => '一次性',
	default        => '',
};

printf(
/*html*/'
<div class="bg-white p-4 rounded-lg hover:shadow-md transition-all duration-300">
	<h2 class="text-lg font-bold mt-0 mb-2">%1$s 授權</h2>
	<div class="grid grid-cols-[10rem_1fr] text-sm text-gray-500 text-mono [&>div]:h-6">
		<div>狀態</div>
		<div class="text-right">
			<sl-tag variant="%2$s" size="small">%3$s</sl-tag>
		</div>
		<div>授權種類</div>
		<div class="text-right">%4$s</div>
		<div>到期日</div>
		<div class="text-right">%5$s</div>
		<div>授權碼</div>
		<div class="text-right">%6$s</div>
	</div>
	%7$s
</div>
',
$product_name,
$status_color,
$status_label,
$display_license_type,
$display_expire_date,
$code,
Plugin::safe_get(
	'license-codes/item/form',
	$args,
	false
)
);
