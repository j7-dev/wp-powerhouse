<?php

declare(strict_types=1);

namespace J7\Powerhouse\Settings;

use J7\WpUtils\Classes\DTO as BaseDTO;

if (class_exists('J7\Powerhouse\Settings\DTO')) {
	return;
}
/**
 * 設定物件
 */
final class DTO extends BaseDTO {

	const SETTINGS_KEY = 'powerhouse_settings';

	/** @var string $theme 選擇主題 */
	public string $theme = 'power';

	/** @var array<string, string> $theme_css 當選擇 custom 主題時，使用自訂的 css */
	public array $theme_css = [];

	/** @var string $delay_email 延遲寄信 */
	public string $delay_email = 'yes';

	/** @var string $last_name_optional 姓氏可選 */
	public string $last_name_optional = 'yes';

	/** @var string $enable_api_booster 啟用 API 加速器 */
	public string $enable_api_booster = 'no';



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
	 * 取得 input name 表單 field_name 還有預設值
	 * 可以用 list($field_name, $default_value) 解構
	 *
	 * @param string $key 欄位名稱
	 * @return array{0: string, 1: string} [欄位名稱, 預設值]
	 */
	public static function get_field_name_and_value( string $key ): array {
		$field_name  = self::SETTINGS_KEY . "[$key]";
		$field_value = self::instance()->$key;
		return [ $field_name, $field_value ];
	}
}
