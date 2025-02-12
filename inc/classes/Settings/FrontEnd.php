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
	 * 建構子
	 */
	public function __construct() {
		\add_action('admin_post_save_powerhouse_settings', [ $this, 'handle_save' ]);
	}

	/**
	 * Render Powerhouse Page Callback
	 */
	public static function powerhouse_settings_page_callback(): void {
		$key = DTO::SETTINGS_KEY;
		// 從 query string 獲取儲存狀態
		$is_saved = isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true';

		printf(
		/*html*/'
		<div id="powerhouse-settings" class="tailwind">
			<form id="powerhouse-settings-form" class="pr-5 mt-8" method="post" action="%4$s">
				<sl-alert class="mb-8" variant="success" %1$s>
					<sl-icon slot="icon" name="check2-circle"></sl-icon>
					儲存成功
				</sl-alert>
				%2$s
				<input type="hidden" name="action" value="save_powerhouse_settings">
				%3$s
				<sl-button class="mt-12" type="submit" variant="primary" name="submit_button" value="1">儲存</sl-button>
			</form>
		</div>
		',
			$is_saved ? 'open' : '',
			wp_nonce_field("{$key}_action", "{$key}_nonce", true, false),
			Plugin::safe_get('settings', null, false),
			admin_url('admin-post.php')
		);
	}

	/**
	 * 儲存表單
	 *
	 * @return void
	 */
	public function handle_save(): void {
		$option_name = DTO::SETTINGS_KEY;

		// 驗證 nonce
		if (!isset($_POST[ "{$option_name}_nonce" ]) || !\wp_verify_nonce(
			(string) $_POST[ "{$option_name}_nonce" ], // phpcs:ignore
			"{$option_name}_action"
		)) {
			\wp_die('安全檢查失敗');
		}

		// phpcs:disable
		// 檢查是否提交了表單
		if (($_POST['submit_button'] ?? '') !== '1') {
			return;
		}

		$data = WP::sanitize_text_field_deep($_POST[$option_name] ?? [
			$option_name => []
		], false);

		$dto_array = DTO::instance()->to_array();
		$allowed_keys = array_keys($dto_array);


		$formatted_data = [];
		foreach($allowed_keys as $key){
			$formatted_data[$key] = $data[$key] ?? 'no';
		}

		$update_success = \update_option($option_name, $formatted_data);

		// 重定向回設定頁面
		\wp_redirect(
			\add_query_arg(
				'settings-updated',
				$update_success ? 'true' : 'false',
				\wp_get_referer()
			)
		);
		exit;
		// phpcs:enable
	}
}
