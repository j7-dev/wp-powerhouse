<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Product\Model;

use J7\Powerhouse\Domains\Product\Utils\CRUD;

/** 商品價格 DTO */
final class Price extends DTO {
	/** @var string $price_html 價格 HTML 字串 */
	public string $price_html;

	/** @var string $regular_price 商品原價 */
	public string $regular_price;

	/** @var string $sale_price 商品特價 */
	public string $sale_price;

	/** @var bool $on_sale 是否特價中 */
	public bool $on_sale;

	/** @var array<string> $sale_date_range 特價日期範圍 */
	public array $sale_date_range;

	/** @var int $date_on_sale_from 特價開始日期 */
	public int $date_on_sale_from;

	/** @var int $date_on_sale_to 特價結束日期 */
	public int $date_on_sale_to;

	/** @var int $total_sales 總銷售量 */
	public int $total_sales;

	/**
	 * 取得實例
	 *
	 * @param \WC_Product $product 商品
	 */
	public static function instance( $product ): self {

		$price_html = CRUD::get_price_html( $product );

		// 優惠日期 [timestamp, timestamp]
		$sale_date_range = [ (int) $product->get_date_on_sale_from()?->getTimestamp(), (int) $product->get_date_on_sale_to()?->getTimestamp() ];

		$args = [
			'price_html'        => $price_html,
			'regular_price'     => $product->get_regular_price(),
			'sale_price'        => $product->get_sale_price(),
			'on_sale'           => $product->is_on_sale(),
			'sale_date_range'   => $sale_date_range,
			'date_on_sale_from' => $sale_date_range[0],
			'date_on_sale_to'   => $sale_date_range[1],
			'total_sales'       => $product->get_total_sales(),
		];

		$instance = new self( $args );
		return $instance;
	}
}
