<?php
/**
 * Bootstrap
 */

declare (strict_types = 1);

namespace J7\PowerHouse;

/**
 * Class Bootstrap
 */
final class Bootstrap {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action( 'admin_menu', [ __CLASS__ , 'add_power_plugin_menu' ], 10 );
	}

	/**
	 * Add Power Plugin Menu
	 */
	public static function add_power_plugin_menu(): void {
		\add_menu_page(
			__( 'Power Plugins', 'power_house' ),
			__( 'Power Plugins', 'power_house' ),
			'manage_options',
			'power_plugins_settings',
			[ __CLASS__, 'power_plugins_page_callback' ],
			'dashicons-superhero',
			70
		);

		\add_submenu_page( 'power_plugins_settings', __( '其他', 'power_house' ), __( '其他', 'power_house' ), 'manage_options', 'power_plugins_settings', [ __CLASS__, 'power_plugins_page_callback' ], 1000 );
	}

	/**
	 * Render Power Plugins Page Callback
	 */
	public static function power_plugins_page_callback(): void {
		Plugin::get('admin');
	}
}
