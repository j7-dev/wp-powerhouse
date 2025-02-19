<?php

declare(strict_types=1);

namespace J7\Powerhouse\Settings;

use J7\WpUtils\Classes\DTO as BaseDTO;
use J7\Powerhouse\Theme\DTO as ThemeDTO;

if (class_exists('J7\Powerhouse\Settings\DTO')) {
	return;
}
/**
 * 設定物件
 */
final class DTO extends BaseDTO {

	const SETTINGS_KEY = 'powerhouse_settings';

	/** @var string $theme 選擇主題 */
	public string $theme = 'custom';

	/** @var string $enable_theme_changer 啟用主題切換器 */
	public string $enable_theme_changer = 'no';

	/** @var array<string, string> $theme_css 當選擇 custom 主題時，使用自訂的 css */
	public array $theme_css = [];

	/** @var string $delay_email 延遲寄信 */
	public string $delay_email = 'yes';

	/** @var string $last_name_optional 姓氏可選 */
	public string $last_name_optional = 'yes';

	/** @var string $enable_api_booster 啟用 API 加速器 */
	public string $enable_api_booster = 'no';

	// BunnyCDN 相關

	/** @var string $bunny_library_id BunnyCDN 圖庫 ID */
	public string $bunny_library_id = '';

	/** @var string $bunny_cdn_hostname BunnyCDN 主機名稱 */
	public string $bunny_cdn_hostname = '';

	/** @var string $bunny_stream_api_key BunnyCDN 串流 API 金鑰 */
	public string $bunny_stream_api_key = '';

	/** @var self 實例 */
	private static $instance = null;

	/**
	 * Constructor.
	 *
	 * @param array<string, mixed> $input Input values.
	 */
	public function __construct( array $input = [] ) {
		parent::__construct($input);
		self::$instance  = $this;
		$this->theme_css = ThemeDTO::instance()?->to_array() ?? [];
	}

	/**
	 * 取得單一實例
	 *
	 * @return self
	 */
	public static function instance():self { // phpcs:ignore
		if ( null === self::$instance ) {
			$setting_array = \get_option(self::SETTINGS_KEY, []);
			if (!\is_array($setting_array)) {
				$setting_array = [];
			}

			/** @var array<string, mixed> $setting_array */
			unset($setting_array['theme_css']); // theme_css 獨立初始化
			return new self($setting_array);
		}
		return self::$instance;
	}
}
