<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Product\Model;

use J7\Powerhouse\Domains\Post\Utils\CRUD as PostCRUD;

/**
 * 商品分類、標籤、品牌 DTO
 */
final class Taxonomy extends DTO {

	/** @var array{id:string, name:string}[] $categories 商品分類 */
	public array $categories;

	/** @var array{id:string, name:string}[] $tags 商品標籤 */
	public array $tags;

	/**
	 * 取得實例
	 *
	 * @param \WC_Product $product 商品
	 */
	public static function instance( $product ): self {
		$product_id = $product->get_id();
		$args       = [
			'categories' => PostCRUD::format_terms(
				[
					'taxonomy'   => 'product_cat',
					'object_ids' => $product_id,
				]
				),
			'tags' => PostCRUD::format_terms(
				[
					'taxonomy'   => 'product_tag',
					'object_ids' => $product_id,
				]
				),
		];

		$instance = new self( $args );
		return $instance;
	}
}
