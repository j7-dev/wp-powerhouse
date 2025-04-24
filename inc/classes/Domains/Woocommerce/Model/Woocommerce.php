<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Woocommerce\Model;

use J7\WpUtils\Classes\DTO;
use J7\Powerhouse\Domains\Woocommerce\Core\Settings;
use J7\Powerhouse\Utils\Base as PowerhouseUtils;

/** Get Woocommerce API DTO */
class Woocommerce extends DTO {

	/** @var array<string, string> $countries TW: 台灣 */
	public array $countries;

	/** @var array{slug: string, symbol: string} $currency 貨幣 */
	public array $currency;

	/** @var array<array{value: string, label: string, hierarchical: bool, publicly_queryable: bool}> $product_taxonomies 商品分類 */
	public array $product_taxonomies;

	/** @var array<array{value: string, label: string, color: string}> $product_types 商品類型 */
	public array $product_types;

	/** @var int $notify_low_stock_amount 低庫存通知數量 */
	public int $notify_low_stock_amount;

	/** @var string $dimension_unit 尺寸單位 */
	public string $dimension_unit;

	/** @var string $weight_unit 重量單位 */
	public string $weight_unit;

	/** 取得 ProductTypes @return self */
	public static function instance(): self {
		$countries = \WC()->countries->get_countries();
		$currency  = \get_option( 'woocommerce_currency', 'TWD' );

		$wc_settings = Settings::instance();

		$product_taxonomies = PowerhouseUtils::get_taxonomy_options();

		$args = array_merge(
			[
				'countries'               => $countries,
				'currency'                => [
					'slug'   => $currency,
					'symbol' => html_entity_decode( \get_woocommerce_currency_symbol($currency) ),
				],
				'product_taxonomies'      => $product_taxonomies,
				'notify_low_stock_amount' => (int) $wc_settings->notify_low_stock_amount,
				'dimension_unit'          => $wc_settings->dimension_unit,
				'weight_unit'             => $wc_settings->weight_unit,
			],
			ProductTypes::instance()->to_array(),
		);

		return new self($args);
	}
}
