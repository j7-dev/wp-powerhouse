<?php
/**
 * Account Page Settings
 * array{fields: array<string>} $args
 */

use J7\Powerhouse\Plugin;
use J7\Powerhouse\Settings;

$fields = $args['fields'] ?? [];

$field_args = [
	'label'       => '使姓氏欄位為非必填',
	'description' => '啟用後，不再強制要求用戶必須填寫姓氏',
	'name'        => $fields[1],
	'value'       => Settings::get($fields[1]),
];

Plugin::get(
	'typography\title',
	[
		'value' => 'My Account 帳號欄位優化',
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
