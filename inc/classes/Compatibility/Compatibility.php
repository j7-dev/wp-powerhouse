<?php

declare (strict_types = 1);

namespace J7\Powerhouse\Compatibility;

use J7\Powerhouse\Plugin;
use J7\Powerhouse\Domains\Limit\Utils\CreateTable;

/**
 * Compatibility 不同版本間的相容性設定
 *
 * @since v2.0.17
 */
final class Compatibility {
	use \J7\WpUtils\Traits\SingletonTrait;

	const AS_COMPATIBILITY_ACTION = 'powerhouse_compatibility_action_scheduler';

	/**
	 * Constructor
	 */
	public function __construct() {
		$scheduled_version = \get_option('powerhouse_compatibility_action_scheduled');
		if ($scheduled_version === Plugin::$version) {
			return;
		}

		Loader::instance();
		ApiOptimize::instance();

		// 排程只執行一次的兼容設定
		\add_action( 'init', [ __CLASS__, 'compatibility_action_scheduler' ] );
		\add_action( self::AS_COMPATIBILITY_ACTION, [ __CLASS__, 'compatibility' ]);
	}


	/**
	 * 排程只執行一次的兼容設定
	 *
	 * @return void
	 */
	public static function compatibility_action_scheduler(): void {
		\as_enqueue_async_action( self::AS_COMPATIBILITY_ACTION, [] );
	}

	/**
	 * 執行排程
	 *
	 * @return void
	 */
	public static function compatibility(): void {

		/**
		 * ============== START 相容性代碼 ==============
		 */

		// 判斷是否已經有 wp_ph_access_itemmeta 這張 table，沒有就建立
		CreateTable::create_itemmeta_table();

		/**
		 * ============== END 相容性代碼 ==============
		 */

		// ❗不要刪除此行，註記已經執行過相容設定
		\flush_rewrite_rules();
		\update_option('powerhouse_compatibility_action_scheduled', Plugin::$version);
	}
}
