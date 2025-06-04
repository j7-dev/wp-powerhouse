<?php

declare(strict_types=1);

namespace J7\Powerhouse\Settings\Core;

/** ApiBoosterRule */
class ApiBoosterRule {
	use \J7\WpUtils\Traits\SingletonTrait;

	/** @var array<string> $base_plugins 基礎外掛列表 */
	public $base_plugins = [
		'woocommerce/woocommerce.php',
		'woocommerce-subscriptions/woocommerce-subscriptions.php',
		'powerhouse/plugin.php',
		'elementor/elementor.php',
		'elementor-pro/elementor-pro.php',
	];

	/** @var array<string> $power_plugins Power 系列外掛列表 */
	public $power_plugins = [
		'power-contract/plugin.php',
		'power-course/plugin.php',
		'power-docs/plugin.php',
		'power-membership/plugin.php',
		'power-partner/plugin.php',
		'power-shop/plugin.php',
	];

	/**
	 * 取得預設規則
	 *
	 * @return array<array-key, mixed>
	 */
	public function get_recipes(): array {
		return [
			[
				'key'     => 'api_booster_rule_power_plugins',
				'enabled' => 'no',
				'name'    => 'Power 系列外掛 API 時，不載入其他外掛',
				'rules'   => "/wp-json/power-*\n/wp-json/v2/powerhouse/*",
				'plugins' => [
					...$this->base_plugins,
					...$this->power_plugins,
				],
			],
			[
				'key'     => 'api_booster_rule_power_course',
				'enabled' => 'no',
				'name'    => 'Power Course 後台 API 加速',
				'rules'   => "/wp-json/power-course/*\n/wp-json/v2/powerhouse/*",
				'plugins' => [
					...$this->base_plugins,
					'power-course/plugin.php',
				],
			],
			[
				'key'     => 'api_booster_rule_power_shop',
				'enabled' => 'no',
				'name'    => 'Power Shop 後台 API 加速',
				'rules'   => "/wp-json/power-shop/*\n/wp-json/v2/powerhouse/*",
				'plugins' => [
					...$this->base_plugins,
					'power-shop/plugin.php',
				],
			],
		];
	}
}
