<?php
/**
 * Bootstrap
 */

declare (strict_types = 1);

namespace J7\Powerhouse;

use J7\WpUtils\Classes\General;

if ( class_exists( 'J7\Powerhouse\Bootstrap' ) ) {
	return;
}
/**
 * Class Bootstrap
 */
final class Bootstrap {
	use \J7\WpUtils\Traits\SingletonTrait;

	const LC_MENU_SLUG = 'powerhouse-license-codes';

	/**
	 * Constructor
	 */
	public function __construct() {
		Compatibility\Compatibility::instance();

		Settings::instance();
		// Admin\Entry::instance();
		Admin\Debug::instance();
		Admin\Account::instance();
		// Admin\OrderDetail::instance();
		Admin\OrderList::instance();
		Admin\DelayEmail::instance();
		Api\Base::instance();
		Api\LC::instance();
		Resources\Loader::instance();
		Theme\FrontEnd::instance();

		\add_action( 'init', [ __CLASS__, 'register_assets' ] );

		\add_action( 'admin_menu', [ __CLASS__ , 'add_menu' ], 10 );
		\add_action( 'admin_menu', [ __CLASS__ , 'add_submenu' ], 100 );

		\add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );

		\add_filter( 'body_class', [ __CLASS__, 'add_tailwind_class' ] );
		\add_filter( 'admin_body_class', [ __CLASS__, 'add_tailwind_class_admin' ] );

		\add_action( 'plugins_loaded', [ __CLASS__ , 'check_lc_array' ], 999 );
	}

	/**
	 * Register assets
	 *
	 * @return void
	 */
	public static function register_assets(): void {
		\wp_register_style( 'shoelace', 'https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.18.0/cdn/themes/light.css', [], '2.18.0' );
		\wp_register_script( 'shoelace', 'https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.18.0/cdn/shoelace-autoloader.js', [], '2.18.0', false );
	}

	/**
	 * Add Power Plugin Menu
	 */
	public static function add_menu(): void {
		\add_menu_page(
		__( 'Powerhouse', 'powerhouse' ),
		__( 'Powerhouse', 'powerhouse' ),
		'manage_options',
		'powerhouse',
		[ Settings::class, 'powerhouse_settings_page_callback' ],
		'dashicons-superhero',
		3
		);
	}

	/**
	 * Add submenu page
	 *
	 * @return void
	 */
	public static function add_submenu(): void {
		\add_submenu_page( 'powerhouse', __( '設定', 'powerhouse' ), __( '設定', 'powerhouse' ), 'manage_options', 'powerhouse-settings', [ Settings::class, 'powerhouse_settings_page_callback' ] );

		// 如果沒有註冊產品資訊，就不用顯示授權碼
		$product_infos = \apply_filters( 'powerhouse_product_infos', [] );
		if (!$product_infos) {
			return;
		}
		\add_submenu_page( 'powerhouse', __( '授權碼', 'powerhouse' ), __( '授權碼', 'powerhouse' ), 'manage_options', self::LC_MENU_SLUG, [ LC::class, 'powerhouse_license_codes_page_callback' ] );
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public static function enqueue_assets(): void {
		if (\method_exists(General::class, 'in_url')) {
			if (!General::in_url(
			[
				'admin.php?page=powerhouse',
			]
			)) {
				return;
			}
		}

		\wp_enqueue_style( Plugin::$snake, Plugin::$url . '/inc/assets/dist/css/index.css', [], Plugin::$version );

		$admin_handle = Plugin::$kebab . '-admin';
		\wp_enqueue_script(
		$admin_handle,
		Plugin::$url . '/inc/assets/dist/admin.js',
		[ 'jquery' ],
		Plugin::$version,
		true
		);

		// CDN shoelace
		\wp_enqueue_style( 'shoelace' );
		\wp_enqueue_script( 'shoelace' );

		if (\method_exists(Plugin::class, 'add_module_handle')) {
			Plugin::instance()->add_module_handle($admin_handle, 'defer');
			Plugin::instance()->add_module_handle('shoelace', '');
		}
	}

	/**
	 * 為前台 body 添加 tailwind class
	 *
	 * @param array<string> $classes 現有的 body classes
	 * @return array<string> 修改後的 body classes
	 */
	public static function add_tailwind_class( array $classes ): array {
		$classes[] = 'tailwind';
		return $classes;
	}

	/**
	 * 為後台 body 添加 tailwind class
	 *
	 * @param string $classes 現有的 admin body classes
	 * @return string 修改後的 admin body classes
	 */
	public static function add_tailwind_class_admin( $classes ) {
		return $classes . ' tailwind';
	}

	/**
	 * Check LC array
	 *
	 * @return void
	 */
	public static function check_lc_array(): void {
		$lc_array = LC::get_lc_array();
	}
}
