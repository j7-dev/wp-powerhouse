<?php

declare(strict_types=1);

namespace J7\Powerhouse\Compatibility;

use J7\Powerhouse\Plugin;

/**
 * Powerhouse Loader
 * 將 self::FILE_NAME 檔案移動到 mu-plugins 目錄下
 * 提前載入 Powerhouse 的 vendor，確保 Powerhouse 是最先載入的
 */
final class Loader {
	use \J7\WpUtils\Traits\SingletonTrait;

	const FILE_NAME = 'powerhouse-loader.php';

	/** Constructor */
	public function __construct() {
		\add_action( Compatibility::AS_COMPATIBILITY_ACTION, [ __CLASS__, 'move_file' ]);
	}

	/**
	 * Move File
	 * 負責將 self::FILE_NAME 移動到 mu-plugins 目錄
	 *
	 * @return void
	 * @throws \Exception 如果檔案操作失敗
	 */
	public static function move_file(): void {
		// 取得 mu-plugins 目錄路徑
		$mu_plugins_dir = WPMU_PLUGIN_DIR;

		// 檢查 mu-plugins 目錄是否存在
		if (!is_dir($mu_plugins_dir)) {
			Plugin::logger( "mu-plugins 目錄不存在，嘗試創建 mu-plugins， 路徑: {$mu_plugins_dir}");
			require_once ABSPATH . 'wp-admin/includes/file.php';
			// 創建 mu-plugins 目錄
			global $wp_filesystem;
			if (!\WP_Filesystem()) {
				Plugin::logger( '無法初始化 WP_Filesystem', 'error' );
				return;
			}
			if (!$wp_filesystem->mkdir($mu_plugins_dir, 0755)) {
				Plugin::logger( '無法創建 mu-plugins 目錄', 'error' );
				return;
			} else {
				Plugin::logger( '成功創建 mu-plugins 目錄' );
			}
		}

		// 源文件路徑
		$source_file = __DIR__ . '/' . self::FILE_NAME;
		// 目標文件路徑
		$target_file = $mu_plugins_dir . '/' . self::FILE_NAME;

		try {
			// 檢查源文件是否存在
			if (!file_exists($source_file)) {
				throw new \Exception( 'source_file 源文件不存在' );
			}

			// 如果目標檔案存在，先嘗試刪除
			if (file_exists($target_file)) {
				if (!unlink($target_file)) {
					throw new \Exception('無法刪除現有檔案');
				}
			}

			// 複製新檔案
			if (!copy($source_file, $target_file)) {
				throw new \Exception('檔案複製失敗');
			}

			Plugin::logger(
				'檔案複製成功',
				'debug',
				[
					'source_file' => $source_file,
					'target_file' => $target_file,
				]
				);
		} catch (\Exception $e) {
			Plugin::logger(
				'檔案操作失敗: ' . $e->getMessage(),
				'error',
				[
					'source_file' => $source_file,
					'target_file' => $target_file,
				]
				);
		}
	}
}
