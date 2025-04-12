<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Product\Model;

use J7\WpUtils\Classes\DTO;

/**
 * 商品基本資料 DTO
 */
abstract class Basic extends DTO {

	/** @var string $id 商品ID */
	public string $id;

	/** @var string $type 商品類型 */
	public string $type;

	/** @var string $name 商品名稱 */
	public string $name;

	/** @var string $slug 商品別名 */
	public string $slug;

	/** @var string|null $date_created 建立日期 */
	public ?string $date_created;

	/** @var string|null $date_modified 修改日期 */
	public ?string $date_modified;

	/** @var string $status 商品狀態 */
	public string $status;

	/** @var bool $featured 是否為精選商品 */
	public bool $featured;

	/** @var string $catalog_visibility 目錄可見性 */
	public string $catalog_visibility;

	/** @var string $sku 商品編號 */
	public string $sku;

	/** @var int $menu_order 選單排序 */
	public int $menu_order;

	/** @var bool $virtual 是否為虛擬商品 */
	public bool $virtual;

	/** @var bool $downloadable 是否為可下載商品 */
	public bool $downloadable;

	/** @var string $permalink 商品永久連結 */
	public string $permalink;

	/**
	 * 建構子
	 *
	 * @param \WC_Product $product 商品
	 */
	public function __construct( $product ) {
		$this->id                 = (string) $product->get_id();
		$this->type               = $product->get_type();
		$this->name               = $product->get_name();
		$this->slug               = $product->get_slug();
		$this->date_created       = $product->get_date_created()?->date( 'Y-m-d H:i:s' );
		$this->date_modified      = $product->get_date_modified()?->date( 'Y-m-d H:i:s' );
		$this->status             = $product->get_status();
		$this->featured           = $product->get_featured();
		$this->catalog_visibility = $product->get_catalog_visibility();
		$this->sku                = $product->get_sku();
		$this->menu_order         = $product->get_menu_order();
		$this->virtual            = $product->get_virtual();
		$this->downloadable       = $product->get_downloadable();
		$this->permalink          = $product->get_permalink();
	}
}
