<?php
/**
 * Checkout Page Settings
 * array{fields: array<string>} $args
 */

use J7\Powerhouse\Plugin;
use J7\Powerhouse\Settings;

$fields = $args['fields'] ?? [];

$field_args = [
	'label'       => '使用非同步方式寄送 Email，加快結帳速度',
	'description' => sprintf(
	/*html*/'可以前往 <a href="%1$s" target="_blank">Scheduled Actions</a> 查看信件寄送的狀況',
	\admin_url('admin.php?page=wc-status&tab=action-scheduler&s=powerhouse_delay_email&action=-1&paged=1&action2=-1')
	),
	'name'        => $fields[0],
	'value'       => Settings::get($fields[0]),
];

Plugin::safe_get(
	'typography\title',
	[
		'value' => '結帳優化',
	]
	);

printf(
	/*html*/'
	<div class="grid grid-cols-[20rem_1fr] gap-4">
		<div>
			<p class="text-sm text-gray-800 font-bold mt-0 mb-2">%1$s</p>
			<p class="text-xs text-gray-400 mt-0 mb-2">%2$s</p>
		</div>
		<div>
			<sl-switch class="block" name="%3$s" value="yes" %4$s></sl-switch>
		</div>
	</div>',
$field_args['label'],
$field_args['description'],
$field_args['name'],
\checked($field_args['value'], 'yes', false),
);
