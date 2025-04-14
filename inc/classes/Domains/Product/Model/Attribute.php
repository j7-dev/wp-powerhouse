<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Product\Model;

/**
 * 商品屬性 DTO
 * 注意：屬性不一定都有變體，屬性也不一定全都是 taxonomy
 * 全局範圍的商品屬性才會是 taxonomy
 */
final class Attribute extends DTO {

	/** @var array<array{
	 * name: string,
	 * variation: bool,
	 * visible: bool,
	 * options: array<array{value: string, label: string}>,
	 * position: int,
	 * }> | array<string, string> $attributes
	 *
	 * 如果是可變商品就會拿到可選的規格 array
	 * 如果是變體，會拿到 array<string, string>
	 * */
	public array $attributes;

	/**
	 * 取得實例
	 *
	 * @param \WC_Product $product 商品
	 */
	public static function instance( \WC_Product $product ): self {
		// 組合商品屬性 $attributes_arr
		$attributes     = $product->get_attributes(); // get attributes object
		$attributes_arr = [];
		foreach ( $attributes as $key => $attribute ) {
			// 如果是 "可變商品" 會顯示選項
			if ( $attribute instanceof \WC_Product_Attribute ) {

				$attributes_arr[] = [
					'name'      => \wc_attribute_label( $attribute?->get_name() ),
					'variation' => $attribute?->get_variation(), // 是否用於變體
					'visible'   => $attribute?->get_visible(), // 是否可見
					'options'   => self::get_options( $attribute ),
					'position'  => $attribute?->get_position(),
				];
			}

			// 如果是 "變體" 會顯示 屬性 => 屬性值 array
			if ( is_string( $key ) && is_string( $attribute ) ) {
				$attributes_arr[ urldecode( $key ) ] = $attribute;
			}
		}

		$args = [
			'attributes' => $attributes_arr,
		];

		$instance = new self( $args );
		return $instance;
	}

	/**
	 * 取得屬性選項
	 *
	 * @param \WC_Product_Attribute $attribute 商品屬性
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_options( \WC_Product_Attribute $attribute ): array {
		$options = [];
		// 先判斷是不是 taxonomy
		if ( !$attribute->is_taxonomy() ) {
			// 如果不是，就用 slugs 來組 slug=>slug
			$slugs = $attribute->get_slugs();

			foreach ( $slugs as $slug ) {
				$options[] = [
					'value' => $slug,
					'label' => $slug,
				];
			}
			return $options;
		}

		// 如果是 taxonomy，則用 term 來組 id=>name
		$terms = $attribute->get_terms();
		foreach ( $terms as $term ) {
			$options[] = [
				'value' => $term->term_id,
				'label' => $term->name,
			];
		}

		return $options;
	}
}
