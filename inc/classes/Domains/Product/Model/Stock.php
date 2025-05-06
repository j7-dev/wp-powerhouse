<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Product\Model;

/** 商品庫存 DTO */
final class Stock extends DTO {

	/** @var string $stock_status 庫存狀態 */
	public string $stock_status;

	/** @var 'yes'|'no' $manage_stock 是否管理庫存 */
	public string $manage_stock;

	/** @var int|null $stock_quantity 庫存數量 */
	public int|null $stock_quantity;

	/** @var string $backorders 允許缺貨訂單 */
	public string $backorders;

	/** @var 'yes'|'no' $backorders_allowed 是否允許缺貨訂單 */
	public string $backorders_allowed;

	/** @var 'yes'|'no' $backordered 是否缺貨中 */
	public string $backordered;

	/** @var string $low_stock_amount 低庫存警告數量 */
	public string $low_stock_amount;

	/** @var 'yes'|'no' $sold_individually 是否為單一銷售 */
	public string $sold_individually;

	/**
	 * 取得實例
	 *
	 * @param \WC_Product $product 商品
	 */
	public static function instance( $product ): self {
		$args = [
			'stock_status'       => $product->get_stock_status(),
			'manage_stock'       => \wc_bool_to_string( $product->get_manage_stock() ),
			'stock_quantity'     => $product->get_stock_quantity(),
			'backorders'         => $product->get_backorders(),
			'backorders_allowed' => \wc_bool_to_string( $product->backorders_allowed() ),
			'backordered'        => \wc_bool_to_string( $product->is_on_backorder() ),
			'low_stock_amount'   => (string) $product->get_low_stock_amount(),
			'sold_individually'  => \wc_bool_to_string( $product->get_sold_individually() ),
		];

		$instance = new self( $args );
		return $instance;
	}
}
