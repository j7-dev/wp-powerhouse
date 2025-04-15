<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Product\Model;

/**
 * 商品分類、標籤、品牌 DTO
 * 如果要在前端 mapping 分類、標籤的名稱，可以用 products/options API
 */
final class Taxonomy extends DTO {

	/** @var array<string> $category_ids 商品分類 ids */
	public array $category_ids;

	/** @var array<string> $tag_ids 商品標籤 ids */
	public array $tag_ids;

	/**
	 * 取得實例
	 *
	 * @param \WC_Product $product 商品
	 */
	public static function instance( $product ): self {
		$args = [
			'category_ids' => array_map( 'strval', $product->get_category_ids() ),
			'tag_ids'      => array_map( 'strval', $product->get_tag_ids() ),
		];

		$instance = new self( $args );
		return $instance;
	}
}
