<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Product\Model;

/**
 * 促銷、交叉銷售相關 DTO
 */
final class Sales extends DTO {

	/** @var array<string> $upsell_ids 交叉銷售商品ID */
	public array $upsell_ids;

	/** @var array<string> $cross_sell_ids 交叉銷售商品ID */
	public array $cross_sell_ids;

	/**
	 * 取得實例
	 *
	 * @param \WC_Product $product 商品
	 */
	public static function instance( $product ): self {
		$args = [
			'upsell_ids'     => array_map( 'strval', $product?->get_upsell_ids() ),
			'cross_sell_ids' => array_map( 'strval', $product?->get_cross_sell_ids() ),
		];

		$instance = new self( $args );
		return $instance;
	}
}
