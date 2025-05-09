<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Product\Model;

/** 尺寸相關 DTO */
final class Size extends DTO {

	/** @var string $length 尺寸長度 */
	public string $length;

	/** @var string $width 尺寸寬度 */
	public string $width;

	/** @var string $height 尺寸高度 */
	public string $height;

	/** @var string $weight 重量 */
	public string $weight;

	/** @var string|null $shipping_class_id 運送類別ID */
	public string|null $shipping_class_id;

	/** @var string $sku 商品編號 */
	public string $sku;

	/** @var string $_global_unique_id GTIN、UPC、EAN 或 ISBN */
	public string $_global_unique_id;

	/**
	 * 取得實例
	 *
	 * @param \WC_Product $product 商品
	 */
	public static function instance( $product ): self {
		$args = [
			'length'            => $product->get_length(),
			'width'             => $product->get_width(),
			'height'            => $product->get_height(),
			'weight'            => $product->get_weight(),
			'shipping_class_id' => (string) $product->get_shipping_class_id() ?: null,
			'sku'               => $product->get_sku(),
			'_global_unique_id' => $product->get_meta( '_global_unique_id' ),
		];

		$instance = new self( $args );
		return $instance;
	}
}
