<?php
/**
 * Bootstrap
 */

declare (strict_types = 1);

namespace J7\Powerhouse;

if ( class_exists( 'J7\Powerhouse\Bootstrap' ) ) {
	return;
}
/**
 * Class Bootstrap
 */
final class Bootstrap {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * @var array
	 * Store instances of classes
	 */
	public $instances = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->instances['Admin\OrderDetail'] = Admin\OrderDetail::instance();
		$this->instances['Admin\OrderList']   = Admin\OrderList::instance();

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
