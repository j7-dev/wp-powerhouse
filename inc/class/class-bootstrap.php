<?php
/**
 * Bootstrap
 */

declare (strict_types = 1);

namespace J7\Powerhouse;

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
			__( 'Powerhouse', 'power_house' ),
			__( 'Powerhouse', 'power_house' ),
			'manage_options',
			'power_house',
			[ __CLASS__, 'power_house_page_callback' ],
			'dashicons-superhero',
			3
		);

		\add_submenu_page( 'power_house', __( '其他', 'power_house' ), __( '其他', 'power_house' ), 'manage_options', 'power_house', [ __CLASS__, 'power_house_page_callback' ], 1000 );
	}

	/**
	 * Render Powerhouse Page Callback
	 */
	public static function power_house_page_callback(): void {
		// Plugin::get('admin');
		echo '';
	}
}
