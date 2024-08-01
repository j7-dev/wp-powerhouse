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
		require_once __DIR__ . '/admin/index.php';

		\add_action( 'admin_menu', [ __CLASS__ , 'add_power_plugin_menu' ], 10 );
		\add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
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

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public static function enqueue_assets(): void {
		\wp_enqueue_script(
			Plugin::$kebab,
			Plugin::$url . '/inc/assets/dist/index.js',
			[ 'jquery' ],
			Plugin::$version,
			[
				'strategy' => 'defer',
			]
		);

		\wp_enqueue_style( Plugin::$snake, Plugin::$url . '/inc/assets/dist/css/index.css', [], Plugin::$version );
	}
}
