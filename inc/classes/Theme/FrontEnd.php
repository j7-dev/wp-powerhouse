<?php

declare (strict_types = 1);

namespace J7\Powerhouse\Theme;

use J7\Powerhouse\Plugin;
use J7\Powerhouse\Settings\DTO;

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
		\add_action('wp_head', [ $this, 'custom_theme_color' ], 10);
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
		// 已經有 data-theme 則不會再新增
		if (strpos($output, 'data-theme') !== false) {
			return $output;
		}

		$theme = DTO::instance()->theme;
		return "{$output} id=\"tw\" data-theme=\"{$theme}\"";
	}

	public function custom_theme_color() {

		\var_dump('testststet');
		?>
<style>
	[data-theme='custom'] {
		color-scheme: custom;
	--in: 72.06% 0.191 231.6;
	--su: 64.8% 0.150 160;
	--wa: 84.71% 0.199 83.87;
	--er: 71.76% 0.221 22.18;
	--pc: 89.824% 0.06192 275.75;
	--ac: 15.352% 0.0368 183.61;
	--inc: 0% 0 0;
	--suc: 0% 0 0;
	--wac: 0% 0 0;
	--erc: 0% 0 0;
	--rounded-box: 1rem;
	--rounded-btn: 0.5rem;
	--rounded-badge: 1.9rem;
	--animation-btn: 0.25s;
	--animation-input: .2s;
	--btn-focus-scale: 0.95;
	--border-btn: 1px;
	--tab-border: 1px;
	--tab-radius: 0.5rem;
	--p: 49.12% 0.3096 275.75;
	--s: 69.71% 0.329 342.55;
	--sc: 98.71% 0.0106 342.55;
	--a: 76.76% 0.184 183.61;
	--n: 32.1785% 0.02476 255.701624;
	--nc: 89.4994% 0.011585 252.096176;
	--b1: 100% 0 0;
	--b2: 96.1151% 0 0;
	--b3: 92.4169% 0.00108 197.137559;
	--bc: 27.8078% 0.029596 256.847952;
}
</style>
		<?php
	}


	/**
	 * 渲染主題按鈕
	 *
	 * @return void
	 */
	public static function render_button() {
		Plugin::get('theme');
	}
}
