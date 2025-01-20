<?php
/**
 * Powerhouse Loader
 * 提前載入 Powerhouse 的 vendor，確保 Powerhouse 是最先載入的
 */

namespace J7\Powerhouse\MU;

/**
 * Loader
 */
final class Loader {

	/**
	 * 想要提前載入的 Traits
	 *
	 * @var array<string>
	 */
	private $traits = [
		'J7\WpUtils\Traits\PluginTrait',
		'J7\WpUtils\Traits\SingletonTrait',
	];

	/**
	 * 想要提前載入的 Classes
	 *
	 * @var array<string>
	 */
	private $classes = [];

	/**
	 * Constructor.
	 */
	public function __construct() {
		\add_action('muplugins_loaded', [ $this, 'loader' ], 100);
	}

	/**
	 * Require Powerhouse vendor
	 *
	 * @return void
	 */
	public function loader(): void {
		$vendor_path = WP_PLUGIN_DIR . '/powerhouse/vendor/autoload.php';
		if (file_exists($vendor_path)) {
			require_once $vendor_path;

			// 這邊必須要使用你想要提早家載的 trait 或 class ，不然沒有用到  composer 會因為按需載入不會主動載入類
			foreach ($this->traits as $trait) {
				trait_exists($trait);
			}

			foreach ($this->classes as $class) {
				class_exists($class);
			}
		}
	}
}

new Loader();
