<?php

declare(strict_types=1);

namespace J7\PowerContract\Settings;

use J7\WpUtils\Classes\DTO as BaseDTO;

if (class_exists('J7\PowerContract\Settings\DTO')) {
	return;
}
/**
 * 設定物件
 */
final class DTO extends BaseDTO {

	const SETTINGS_KEY = 'powerhouse_settings';

	/** @var string $theme 主題 */
	public string $theme = 'power';

	/** @var self 實例 */
	private static $instance = null;

	/**
	 * Constructor.
	 *
	 * @param array<string, mixed> $input Input values.
	 */
	public function __construct( array $input = [] ) {
		parent::__construct($input);
		self::$instance = $this;
	}

	/**
	 * 取得單一實例
	 *
	 * @return self
	 */
	public static function instance() { // phpcs:ignore
		$setting_array = \get_option(self::SETTINGS_KEY, []);
		if (!\is_array($setting_array)) {
			$setting_array = [];
		}

		/** @var array<string, mixed> $setting_array */
		if ( null === self::$instance ) {
			new self($setting_array);
		}
		return self::$instance;
	}


	/**
	 * 取得 input name 表單用
	 *
	 * @param string $key 欄位名稱
	 * @return string 欄位名稱
	 */
	// public static function get_field_name( string $key ): string {
	// return self::SETTINGS_KEY . "[$key]";
	// }
}
