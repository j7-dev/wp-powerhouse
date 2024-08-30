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

	/**
	 * Constructor
	 */
	public function __construct() {
		Settings::instance();
		Admin\Account::instance();
		Admin\OrderDetail::instance();
		Admin\OrderList::instance();
		Admin\DelayEmail::instance();

		\add_action( 'admin_menu', [ __CLASS__ , 'add_menu' ], 10 );
		\add_action( 'admin_menu', [ __CLASS__ , 'add_submenu' ], 100 );

		\add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
		\add_filter( 'body_class', [ __CLASS__, 'add_tailwind_class' ] );
		\add_filter( 'admin_body_class', [ __CLASS__, 'add_tailwind_class_admin' ] );

		// TEST
		\add_filter(
			'powerhouse_product_names',
			function ( $names ) {
				return $names + [
					'power-course' => 'Power Course',
				]; }
			);
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
		\add_submenu_page( 'powerhouse', __( '授權碼', 'powerhouse' ), __( '授權碼', 'powerhouse' ), 'manage_options', 'powerhouse-license-codes', [ LicenseCodes::class, 'powerhouse_license_codes_page_callback' ] );
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public static function enqueue_assets(): void {

		// TODO 之後再對 css 做分割 & 按需載入
		\wp_enqueue_style( Plugin::$snake, Plugin::$url . '/inc/assets/dist/css/index.css', [], Plugin::$version );

		if (\method_exists(General::class, 'in_url')) {
			if (!General::in_url(
			[
				'admin.php?page=powerhouse',
			]
			)) {
				return;
			}
		}

		\wp_enqueue_script(
		Plugin::$kebab,
		Plugin::$url . '/inc/assets/dist/index.js',
		[ 'jquery' ],
		Plugin::$version,
		[
			'strategy' => 'defer',
		]
		);

		// CDN shoelace
		// phpcs:disable
		echo '
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.16.0/cdn/themes/light.css" />
		<script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.16.0/cdn/shoelace-autoloader.js"></script>
		';
		// phpcs:enable
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
}
