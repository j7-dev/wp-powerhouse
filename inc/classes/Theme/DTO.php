<?php

declare(strict_types=1);

namespace J7\Powerhouse\Theme;

use J7\WpUtils\Classes\DTO as BaseDTO;
use J7\Powerhouse\Settings\DTO as SettingsDTO;


if (class_exists('J7\Powerhouse\Theme\DTO')) {
	return;
}
/**
 * 設定物件
 */
final class DTO extends BaseDTO {

	/** @var string primary 顏色 */
	public string $p = '49.12% 0.3096 275.75';

	/** @var string primary content 內容顏色 */
	public string $pc = '89.824% 0.06192 275.75';

	/** @var string secondary 顏色 */
	public string $s = '69.71% 0.329 342.55';

	/** @var string secondary content 內容顏色 */
	public string $sc = '98.71% 0.0106 342.55';

	/** @var string accent 顏色 */
	public string $a = '76.76% 0.184 183.61';

	/** @var string accent content 內容顏色 */
	public string $ac = '15.352% 0.0368 183.61';

	/** @var string neutral 顏色 */
	public string $n = '32.1785% 0.02476 255.701624';

	/** @var string neutral content 內容顏色 */
	public string $nc = '89.4994% 0.011585 252.096176';

	/** @var string base-100 顏色 */
	public string $b1 = '100% 0 0';

	/** @var string base-200 顏色 */
	public string $b2 = '96.1151% 0 0';

	/** @var string base-300 顏色 */
	public string $b3 = '92.4169% 0.00108 197.137559';

	/** @var string base-content 內容顏色 */
	public string $bc = '27.8078% 0.029596 256.847952';

	// status color

	/** @var string info 顏色 */
	public string $in = '72.06% 0.191 231.6';

	/** @var string info content 內容顏色 */
	public string $inc = '0% 0 0';

	/** @var string success 顏色 */
	public string $su = '64.8% 0.150 160';

	/** @var string success content 內容顏色 */
	public string $suc = '0% 0 0';

	/** @var string warning 顏色 */
	public string $wa = '84.71% 0.199 83.87';

	/** @var string warning content 內容顏色 */
	public string $wac = '0% 0 0';

	/** @var string error 顏色 */
	public string $er = '71.76% 0.221 22.18';

	/** @var string error content 內容顏色 */
	public string $erc = '0% 0 0';

	// other

	/** @var string rounded_box 圓角 */
	public string $rounded_box = '1rem';

	/** @var string rounded_btn 圓角 */
	public string $rounded_btn = '0.5rem';

	/** @var string rounded_badge 圓角 */
	public string $rounded_badge = '1.9rem';

	/** @var string animation_btn 動畫 */
	public string $animation_btn = '0.25s';

	/** @var string animation_input 動畫 */
	public string $animation_input = '.2s';

	/** @var string btn_focus_scale 動畫 */
	public string $btn_focus_scale = '0.95';

	/** @var string border_btn 邊框 */
	public string $border_btn = '1px';

	/** @var string tab_border 邊框 */
	public string $tab_border = '1px';

	/** @var string tab_radius 圓角 */
	public string $tab_radius = '0.5rem';

	/** @var string theme 主題 */
	public string $theme = 'custom';

	/** @var self 實例 */
	private static $instance = null;

	/**
	 * Constructor.
	 *
	 * @param array<string, mixed> $input Input values.
	 */
	public function __construct( array $input = [] ) {
		parent::__construct($input, false);
		self::$instance = $this;
	}

	/**
	 * 取得單一實例
	 *
	 * @return self|null
	 */
	public static function instance():self|null { // phpcs:ignore
		try {
			$setting_array = \get_option(SettingsDTO::SETTINGS_KEY, []);
			@[ // @phpstan-ignore-line
			'theme_css' => $theme_css,
			'theme'     => $theme,
			]              = $setting_array;

			$theme_css          = is_array($theme_css) ? $theme_css : [];
			$theme_css['theme'] = $theme;

			/** @var array<string, mixed> $theme_css */
			if ( null === self::$instance ) {
				new self(self::remove_double_dash($theme_css));
			}
			return self::$instance;

		} catch (\Throwable $th) {
			return null;
		}
	}

	/**
	 * 移除雙破折號
	 *
	 * @param array<string, mixed> $theme_css 主題 CSS。
	 * @return array<string, mixed> 移除雙破折號後的主題 CSS。
	 */
	public static function remove_double_dash( array $theme_css = [] ): array {
		$new_theme_css = [];
		foreach ($theme_css as $key => $value) {
			$new_theme_css[ str_replace('--', '', $key) ] = $value;
		}
		return $new_theme_css;
	}


	/**
	 * 取得公開的屬性
	 *
	 * @param bool $with_dash 是否帶 --
	 *
	 * @return array<string, string>
	 */
	public function to_array( $with_dash = true ): array {

		$properties = parent::to_array();
		if (!$with_dash) {
			/** @var array<string, string> $properties */
			return $properties;
		}

		$formatted_properties = [];
		/** @var string $value */
		foreach ($properties as $key => $value) {
			if ('theme' === $key) {
				$formatted_properties['theme'] = $value;
				continue;
			}

			$formatted_properties[ "--{$key}" ] = $value;
		}
		return $formatted_properties;
	}

	/**
	 * 印出 CSS
	 *
	 * @return void
	 */
	public function print_css(): void {
		$theme_css = $this->to_array();
		$style     = '<style>';
		$style    .= "[data-theme='{$theme_css['theme']}'] {";

		unset($theme_css['theme']);
		foreach ($theme_css as $key => $value) {
			$style .= "{$key}: {$value};";
		}
		$style .= '}';
		$style .= '</style>';
		echo $style;
	}
}
