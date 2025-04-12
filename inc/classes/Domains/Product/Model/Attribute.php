<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Product\Model;

use J7\WpUtils\Classes\DTO;

/**
 * 商品屬性 DTO
 */
abstract class Attribute extends DTO {

	/** @var array<int|string, array{name: string, options: array, position: int}|string> $attributes_arr */
	public array $attributes;

	/**
	 * 建構子
	 *
	 * @param \WC_Product $product 商品
	 */
	public function __construct( $product ) {
		// 組合商品屬性 $attributes_arr
		$attributes     = $product->get_attributes(); // get attributes object
		$attributes_arr = [];
		foreach ( $attributes as $key => $attribute ) {
			if ( $attribute instanceof \WC_Product_Attribute ) {
				$attributes_arr[] = [
					'name'     => \wc_attribute_label( $attribute?->get_name() ),
					'options'  => $attribute?->get_options(),
					'position' => $attribute?->get_position(),
				];
			}

			if ( is_string( $key ) && is_string( $attribute ) ) {
				$attributes_arr[ urldecode( $key ) ] = $attribute;
			}
		}

		$this->attributes = $attributes_arr;
	}
}
