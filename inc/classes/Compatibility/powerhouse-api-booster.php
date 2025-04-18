<?php
/**
 * ApiBooster
 * 在特定的 API 路徑下，只載入必要的插件
 */

namespace J7\Powerhouse\MU;

/**
 * ApiBooster
 */
final class ApiBooster {

	/**
	 * Namespaces 只有這幾個 namespace 的 API 請求，才會載入必要的插件
	 *
	 * @var array<string>
	 */
	protected static $namespaces = [
		'/wp-json/power-', // power- 開頭 API
		'/wp-json/v2/powerhouse',
	];

	/**
	 * Constructor.
	 */
	public function __construct() {
		@[ // @phpstan-ignore-line
			'enable_api_booster' => $enable_api_booster,
		] = \get_option('powerhouse_settings');

		if ('yes' !== $enable_api_booster ) {
			return;
		}

		\add_action('muplugins_loaded', [ __CLASS__, 'only_load_required_plugins' ], 100);
	}

	/**
	 * Only Load Required Plugins
	 * 只載入必要的插件
	 *
	 * @return void
	 */
	public static function only_load_required_plugins(): void {
		// 檢查 API 請求是否包含 "/{$namespace}" 字串
		$some_strpos = false;
		foreach (self::$namespaces as $namespace) {
			if (strpos((string) $_SERVER['REQUEST_URI'],  $namespace) !== false) { // phpcs:ignore
				$some_strpos = true;
				break;
			}
		}
		if (!$some_strpos) {
			return;
		}

		// 基本插件
		$base_plugins = [
			'powerhouse/plugin.php',
			'woocommerce/woocommerce.php',
			'woocommerce-subscriptions/woocommerce-subscriptions.php',
		];

		// 檢查是否所有必要的插件都已經載入
		// 取得所有已啟用的插件
		$active_plugins = \get_option('active_plugins', []);
		$active_plugins = is_array($active_plugins) ? $active_plugins : [];

		$required_plugins = [];
		/** @var string[] $active_plugins */
		foreach ($active_plugins as $active_plugin) {
			// 如果以 power- 開頭，或者在 base_plugins 中，則加入 required_plugins
			if (( strpos($active_plugin, 'power-') === 0 ) || in_array($active_plugin, $base_plugins, true)) {
				$required_plugins[] = $active_plugin;
			}
		}

		\add_filter('option_active_plugins', fn () => $required_plugins, 100 );

		// 移除不必要的 WordPress 功能
		$hooks_to_remove = [
			'widgets_init',
			'register_sidebar',
			'wp_register_sidebar_widget',
			'wp_default_scripts',
			'wp_default_styles',
			'admin_bar_init',
			'add_admin_bar_menus',
		];

		foreach ( $hooks_to_remove as $hook ) {
			\add_action(
				$hook,
				function () use ( $hook ) {
					\remove_all_actions($hook);
				},
				-999999
				);
		}
	}
}

new ApiBooster();
