<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Product\Model;

/** 商品詳細資料 DTO */
final class Detail extends DTO {

	/** @var string $description 商品描述 */
	public string $description;

	/** @var string $short_description 商品簡短描述 */
	public string $short_description;

	/**
	 * 取得實例
	 *
	 * @param \WC_Product $product 商品
	 */
	public static function instance( $product ): self {
		$args = [
			'description'       => $product->get_description(),
			'short_description' => $product->get_short_description(),
		];

		$instance = new self($args);
		return $instance;
	}
}
