<?php

declare(strict_types=1);

namespace J7\Powerhouse\Settings\Core;

/** ApiBoosterRule */
class ApiBoosterRule {
	use \J7\WpUtils\Traits\SingletonTrait;

	/** @var array<string> $power_plugins Power 系列外掛列表 */
	public $power_plugins = [
		'powerhouse/plugin.php',
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
				'key'     => 'api_booster_rule_default',
				'enabled' => 'no',
				'name'    => 'Power 系列外掛 API 時，不載入其他外掛',
				'rules'   => "/wp-json/power-*\n/wp-json/v2/powerhouse/*",
				'plugins' => $this->power_plugins,
			],
		];
	}
}
