<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Product\Model;

/**
 * 可變商品的變體相關 DTO
 */
final class Variation extends DTO {

	/** @var array<array<string, mixed>>|null $children 變體的商品 DTO 資料 */
	public array|null $children = null;

	/**
	 * 取得實例
	 *
	 * @param \WC_Product $product 商品
	 */
	public static function instance( \WC_Product $product, bool $with_description = false, array $meta_keys = [] ): self {
		// 將變體加入倒 children
		$variation_ids = $product->get_children(); // get variations
		$children      = null;
		if ( $variation_ids ) {
			$variation_product_array = [];
			foreach ($variation_ids as $variation_id) {
				$variation_product = \wc_get_product( $variation_id );
				if (!$variation_product) {
					continue;
				}
				$variation_product_array[] = Product::instance( $variation_product, $with_description, $meta_keys )->to_array();
			}

			$children = $variation_product_array;
		}
		$args = [
			'children' => $children,
		];

		$instance = new self( $args );
		return $instance;
	}
}
