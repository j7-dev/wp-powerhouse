<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Woocommerce\Model;

use J7\WpUtils\Classes\DTO;

/**
 * ProductTypes DTO
 *
 * @see https://github.com/woocommerce/woocommerce/blob/trunk/plugins/woocommerce/src/Enums/ProductType.php
 * */
class ProductTypes extends DTO {
	/** @var array<array[value => string, label => string, color => string]> $product_types */
	public array $product_types;

	/** @var array<array[value => string, label => string, color => string]> $product_types */
	protected static array $product_types_mapper = [
		[
			'value' => 'simple',
			'label' => '簡單商品',
			'color' => 'processing', // 藍色
		],
		[
			'value' => 'grouped',
			'label' => '組合商品',
			'color' => 'orange', // 綠色
		],
		[
			'value' => 'external',
			'label' => '外部商品',
			'color' => 'lime', // 橘色
		],
		[
			'value' => 'variable',
			'label' => '可變商品',
			'color' => 'magenta', // 紅色
		],
		[
			'value' => 'variation',
			'label' => '商品變體',
			'color' => 'magenta', // 紅色
		],
		[
			'value' => 'subscription',
			'label' => '簡易訂閱',
			'color' => 'cyan', // 紫色
		],
		[
			'value' => 'variable-subscription',
			'label' => '可變訂閱',
			'color' => 'purple', // 青色
		],
		[
			'value' => 'subscription_variation',
			'label' => '訂閱變體',
			'color' => 'purple',
		],
	];


	/** 取得 ProductTypes @return self */
	public static function instance(): self {
		$product_type_keys = array_keys( \wc_get_product_types() );
		$product_types     = [];
		foreach ( self::$product_types_mapper as $product_type ) {
			if ( in_array( $product_type['value'], $product_type_keys ) ) {
				$product_types[] = $product_type;
			}
		}

		return new self(
			[
				'product_types' => $product_types,
			]
			);
	}
}
