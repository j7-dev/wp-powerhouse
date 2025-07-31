<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Register\Core;

use J7\Powerhouse\Compatibility\Services\EmailValidator;

/**
 * 註冊過濾器
 * 註冊前驗證用戶的 Email 網域是否設置郵件伺服器，如果沒有設置則不允許註冊
 */
final class Filter {
	use \J7\WpUtils\Traits\SingletonTrait;

	/** Constructor */
	private function __construct() {

		if (\class_exists('\J7\Powerhouse\MU\EmailValidator')) {
			return;
		}

		// 1. 檢查 mu-plugins 目錄下是否有 powerhouse-email-validator.php 檔案
		$powerhouse_email_validator_file = \wp_normalize_path(WPMU_PLUGIN_DIR . '/powerhouse-email-validator.php');
		if (file_exists($powerhouse_email_validator_file)) {
			// 如果存在，就跳過
			return;
		}

		// 如果不存在就引入 powerhouse-email-validator.php
		require_once EmailValidator::instance()->get_file_path();
	}
}
