<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Woocommerce\Model;

use J7\WpUtils\Classes\DTO;

/**
 * ProductStockStatuses DTO
 *
 * @see https://github.com/woocommerce/woocommerce/blob/trunk/plugins/woocommerce/src/Enums/ProductType.php
 * */
class ProductStockStatuses extends DTO {
	/** @var array<array{value: string, label: string, color: string}> $product_stock_statuses */
	public array $product_stock_statuses;

	/** @var array<array{value: string, label: string, color: string}> $product_stock_statuses_mapper */
	protected static array $product_stock_statuses_mapper = [
		'instock' => [
			'value' => 'instock',
			'label' => '有庫存',
			'color' => 'blue',
		],
		'outofstock' => [
			'value' => 'outofstock',
			'label' => '缺貨',
			'color' => 'magenta',
		],
		'onbackorder' => [
			'value' => 'onbackorder',
			'label' => '預定',
			'color' => 'cyan',
		],
	];

	/** 取得 ProductStockStatuses @return self */
	public static function instance(): self {
		$product_stock_status_array = \wc_get_product_stock_status_options(); // key => name 的 array
		$product_stock_statuses     = [];
		foreach ( $product_stock_status_array as $stock_status => $stock_status_name ) {
			if ( isset( self::$product_stock_statuses_mapper[ $stock_status ] ) ) {
				$product_stock_statuses[] = self::$product_stock_statuses_mapper[ $stock_status ];
				continue;
			}
			$product_stock_statuses[] = [
				'value' => $stock_status,
				'label' => $stock_status_name,
				'color' => 'default',
			];
		}

		return new self(
			[
				'product_stock_statuses' => $product_stock_statuses,
			]
			);
	}
}
