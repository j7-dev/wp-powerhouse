<?php
/**
 * License Codes
 */

use J7\Powerhouse\Plugin;



$default_license_code = [
	'product_slug' => 'unknown',
	'product_name' => 'Unknown',
	'code'         => '',
	'status'       => '',
	'expire_date'  => '',
	'type'         => 'normal',
	'link'         => '',
];

$default_args = [
	'license_code' => $default_license_code,
];

// @phpstan-ignore-next-line
$args = \wp_parse_args( $args, $default_args );

[
	'license_code' => $license_code,
] = $args;

$license_code = \wp_parse_args( $license_code, $default_license_code );

[
	'product_slug'  => $product_slug,
	'product_name' => $product_name,
	'code'         => $code,
	'status'       => $license_status,
	'expire_date' => $expire_date,
	'type'         => $license_type,
	'link'         => $buy_link,
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
	<div class="flex justify-between items-center">
		<h2 class="text-lg font-bold mt-0 mb-2">%1$s 授權</h2>
		%2$s
	</div>
	<div class="grid grid-cols-[10rem_1fr] text-sm text-gray-500 text-mono [&>div]:h-6">
		<div>狀態</div>
		<div class="text-right">
			<sl-tag variant="%3$s" size="small">%4$s</sl-tag>
		</div>
		<div>授權種類</div>
		<div class="text-right">%5$s</div>
		<div>到期日</div>
		<div class="text-right">%6$s</div>
		<div>授權碼</div>
		<div class="text-right">%7$s</div>
	</div>
	%8$s
</div>
',
$product_name,
$buy_link ? "<sl-button variant='primary' href='{$buy_link}' target='_blank' size='small' outline>購買授權</sl-button>" : '',
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
