<?php

declare(strict_types=1);

namespace J7\Powerhouse\Compatibility\Services;

use J7\Powerhouse\Compatibility\Shared\MuPluginsLoader;

/**
 * Powerhouse Loader
 * 將 self::FILE_NAME 檔案移動到 mu-plugins 目錄下
 * 提前載入 Powerhouse 的 vendor，確保 Powerhouse 是最先載入的
 */
final class Loader extends MuPluginsLoader {
	use \J7\WpUtils\Traits\SingletonTrait;

	/** @var string $file_name 要移動的檔案名稱 */
	protected string $file_name = 'powerhouse-loader.php';
}
