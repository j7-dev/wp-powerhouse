<?php
/**
 * Bootstrap
 */

declare (strict_types = 1);

namespace J7\Powerhouse;

use J7\WpUtils\Classes\General;
use J7\Powerhouse\Utils\Base;
use Kucrut\Vite;

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

		Settings\FrontEnd::instance();
		Admin\Entry::instance();
		Admin\Debug::instance();
		Admin\Account::instance();
		// Admin\OrderDetail::instance();
		Admin\OrderList::instance();
		Admin\DelayEmail::instance();
		Api\Base::instance();
		Api\LC::instance();
		Domains\Loader::instance();
		Theme\FrontEnd::instance();

		\add_action( 'init', [ __CLASS__, 'register_assets' ] );

		\add_action( 'admin_menu', [ __CLASS__ , 'add_menu' ], 10 );
		\add_action( 'admin_menu', [ __CLASS__ , 'add_submenu' ], 100 );

		\add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_frontend_assets' ] );
		\add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_assets' ] );

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
		[ Settings\FrontEnd::class, 'powerhouse_settings_page_callback' ],
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
		\add_submenu_page( 'powerhouse', __( '設定', 'powerhouse' ), __( '設定', 'powerhouse' ), 'manage_options', 'powerhouse-settings', [ Settings\FrontEnd::class, 'powerhouse_settings_page_callback' ] );

		// 如果沒有註冊產品資訊，就不用顯示授權碼
		$product_infos = \apply_filters( 'powerhouse_product_infos', [] );
		if (!$product_infos) {
			return;
		}
		\add_submenu_page( 'powerhouse', __( '授權碼', 'powerhouse' ), __( '授權碼', 'powerhouse' ), 'manage_options', self::LC_MENU_SLUG, [ LC::class, 'powerhouse_license_codes_page_callback' ] );
	}

	/**
	 * 前端載入統一樣式 css
	 *
	 * @return void
	 */
	public static function enqueue_frontend_assets(): void {
		\wp_enqueue_style( Plugin::$snake, Plugin::$url . '/js/dist/css/front.min.css', [], Plugin::$version );
	}

	/**
	 * 後台 css
	 *
	 * @return void
	 */
	public static function enqueue_admin_assets(): void {

		// 後台載入統一樣式
		\wp_enqueue_style( Plugin::$snake, Plugin::$url . '/js/dist/css/admin.min.css', [], Plugin::$version );

		if (!General::in_url(
			[
				'admin.php?page=powerhouse',
			]
			)) {
			return;
		}

		// JS 按需載入
		Vite\enqueue_asset(
			Plugin::$dir . '/js/dist',
			'js/src/main.tsx',
			[
				'handle'    => Plugin::$kebab,
				'in-footer' => true,
			]
		);

		$post_id   = \get_the_ID();
		$permalink = $post_id ? \get_permalink( $post_id ) : '';

		/** @var array<string> $active_plugins */
		$active_plugins = \get_option( 'active_plugins', [] );

		$encrypt_env = Base::simple_encrypt(
			[
				'SITE_URL'             => \untrailingslashit( \site_url() ),
				'API_URL'              => \untrailingslashit( \esc_url_raw( rest_url() ) ),
				'CURRENT_USER_ID'      => \get_current_user_id(),
				'CURRENT_POST_ID'      => $post_id,
				'PERMALINK'            => \untrailingslashit( $permalink ),
				'APP_NAME'             => Plugin::$app_name,
				'KEBAB'                => Plugin::$kebab,
				'SNAKE'                => Plugin::$snake,
				'BUNNY_LIBRARY_ID'     => \get_option( 'bunny_library_id', '' ),
				'BUNNY_CDN_HOSTNAME'   => \get_option( 'bunny_cdn_hostname', '' ),
				'BUNNY_STREAM_API_KEY' => \get_option( 'bunny_stream_api_key', '' ),
				'NONCE'                => \wp_create_nonce( 'wp_rest' ),
				'APP1_SELECTOR'        => Base::APP1_SELECTOR,
				'ELEMENTOR_ENABLED'    => \in_array( 'elementor/elementor.php', $active_plugins, true ), // 檢查 elementor 是否啟用
			]
		);

		\wp_localize_script(
			Plugin::$kebab,
			Plugin::$snake . '_data',
			[
				'env' => $encrypt_env,
			]
		);
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
