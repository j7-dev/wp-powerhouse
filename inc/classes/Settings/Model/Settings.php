<?php

declare(strict_types=1);

namespace J7\Powerhouse\Settings\Model;

use J7\WpUtils\Classes\DTO as BaseDTO;
use J7\Powerhouse\Theme\Model\Theme;
use J7\Powerhouse\Settings\Core\ApiBoosterRule;

/** Powerhouse Settings */
class Settings extends BaseDTO {

	// mu-plugins 裡面的 api booster 使用此 key ，請勿修改
	const SETTINGS_KEY = 'powerhouse_settings';

	/** @var string $theme 選擇主題 */
	public string $theme = 'custom';

	/** @var string $enable_theme_changer 啟用主題切換器 */
	public string $enable_theme_changer = 'no';

	/** @var string $enable_theme 啟用主題 */
	public string $enable_theme = 'yes';

	/** @var array<string, string> $theme_css 當選擇 custom 主題時，使用自訂的 css */
	public array $theme_css = [];

	/** @var string $delay_email 延遲寄信 */
	public string $delay_email = 'yes';

	/** @var string $last_name_optional 姓氏可選 */
	public string $last_name_optional = 'yes';

	/** @var string $enable_captcha_login 啟用登入驗證碼 */
	public string $enable_captcha_login = 'no';

	/** @var array<string> $captcha_role_list 驗證碼角色列表 */
	public array $captcha_role_list = [ 'administrator' ];

	/** @var string $enable_captcha_register 啟用註冊驗證碼 */
	public string $enable_captcha_register = 'no';


	/** @var array $api_booster_rules API 加速器規則 */
	public array $api_booster_rules = [];

	/** @var array $api_booster_rule_recipes API 加速器模板 */
	public array $api_booster_rule_recipes = [];


	// BunnyCDN 相關

	/** @var string $bunny_library_id BunnyCDN 圖庫 ID */
	public string $bunny_library_id = '';

	/** @var string $bunny_cdn_hostname BunnyCDN 主機名稱 */
	public string $bunny_cdn_hostname = '';

	/** @var string $bunny_stream_api_key BunnyCDN 串流 API 金鑰 */
	public string $bunny_stream_api_key = '';

	/** @var self 實例 */
	protected static $instance = null;

	/**
	 * Constructor.
	 *
	 * @param array<string, mixed> $input Input values.
	 */
	public function __construct( array $input = [] ) {
		parent::__construct($input);
		self::$instance  = $this;
		$this->theme_css = Theme::instance()->to_array();
	}

	/**
	 * 取得單一實例
	 *
	 * @return self
	 */
	public static function instance():self { // phpcs:ignore
		if ( null === self::$instance ) {
			$setting_array = \get_option(self::SETTINGS_KEY, []);
			$setting_array = is_array($setting_array) ? $setting_array : [];

			/** @var array<string, mixed> $setting_array */
			unset($setting_array['theme_css']); // theme_css 獨立初始化

			unset($setting_array['enable_api_booster']);

			return new self($setting_array);
		}
		return self::$instance;
	}

	/**
	 * 部分更新
	 * 保留原本 array 上的值，只對新的 key-value 更新
	 *
	 * @param array<string, mixed> $values 更新值
	 */
	public function partial_update( array $values ): void {
		$from_setting_array = $this->to_array();
		$to_setting_array   = \wp_parse_args($values, $from_setting_array);
		foreach ($to_setting_array as $key => $value) {
			if (!property_exists($this, $key)) {
				unset($to_setting_array[ $key ]);
			}
		}
		\update_option(self::SETTINGS_KEY, $to_setting_array);
	}

	/** 初始化後執行 */
	protected function after_init(): void {
		$this->api_booster_rule_recipes = ApiBoosterRule::instance()->get_recipes();
	}
}
