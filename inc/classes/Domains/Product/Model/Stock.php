<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Product\Model;

/**
 * 商品庫存 DTO
 */
final class Stock extends DTO {

	/** @var int|null $stock 庫存數量 */
	public int|null $stock;

	/** @var string $stock_status 庫存狀態 */
	public string $stock_status;

	/** @var bool $manage_stock 是否管理庫存 */
	public bool $manage_stock;

	/** @var int|null $stock_quantity 庫存數量 */
	public int|null $stock_quantity;

	/** @var string $backorders 允許缺貨訂單 */
	public string $backorders;

	/** @var bool $backorders_allowed 是否允許缺貨訂單 */
	public bool $backorders_allowed;

	/** @var bool $backordered 是否缺貨中 */
	public bool $backordered;

	/** @var int|string $low_stock_amount 低庫存警告數量 */
	public int|string $low_stock_amount;

	/**
	 * 取得實例
	 *
	 * @param \WC_Product $product 商品
	 */
	public static function instance( $product ): self {
		$args = [
			'stock'              => $product->get_stock_quantity(),
			'stock_status'       => $product->get_stock_status(),
			'manage_stock'       => $product->get_manage_stock(),
			'stock_quantity'     => $product->get_stock_quantity(),
			'backorders'         => $product->get_backorders(),
			'backorders_allowed' => $product->backorders_allowed(),
			'backordered'        => $product->is_on_backorder(),
			'low_stock_amount'   => $product->get_low_stock_amount(),
		];

		$instance = new self( $args );
		return $instance;
	}
}
