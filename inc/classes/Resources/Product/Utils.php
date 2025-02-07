<?php
/**
 * Product Utils
 */

declare(strict_types=1);

namespace J7\Powerhouse\Resources\Product;

use J7\WpUtils\Classes\WP;
use J7\Powerhouse\Resources\Post\Utils as PostUtils;

/**
 * Class Utils
 */
abstract class Utils {

	/**
	 * 新增商品
	 * 預設創建簡單商品
	 *
	 * @param array<string, mixed> $data 資料，可以用 set_ 方法更新
	 * @param array<string, mixed> $meta_data 元資料
	 *
	 * @return int
	 */
	public static function create_product( array $data = [], array $meta_data = [] ): int {
		$product = new \WC_Product_Simple();
		$data    = \wp_parse_args(
			$data,
			[
				'name' => __('new product', 'powerhouse'),
			]
			);
		self::update_product($product, $data, $meta_data);
		return $product->get_id();
	}




	/**
	 * 更新商品
	 *
	 * @param \WC_Product          $product 商品
	 * @param array<string, mixed> $data 資料，可以用 set_ 方法更新
	 * @param array<string, mixed> $meta_data 元資料
	 *
	 * @return void
	 */
	public static function update_product( \WC_Product $product, array $data = [], array $meta_data = [] ): void {
		Helper\Save::data($product, $data );
		Helper\Save::meta_data($product, $meta_data );
	}


