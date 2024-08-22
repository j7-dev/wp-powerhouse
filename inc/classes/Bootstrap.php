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
	 * @var array
	 * Store instances of classes
	 */
	public $instances = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->instances['Settings']          = Settings::instance();
		$this->instances['Admin\Account']     = Admin\Account::instance();
		$this->instances['Admin\OrderDetail'] = Admin\OrderDetail::instance();
		$this->instances['Admin\OrderList']   = Admin\OrderList::instance();
		$this->instances['Admin\DelayEmail']  = Admin\DelayEmail::instance();

		\add_action( 'admin_menu', [ __CLASS__ , 'add_menu' ], 10 );
		\add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
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
		[ Settings::class, 'powerhouse_page_callback' ],
		'dashicons-superhero',
		3
		);

		\add_submenu_page( 'powerhouse', __( '設定', 'powerhouse' ), __( '其他', 'powerhouse' ), 'manage_options', 'powerhouse', [ Settings::class, 'powerhouse_page_callback' ], 1000 );
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

		// CDN shoelace
		// phpcs:disable
		echo '
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.16.0/cdn/themes/light.css" />
		<script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.16.0/cdn/shoelace-autoloader.js"></script>
		';
		// phpcs:enable
	}
}
