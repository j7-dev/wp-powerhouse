<?php
/**
 * Loader 載入每個 Resource API
 * 有要做條件載入可以在這邊做
 */

declare(strict_types=1);

namespace J7\Powerhouse\Resources;

/**
 * Class Loader
 */
final class Loader {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		Post\V2Api::instance();
		User\V2Api::instance();
		Product\V2Api::instance();
		Option\V2Api::instance();
		Shortcode\V2Api::instance();
		Upload\V2Api::instance();
		Duplicate\V2Api::instance();
	}
}
