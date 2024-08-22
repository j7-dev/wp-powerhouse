<?php
/**
 * Settings
 */

declare (strict_types = 1);

namespace J7\Powerhouse;

use J7\Powerhouse\Plugin;


if ( class_exists( 'J7\Powerhouse\Settings' ) ) {
	return;
}
/**
 * Class Settings
 */
final class Settings {
	use \J7\WpUtils\Traits\SingletonTrait;

	const KEY = 'powerhouse_settings';

	/**
	 * @var array
	 * Store settings
	 */
	public static $settings = [];


	/**
	 * 取得設定值
	 *
	 * @param string|null $key 設定值的鍵
	 *
	 * @return mixed 設定值
	 */
	public static function get( ?string $key = null ) {

		$default_value = [
			'delay_email' => 'yes',
		];

		if (!self::$settings) {
			$settings = \get_option(self::KEY, $default_value);
			$settings = \wp_parse_args($settings, $default_value);
		} else {
			$settings = self::$settings;
		}

		if (!$key) {
			return $settings;
		}

		return $settings[ $key ] ?? '';
	}

	/**
	 * Render Powerhouse Page Callback
	 */
	public static function powerhouse_page_callback(): void {
		$key      = self::KEY;
		$fields   = [ 'delay_email', 'last_name_optional' ];
		$is_saved = self::handle_save($fields);

		printf(
		/*html*/'
		<div id="powerhouse-settings" class="tailwind">
			<form id="powerhouse-settings-form" class="pr-5 mt-8" method="post" action="">
				<sl-alert class="mb-8" variant="success" %1$s>
					<sl-icon slot="icon" name="check2-circle"></sl-icon>
					儲存成功
				</sl-alert>
				%2$s
				%3$s
				<sl-button class="mt-12" type="submit" variant="primary" name="submit_button" value="1">儲存</sl-button>
			</form>
		</div>
		',
		$is_saved ? 'open' : '',
		\wp_nonce_field("{$key}_action", "{$key}_nonce", true, false),
		Plugin::get(
			'settings',
			[
				'fields' => $fields,
			],
			false
		)
		);
	}


	/**
	 * 儲存表單
	 *
	 * @param array<string> $fields 表單欄位
	 *
	 * @return bool 是否儲存
	 */
	private static function handle_save( $fields = [] ): bool {
		// phpcs:disable
		// 檢查是否提交了表單
		if (($_POST['submit_button'] ?? '') !== '1' || !$fields) {
			return false;
		}

		$key = self::KEY;

		// 驗證 nonce
		if (!isset($_POST[ "{$key}_nonce" ]) || !\wp_verify_nonce($_POST[ "{$key}_nonce" ], "{$key}_action")) {
			\wp_die('安全檢查失敗');
		}

		// 獲取並清理表單數據
		$data = [];
		foreach ($fields as $field) {
			$data[ $field ] = \sanitize_text_field($_POST[ $field ] ?? '');
		}

		$update_success = \update_option($key, $data);
		if($update_success){
			self::$settings = $data;
		}

		return $update_success;
		// phpcs:enable
	}
}
