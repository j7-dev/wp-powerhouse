<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains;

/**
 * Loader 載入每個 Resource API
 * 有要做條件載入可以在這邊做
 */
final class Loader {
	use \J7\WpUtils\Traits\SingletonTrait;

	/** Constructor */
	public function __construct() {
		Post\V2Api::instance();
		User\V2Api::instance();
		Option\V2Api::instance();
		Shortcode\V2Api::instance();
		Upload\V2Api::instance();
		LC\V2Api::instance();
		if ( class_exists( '\WooCommerce' ) ) {
			Product\V2Api::instance();
			Copy\V2Api::instance();
			Limit\V2Api::instance();
		}
	}
}
