<?php

declare(strict_types=1);

namespace J7\Powerhouse\Compatibility;

/**
 * 更新其他外掛時，自動更新 powerhouse
 */
final class AutoUpdate {
	use \J7\WpUtils\Traits\SingletonTrait;

	const AUTO_UPDATE_HOOK = 'powerhouse_auto_update';

	/** @var array<string> 以 power- 開頭的插件 */
	private $power_plugins = [];

	/** @var string 需要自動更新的插件 */
	private $target_plugin = '';

	/** Constructor */
	public function __construct() {
		$this->set_plugins();
		\add_action( 'upgrader_process_complete', [ $this, 'auto_update' ], 10, 2 );
		\add_action( self::AUTO_UPDATE_HOOK, [ $this, 'process_auto_update' ] );
	}

	/**
	 * 設定需要自動更新的插件
	 * 測試時用 all in one + classic editor 測試
	 *
	 * @return void
	 */
	private function set_plugins(): void {
		$env = \wp_get_environment_type();
		if ( $env === 'local' ) {
			// 本地測試用
			$this->target_plugin = 'classic-editor/classic-editor.php';
			$this->power_plugins = [ 'all-in-one-wp-migration/all-in-one-wp-migration.php' ];
			return;
		}

		$this->target_plugin = 'powerhouse/plugin.php';
		$this->power_plugins = self::get_power_plugins();
	}


	/**
	 * 更新其他外掛時，自動更新 powerhouse
	 *
	 * @param \WP_Upgrader              $upgrader 升級器
	 * @param array<string, mixed>|null $hook_extra 鉤子額外參數
	 * @return void
	 * @throws \Exception 如果更新失敗
	 */
	public function auto_update( $upgrader, $hook_extra ) {
		@[ // @phpstan-ignore-line
				'plugins' => $plugins, // 目前更新的外掛
			] = $hook_extra;

		$plugins = is_array( $plugins ) ? $plugins : [];
		if (!$plugins) {
			return;
		}
		$plugin = reset($plugins); // 目前更新的外掛

		// 如果目前更新的外掛屬於 power- 系列
		if ( in_array( $plugin, $this->power_plugins, true ) ) {
			$action_id = \as_schedule_single_action(time() + 10, self::AUTO_UPDATE_HOOK);
			\J7\WpUtils\Classes\WC::log($action_id, '排程自動更新 powerhouse action_id: ');
		}
	}

	/**
	 * 自動更新 powerhouse
	 *
	 * @return void
	 * @throws \Exception 如果更新失敗
	 */
	public function process_auto_update() {
		try {
			// 檢查插件更新
			\wp_update_plugins();

			// 獲取可用的插件更新
			$plugin_updates = \get_site_transient('update_plugins');

			// 確保該插件有更新可用
			if (isset($plugin_updates->response[ $this->target_plugin ])) { // @phpstan-ignore-line
				// 初始化更新器
				require_once ABSPATH . 'wp-admin/includes/admin.php';
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
				$upgrader = new \Plugin_Upgrader();

				// 執行更新
				$result = $upgrader->upgrade($this->target_plugin);

				if (\is_wp_error($result)) {
					throw new \Exception('更新錯誤: ' . $result->get_error_message());
				} else {
					$result = \activate_plugin($this->target_plugin);
					if (\is_wp_error($result)) {
						throw new \Exception('啟用錯誤: ' . $result->get_error_message());
					}
					\J7\WpUtils\Classes\WC::log('自動更新 ' . $this->target_plugin . ' 成功');
				}
			} else {
				throw new \Exception('沒有可用的更新或插件不存在');
			}
		} catch (\Throwable $th) {
			\J7\WpUtils\Classes\WC::log($th->getMessage(), 'AutoUpdate::process_auto_update ');
		}
	}


	/**
	 * 取得所有以 power- 開頭的插件
	 *
	 * @return array<string>
	 */
	private static function get_power_plugins(): array {
		/** @var string[] $active_plugins */
		$active_plugins = \get_option('active_plugins', []);
		$power_plugins  = [];

		foreach ($active_plugins as $active_plugin) {
			if (strpos($active_plugin, 'power-') === 0) {
				$power_plugins[] = $active_plugin;
			}
		}
		return $power_plugins;
	}
}
