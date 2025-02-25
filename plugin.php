<?php
/**
 * Plugin Name:       Powerhouse
 * Plugin URI:        https://github.com/j7-dev/powerhouse
 * Description:       方便開發 WordPress 外掛的工具包。
 * Version:           3.1.0
 * Requires at least: 5.7
 * Requires PHP:      8.0
 * Author:            J7
 * Author URI:        https://github.com/j7-dev
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       powerhouse
 * Domain Path:       /languages
 * Tags: vite, WordPress plugin
 *
 * *****************************************************************************************
 *                                                                                         *
 * ██████╗  ██████╗ ██╗    ██╗███████╗██████╗ ██╗  ██╗ ██████╗ ██╗   ██╗███████╗███████╗   *
 * ██╔══██╗██╔═══██╗██║    ██║██╔════╝██╔══██╗██║  ██║██╔═══██╗██║   ██║██╔════╝██╔════╝   *
 * ██████╔╝██║   ██║██║ █╗ ██║█████╗  ██████╔╝███████║██║   ██║██║   ██║███████╗█████╗     *
 * ██╔═══╝ ██║   ██║██║███╗██║██╔══╝  ██╔══██╗██╔══██║██║   ██║██║   ██║╚════██║██╔══╝     *
 * ██║     ╚██████╔╝╚███╔███╔╝███████╗██║  ██║██║  ██║╚██████╔╝╚██████╔╝███████║███████╗   *
 * ╚═╝      ╚═════╝  ╚══╝╚══╝ ╚══════╝╚═╝  ╚═╝╚═╝  ╚═╝ ╚═════╝  ╚═════╝ ╚══════╝╚══════╝   *
 *                                                                                         *
 * *********************************** www.powerhouse.com **********************************
 */

declare ( strict_types=1 );

namespace J7\Powerhouse;

if ( \class_exists( 'J7\Powerhouse\Plugin' ) ) {
	return;
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/inc/classes/Domains/LC/Utils.php';

/**
 * Class Plugin
 */
final class Plugin {
	use \J7\WpUtils\Traits\PluginTrait;
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->required_plugins = [
			[
				'name'     => 'WooCommerce',
				'slug'     => 'woocommerce',
				'required' => true,
				'version'  => '7.6.0',
			],
		];

		$this->init(
		[
			'app_name'     => 'Powerhouse',
			'github_repo'  => 'https://github.com/j7-dev/wp-powerhouse',
			'callback'     => [ Bootstrap::class, 'instance' ],
			'priority'     => -10,
			'lc'           => false,
			'hide_submenu' => true,
		]
		);

		self::$template_page_names = [ 'admin', 'settings', 'license-codes', 'powerhouse', 'admin-layout' ];
	}


	/**
	 * Activate
	 * 啟用時創建 access_itemmeta table
	 *
	 * @return void
	 * @throws \Exception Exception.
	 */
	public function activate(): void {
		require_once __DIR__ . '/inc/classes/Domains/Limit/Utils/CreateTable.php';
		Domains\Limit\Utils\CreateTable::create_itemmeta_table();
	}
}

Plugin::instance();
