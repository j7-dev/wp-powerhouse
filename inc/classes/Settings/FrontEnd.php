<?php

declare (strict_types = 1);

namespace J7\Powerhouse\Settings;

use J7\Powerhouse\Plugin;
use J7\WpUtils\Classes\WP;


if ( class_exists( 'J7\Powerhouse\Settings\FrontEnd' ) ) {
	return;
}
/**
 * Class FrontEnd
 */
final class FrontEnd {
	use \J7\WpUtils\Traits\SingletonTrait;


	/**
	 * @var array<string, mixed>
	 * Store settings
	 */
	public static $settings = [];

	/**
	 * Render Powerhouse Page Callback
	 */
	public static function powerhouse_settings_page_callback(): void {
		$key      = DTO::SETTINGS_KEY;
		$is_saved = self::handle_save();

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
		Plugin::safe_get('settings', null, false)
		);
	}


	/**
	 * 儲存表單
	 *
	 * @return bool 是否儲存
	 */
	private static function handle_save(): bool {
		// phpcs:disable
		// 檢查是否提交了表單
		if (($_POST['submit_button'] ?? '') !== '1') {
			return false;
		}

		$key = DTO::SETTINGS_KEY;

		// 驗證 nonce
		if (!isset($_POST[ "{$key}_nonce" ]) || !\wp_verify_nonce((string) $_POST[ "{$key}_nonce" ], "{$key}_action")) {
			\wp_die('安全檢查失敗');
		}


		if(!isset($_POST[$key])){
			return false;
		}

		$data = WP::sanitize_text_field_deep($_POST[$key], false);

		$update_success = \update_option($key, $data);

		return $update_success;
		// phpcs:enable
	}
}
