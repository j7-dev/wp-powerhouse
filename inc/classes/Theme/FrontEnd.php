<?php

declare (strict_types = 1);

namespace J7\Powerhouse\Theme;

if ( class_exists( 'J7\Powerhouse\Theme\FrontEnd' ) ) {
	return;
}
/**
 * Class FrontEnd
 */
final class FrontEnd {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		\add_filter( 'language_attributes', [ $this, 'add_html_attr' ], 20, 2 );
	}

	/**
	 * Add html attr
	 * 用來切換 daisyUI 的主題
	 *
	 * @param string $output Output.
	 * @param string $doctype Doctype.
	 *
	 * @return string
	 */
	public function add_html_attr( string $output, string $doctype ): string {
		return $output . ' data-theme="power"';
	}
}
