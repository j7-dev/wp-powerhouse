<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Register\Core;

use J7\Powerhouse\Settings\Model\Settings;

/**
 * 註冊過濾器
 * 註冊前驗證用戶的 Email 網域是否設置郵件伺服器，如果沒有設置則不允許註冊
 */
final class Filter {
	use \J7\WpUtils\Traits\SingletonTrait;

	/** Constructor */
	private function __construct() {
		$settings = Settings::instance();
		if ($settings->enable_email_domain_check_register !== 'yes') {
			return;
		}

		/** @category [前台]  WordPress 標準註冊（如 wp-login.php?action=register） */
		\add_filter('registration_errors', [ $this, 'validate_email_domain' ], 10, 3);

		/** @category [前台] WooCommerce My Account 註冊 */
		\add_filter('woocommerce_registration_errors', [ $this, 'validate_email_domain' ], 10, 3);

		/** @category [前台] 後台建立用戶時的驗證，後台創建用戶先不阻擋 */
		// \add_action('user_profile_update_errors', [ $this, 'check_admin_email_domain' ], 10, 3);
	}

	/**
	 * 檢查後台建立用戶時的 Email 網域
	 *
	 * @param \WP_Error $errors WP_Error 錯誤
	 * @param bool      $update 是否為更新操作
	 * @param \stdClass $user 用戶物件
	 */
	public function check_admin_email_domain( \WP_Error $errors, bool $update, \stdClass $user ): void {
		// 只在建立新用戶時檢查，不在更新時檢查
		if ( $update ) {
			return;
		}

		if ( isset( $user->user_email ) ) {
			$this->validate_email_domain( $errors, $user->user_login, $user->user_email );
		}
	}

	/**
	 * 通用的 Email 網域驗證邏輯
	 *
	 * @param \WP_Error $errors WP_Error 錯誤
	 * @param string    $sanitized_user_login 用戶名稱
	 * @param string    $user_email 用戶 Email
	 * @return \WP_Error WP_Error 錯誤
	 */
	public function validate_email_domain( \WP_Error $errors, string $sanitized_user_login, string $user_email ): \WP_Error {
		$settings          = Settings::instance();
		$whitelist_domains = $settings->email_domain_check_white_list;
		$whitelist_domains = \array_map( 'strtolower', $whitelist_domains );

		// 解析 Email 網域
		$email_parts = \explode('@', \strtolower($user_email));
		if (\count($email_parts) !== 2) {
			$errors->add('invalid_email', __('無效的 Email 格式'));
			return $errors;
		}

		$domain = $email_parts[1];

		// 白名單檢查
		if (\in_array($domain, $whitelist_domains, true)) {
			return $errors; // 跳過檢查
		}

		// 檢查 MX 記錄
		if (!checkdnsrr($domain, 'MX')) {
			$errors->add('invalid_email_domain', __('該 Email 網域未設置郵件伺服器，請使用有效的郵件網域'));
		}

		return $errors;
	}
}