	/**
	 * Format product details
	 *
	 * @param int           $product_id  Product ID.
	 * @param bool          $with_description 是否要包含 description.
	 * @param array<string> $meta_keys 要暴露的前端 meta key.
	 *
	 * @return array{
	 *  id: string,
	 *  type: string,
	 *  name: string,
	 *  slug: string,
	 *  date_created: string,
	 *  date_modified: string,
	 *  status: string,
	 *  featured: bool,
	 *  catalog_visibility: string,
	 *  sku: string,
	 *  menu_order: int,
	 *  virtual: bool,
	 *  downloadable: bool,
	 *  permalink: string,
	 *  price_html: string,
	 *  regular_price: float,
	 *  sale_price: float,
	 *  on_sale: bool,
	 *  sale_date_range: array{0: int, 1: int},
	 *  date_on_sale_from: int,
	 *  date_on_sale_to: int,
	 *  total_sales: int,
	 *  stock: int,
	 *  stock_status: string,
	 *  manage_stock: bool,
	 *  stock_quantity: int,
	 *  backorders: string,
	 *  backorders_allowed: bool,
	 *  backordered: bool,
	 *  low_stock_amount: int|null,
	 *  upsell_ids: array<string>,
	 *  cross_sell_ids: array<string>,
	 *  attributes: array<string, mixed>,
	 *  categories: array<string, mixed>,
	 *  tags: array<string, mixed>,
	 *  images: array<string, mixed>,
	 *  parent_id: string,
	 *  description?: string,
	 *  short_description?: string,
	 *  _subscription_price?: string,
	 *  _subscription_period?: string,
	 *  _subscription_period_interval?: string,
	 *  _subscription_length?: string,
	 *  _subscription_sign_up_fee?: string,
	 *  _subscription_trial_length?: string,
	 *  _subscription_trial_period?: string,
	 *  children?: array<mixed>,
	 *  ...
	 * }|null
	 */
	public static function format_product_details(
		int $product_id,
		bool $with_description = false,
		array $meta_keys = []
	): array|null {
		$product = \wc_get_product( $product_id );

		if ( ! ( $product instanceof \WC_Product ) ) {
			return null;
		}

		$date_created  = $product->get_date_created();
		$date_modified = $product->get_date_modified();

		// 組合 $images
		$image_id          = $product->get_image_id();
		$gallery_image_ids = $product->get_gallery_image_ids();
		$image_ids         = [ $image_id, ...$gallery_image_ids ];
		$images            = [];
		foreach ($image_ids as $image_id) {
			$image_info = WP::get_image_info($image_id);
			if ($image_info) {
				$images[] = $image_info;
			}
		}

		// 判斷需不需要暴露 description
		$description_array = $with_description ? [
			'description'       => $product->get_description(),
			'short_description' => $product->get_short_description(),
		] : [];

		// 取得最低庫存警告數量
		$low_stock_amount = ( '' === $product->get_low_stock_amount() ) ? null : $product->get_low_stock_amount();

		// 將變體加入倒 children
		$variation_ids = $product->get_children(); // get variations
		$children      = [];
		if ( $variation_ids ) {
			$variation_products = array_map( 'wc_get_product', $variation_ids );
			$variation_products = array_filter($variation_products);
			$children_details   = array_values(array_map( [ __CLASS__, 'format_product_details' ], $variation_products )); // @phpstan-ignore-line
			$children           = [
				'children' => $children_details,
			];
		}

		// 暴露額外的 meta keys
		$meta_keys_array         = self::get_meta_keys_array($product, $meta_keys);
		$subscription_data_array = Helper\Subscription::get_subscription_meta_data($product);

		// 組合商品屬性 $attributes_arr
		$attributes     = $product->get_attributes(); // get attributes object
		$attributes_arr = [];
		foreach ( $attributes as $key => $attribute ) {
			if ( $attribute instanceof \WC_Product_Attribute ) {
				$attributes_arr[] = [
					'name'     => \wc_attribute_label( $attribute?->get_name() ),
					'options'  => $attribute?->get_options(),
					'position' => $attribute?->get_position(),
				];
			}

			if ( is_string( $key ) && is_string( $attribute ) ) {
				$attributes_arr[ urldecode( $key ) ] = $attribute;
			}
		}

		$price_html = self::get_price_html( $product );

		// 優惠日期 [timestamp, timestamp]
		$sale_date_range = [ (int) $product->get_date_on_sale_from()?->getTimestamp(), (int) $product->get_date_on_sale_to()?->getTimestamp() ];

		$base_array = [
			// Get Product General Info
			'id'                 => (string) $product_id,
			'type'               => $product->get_type(),
			'name'               => $product->get_name(),
			'slug'               => $product->get_slug(),
			'date_created'       => $date_created?->date( 'Y-m-d H:i:s' ),
			'date_modified'      => $date_modified?->date( 'Y-m-d H:i:s' ),
			'status'             => $product->get_status(),
			'featured'           => $product->get_featured(),
			'catalog_visibility' => $product->get_catalog_visibility(),
			'sku'                => $product->get_sku(),
			'menu_order'         => $product->get_menu_order(),
			'virtual'            => $product->get_virtual(),
			'downloadable'       => $product->get_downloadable(),
			'permalink'          => \get_permalink( $product_id ),

			// Get Product Prices
			'price_html'         => $price_html,
			'regular_price'      => $product->get_regular_price(),
			'sale_price'         => $product->get_sale_price(),
			'on_sale'            => $product->is_on_sale(),
			'sale_date_range'    => $sale_date_range,
			'date_on_sale_from'  => $sale_date_range[0],
			'date_on_sale_to'    => $sale_date_range[1],
			'total_sales'        => $product->get_total_sales(),

			// Get Product Stock
			'stock'              => $product->get_stock_quantity(),
			'stock_status'       => $product->get_stock_status(),
			'manage_stock'       => $product->get_manage_stock(),
			'stock_quantity'     => $product->get_stock_quantity(),
			'backorders'         => $product->get_backorders(),
			'backorders_allowed' => $product->backorders_allowed(),
			'backordered'        => $product->is_on_backorder(),
			'low_stock_amount'   => $low_stock_amount,

			// Get Linked Products
			'upsell_ids'         => array_map( 'strval', $product?->get_upsell_ids() ),
			'cross_sell_ids'     => array_map( 'strval', $product?->get_cross_sell_ids() ),

			// Get Product Variations and Attributes
			'attributes'         => $attributes_arr,

			// Get Product Taxonomies
			'categories'         => PostUtils::format_terms(
				[
					'taxonomy'   => 'product_cat',
					'object_ids' => $product_id,
				]
				),
			'tags'               => PostUtils::format_terms(
				[
					'taxonomy'   => 'product_tag',
					'object_ids' => $product_id,
				]
				),

			// Get Product Images
			'images'             => $images,
			'parent_id'          => (string) $product->get_parent_id(),
		];

		// @phpstan-ignore-next-line
		return array_merge(
			$base_array,
			$description_array,
			$subscription_data_array,
			$children,
			$meta_keys_array,
		);
	}

