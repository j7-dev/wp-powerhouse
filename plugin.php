<?php
/**
 * Plugin Name:       Powerhouse
 * Plugin URI:        https://github.com/j7-dev/powerhouse
 * Description:       方便開發 WordPress 外掛的工具包。
 * Version:           1.0.30
 * Requires at least: 5.7
 * Requires PHP:      8.0
 * Author:            J7
 * Author URI:        https://github.com/j7-dev
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       power-partner-server
 * Domain Path:       /languages
 * Tags: vite, WordPress plugin
 */

declare ( strict_types=1 );

namespace J7\Powerhouse;

if ( \class_exists( 'J7\Powerhouse\Plugin' ) ) {
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Class Plugin
 * TODO 依賴 WC ?
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
				'app_name'    => 'Powerhouse',
				'github_repo' => 'https://github.com/j7-dev/wp-powerhouse',
				'callback'    => [ Bootstrap::class, 'instance' ],
			]
		);

		self::$template_page_names = [ 'admin', 'settings', 'license-codes' ];
	}
}

Plugin::instance();
