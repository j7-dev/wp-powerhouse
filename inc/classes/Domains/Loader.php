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
		Comment\Core\V2Api::instance();
		Post\Core\V2Api::instance();
		User\Core\V2Api::instance();
		Option\Core\V2Api::instance();
		Shortcode\Core\V2Api::instance();
		Upload\Core\V2Api::instance();
		LC\Core\V2Api::instance();

		if ( class_exists( '\WooCommerce' ) ) {
			Option\Core\WC_Countries::instance();
			Product\Core\V2Api::instance();
			Copy\Core\V2Api::instance();
			Limit\Core\V2Api::instance();
			Order\Core\V2Api::instance();
			Report\Revenue\Core\V2Api::instance();
		}
	}
}
