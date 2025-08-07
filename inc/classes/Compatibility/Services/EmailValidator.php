<?php

declare(strict_types=1);

namespace J7\Powerhouse\Compatibility\Services;

use J7\Powerhouse\Compatibility\Shared\MuPluginsLoader;

/**
 * EmailValidator
 * 將 powerhouse-api-booster.php 檔案移動到 mu-plugins 目錄下
 * 加快 API 回應速度
 */
final class EmailValidator extends MuPluginsLoader {
	use \J7\WpUtils\Traits\SingletonTrait;

	/** @var string $file_name 要移動的檔案名稱 */
	protected string $file_name = 'powerhouse-email-validator.php';

	/** 取得檔案路徑 指向 inc\classes\Compatibility\mu-plugins\powerhouse-email-validator.php @return string */
	public function get_file_path(): string {
		return \wp_normalize_path("{$this->file_dir}/{$this->file_name}");
	}
}
