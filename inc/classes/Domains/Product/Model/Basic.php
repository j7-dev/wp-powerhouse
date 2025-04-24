<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Product\Model;

use J7\WpUtils\Classes\WP;

/** 商品基本資料 DTO */
final class Basic extends DTO {

	/** @var string $id 商品ID */
	public string $id;

	/** @var string $type 商品類型 */
	public string $type;

	/** @var string $name 商品名稱 */
	public string $name;

	/** @var string $slug 商品別名 */
	public string $slug;

	/** @var array<array{id: string, url: string}> $images 商品圖片 */
	public array $images;

	/** @var string|null $date_created 建立日期 */
	public ?string $date_created;

	/** @var string|null $date_modified 修改日期 */
	public ?string $date_modified;

	/** @var string $status 商品狀態 */
	public string $status;

	/** @var 'yes'|'no' $featured 是否為精選商品 */
	public string $featured;

	/** @var string $catalog_visibility 目錄可見性 */
	public string $catalog_visibility;

	/** @var int $menu_order 選單排序 */
	public int $menu_order;

	/** @var 'yes'|'no' $virtual 是否為虛擬商品 */
	public string $virtual;

	/** @var 'yes'|'no' $downloadable 是否為可下載商品 */
	public string $downloadable;

	/** @var string $permalink 商品永久連結 */
	public string $permalink;

	/** @var string $edit_url 商品編輯連結 */
	public string $edit_url;

	/** @var string $parent_id 父商品ID */
	public string $parent_id;

	/**
	 * 取得實例
	 *
	 * @param \WC_Product $product 商品
	 */
	public static function instance( $product ): self {

		// 組合 $images
		$image_id          = $product->get_image_id();
		$gallery_image_ids = $product->get_gallery_image_ids();
		$image_ids         = [ $image_id, ...$gallery_image_ids ];
		$images            = [];
		foreach ($image_ids as $image_id) {
			$image_info = WP::get_image_info($image_id);
			/** @var array{id: string, url: string}|null $image_info */
			if ($image_info) {
				$images[] = $image_info;
			}
		}

		$args = [
			'id'                 => (string) $product->get_id(),
			'type'               => $product->get_type(),
			'name'               => $product->get_name(),
			'slug'               => $product->get_slug(),
			'images'             => $images,
			'date_created'       => $product->get_date_created()?->date( 'Y-m-d H:i:s' ),
			'date_modified'      => $product->get_date_modified()?->date( 'Y-m-d H:i:s' ),
			'status'             => $product->get_status(),
			'featured'           => \wc_bool_to_string( $product->get_featured() ),
			'catalog_visibility' => $product->get_catalog_visibility(),
			'menu_order'         => $product->get_menu_order(),
			'virtual'            => \wc_bool_to_string( $product->get_virtual() ),
			'downloadable'       => \wc_bool_to_string( $product->get_downloadable() ),
			'permalink'          => $product->get_permalink(),
			'edit_url'           => \get_edit_post_link( $product->get_id(), '&' ),
			'parent_id'          => $product->get_parent_id() ? (string) $product->get_parent_id() : '',
		];

		$instance = new self($args);
		return $instance;
	}
}