	/**
	 * 取得 meta keys array
	 *
	 * @param \WC_Product   $product 商品.
	 * @param array<string> $meta_keys 要暴露出來的 meta keys.
	 * @return array<string, mixed>
	 */
	public static function get_meta_keys_array( \WC_Product $product, array $meta_keys = [] ): array {
		$meta_keys_array = [];
		foreach ($meta_keys as $meta_key) {
			$meta_keys_array[ $meta_key ] = $product->get_meta( $meta_key );
		}

		// 可以改寫 meta_keys
		// @phpstan-ignore-next-line
		return \apply_filters( 'powerhouse/product/get_meta_keys_array', $meta_keys_array, $product );
	}



	/**
	 * 取得產品價格 HTML。
	 *
	 * @param \WC_Product $product WooCommerce 產品實例。
	 *
	 * @return string 產品價格的 HTML 字串。
	 */
	public static function get_price_html( \WC_Product $product ): string {
		$product_type = $product->get_type();

		return match ($product_type) {
			'subscription' => Helper\Subscription::get_price_html($product),
			'variable-subscription' => '',
			default => $product->get_price_html(),
		};
	}


	/**
	 * Get Max Min Price
	 * TODO 可能可以改 SQL 查詢更快
	 *
	 * @return array{max_price:float, min_price:float}
	 */
	public static function get_max_min_prices(): array {
		$transient_key = 'max_min_prices';

		$max_min_prices = \get_transient( $transient_key );

		if ( false !== $max_min_prices ) {
			/** @var array{max_price:float, min_price:float} */
			return $max_min_prices;
		}
		// 獲取最高價格的商品
		$max_price_products = \wc_get_products(
			[
				'order'    => 'DESC', // 遞減排序
				'orderby'  => 'meta_value_num',
				'meta_key' => '_price',
				'limit'    => 1,         // 僅獲取一個結果
				'status'   => 'publish', // 僅包含已發佈的商品
			]
		);
		$max_price          = 0;
		if ( ! empty( $max_price_products ) ) {
			$max_price_product = reset( $max_price_products );     // 獲取第一個元素
			$max_price         = $max_price_product?->get_price(); // 獲取最高價格
		}

		// 獲取最低價格的商品
		$min_price_products = \wc_get_products(
			[
				'order'    => 'ASC', // 遞增排序
				'orderby'  => 'meta_value_num',
				'meta_key' => '_price',
				'limit'    => 1,         // 僅獲取一個結果
				'status'   => 'publish', // 僅包含已發佈的商品
			]
		);

		$min_price = 0;
		if ( ! empty( $min_price_products ) ) {
			$min_price_product = reset( $min_price_products );     // 獲取第一個元素
			$min_price         = $min_price_product?->get_price(); // 獲取最低價格
		}

		$max_min_prices = [
			'max_price' => (float) $max_price,
			'min_price' => (float) $min_price,
		];

		// @phpstan-ignore-next-line
		\set_transient( $transient_key, $max_min_prices, 1 * DAY_IN_SECONDS );

		return $max_min_prices;
	}
}
