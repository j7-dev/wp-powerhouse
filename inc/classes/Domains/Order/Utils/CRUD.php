<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Order\Utils;

use Automattic\WooCommerce\Admin\Overrides\OrderRefund;
use J7\Powerhouse\Domains\User\Utils\CRUD as UserCRUD;
use Automattic\WooCommerce\Admin\API\Reports\Customers\Query as CustomersQuery;


/** Order CRUD */
abstract class CRUD {

	const TEMPLATE = '';

	/**TODO
	 * Create a new post
	 *
	 * @see https://developer.wordpress.org/reference/functions/wp_insert_post/
	 *
	 * 簡單的新增，沒有太多參數，所以不使用 Converter
	 *
	 * @param array<string, mixed> $args Arguments.
	 *
	 * @return int|\WP_Error
	 */
	public static function create_post( array $args = [] ): int|\WP_Error {
		$default_args = [
			'post_title'    => '新文章',
			'post_name'     => 'new',
			'menu_order'    => -1, // 這樣在 sortable list 才會在最上方
			'post_status'   => 'publish',
			'post_author'   => \get_current_user_id(),
			'page_template' => self::TEMPLATE,
		];

		$args = \wp_parse_args( $args, $default_args );

		$args = \apply_filters('powerhouse/post/create_post_args', $args);

		/** @var array{ID?: int, post_author?: int, post_date?: string, post_date_gmt?: string, post_content?: string, post_content_filtered?: string, post_title?: string, post_excerpt?: string, ...} $args */
		return \wp_insert_post($args);
	}

	/**
	 * Format Order details
	 * WC_Order 轉 array
	 *
	 * @param \WC_Order|OrderRefund $order             order.
	 *
	 * @return array{
	 *  id: string,
	 *  order_number: string,
	 *  customer: array{
	 *    id: string,
	 *    name: string,
	 *    email: string,
	 *  },
	 *  items: array{
	 *    id: string,
	 *    name: string,
	 *    quantity: int,
	 *    price: string,
	 *  }[],
	 *  date_created: string,
	 *  date_modified: string,
	 *  status: string,
	 *  total: string,
	 *  total_discount: string,
	 *  payment_method_title: string,
	 *  payment_complete: boolean,
	 *  date_paid: string,
	 *  created_via: string,
	 * }
	 */
	public static function format_order_details( \WC_Order|OrderRefund $order, $with_details = false ) {
		if (!( $order instanceof \WC_Order )) {
			return null;
		}

		/** @var \WC_Order_Item_Product[] $items */
		$items       = $order->get_items();
		$items_array = [];
		foreach ($items as $item) {
			@[
				'product_id' => $product_id,
				'variation_id' => $variation_id,
			] = $item->get_data();

			$product_id    = $variation_id ?: $product_id;
			$product       = \wc_get_product( $product_id );
			$items_array[] = array_merge(
				$item->get_data(),
				[
					'image' => \wp_get_attachment_url( $product->get_image_id() ),
				]
			);
		}

		// $meta_keys_array = self::get_meta_keys_array($post, $meta_keys);

		$customer               = UserCRUD::format_user_record( $order->get_customer_id() ) ?? [];
		$customer['ip_address'] = $order->get_customer_ip_address();

		$base_array = [
			'id'                    => (string) $order->get_id(),
			'order_number'          => $order->get_order_number(),
			'customer'              => $customer,
			'items'                 => $items_array,
			'date_created'          => $order->get_date_created()?->date( 'Y-m-d H:i' ),
			'date_modified'         => $order->get_date_modified()?->date( 'Y-m-d H:i' ),
			'status'                => $order->get_status(),
			'formatted_order_total' => $order->get_formatted_order_total(),
			'payment_method_title'  => $order->get_payment_method_title(),
			'payment_complete'      => $order->payment_complete(),
			'date_paid'             => $order->get_date_paid()?->date( 'Y-m-d H:i' ),
			'created_via'           => $order->get_created_via(),
			'edit_url'              => $order->get_edit_order_url(),

			'shipping_total'        => (float) $order->get_shipping_total(), // 運費合計
			'shipping_method'       => $order->get_shipping_method(), // 運送方式
			'subtotal'              => $order->get_subtotal(), // 商品小計
			'total_discount'        => $order->get_total_discount(), // 折扣合計
			'total_fees'            => $order->get_total_fees(), // 其他費用合計
			'total_tax'             => (float) $order->get_total_tax(), // 稅金合計
			'total'                 => $order->get_total(), // 訂單總金額

		];

		if (!$with_details) {
			return $base_array;
		}

		$order_notes = self::get_order_notes($order);

		$formatted_array = array_merge(
			$base_array,
			[
				'order_notes' => $order_notes,
			],
			Info::to_order_array( $order->get_id() ),
			// $meta_keys_array
		);

		// @phpstan-ignore-next-line
		return $formatted_array;
	}

	/**
	 * Get order notes
	 *
	 * @param \WC_Order $order order.
	 *
	 * @return array{id: string, date_created: string, content: string, customer_note: string, added_by: string, order_id: string}[]
	 */
	public static function get_order_notes( \WC_Order $order ): array {
		$order_notes           = \wc_get_order_notes(
			[
				'order_id' => $order->get_id(),
			]
			);
		$formatted_order_notes = [];
		foreach ($order_notes as $order_note) {
			$formatted_order_notes[] = [
				'id'            => $order_note->id,
				'date_created'  => $order_note->date_created?->date('Y-m-d H:i'),
				'content'       => \wpautop($order_note->content),
				'customer_note' => $order_note->customer_note,
				'added_by'      => $order_note->added_by,
				'order_id'      => $order_note->order_id,
			];
		}

		$formatted_order_notes[] = [
			'id'            => $order->get_id(),
			'date_created'  => $order->get_date_created()?->date('Y-m-d H:i'),
			'content'       => '訂單創建',
			'customer_note' => false,
			'added_by'      => 'system',
			'order_id'      => 'N/A',
		];

		return $formatted_order_notes;
	}

	/**TODO
	 * 取得 meta keys array
	 *
	 * @param \WP_Post      $post 文章.
	 * @param array<string> $meta_keys 要暴露出來的 meta keys.
	 * @return array<string, mixed>
	 */
	public static function get_meta_keys_array( \WP_Post $post, array $meta_keys = [] ): array {
		$meta_keys_array = [];
		foreach ($meta_keys as $meta_key) {
			$meta_keys_array[ $meta_key ] = \get_post_meta( $post->ID, $meta_key, true );
		}

		// 可以改寫 meta_keys
		// @phpstan-ignore-next-line
		return \apply_filters( 'powerhouse/post/get_meta_keys_array', $meta_keys_array, $post );
	}



	/**TODO
	 * Converter 轉換器
	 * 把 key 轉換/重新命名，將 前端傳過來的欄位轉換成 wp_update_post 能吃的參數
	 *
	 * 前端圖片欄位就傳 'image_ids' string[] 就好
	 *
	 * @param array{id?: string, depth?: int, name?: string, slug?: string, description?: string, short_description?: string, status?: string, category_ids?: string[], tag_ids?: string[], parent_id?: string} $args    Arguments.
	 *
	 * @return array{ID?: string, post_title?: string, post_name?: string, post_content?: string, post_excerpt?: string, post_status?: string, post_category?: string[], tags_input?: string[], post_parent?: string}
	 */
	public static function converter( array $args ): array {

		unset($args['id']); // 不存 id
		unset($args['depth']); // 不存 depth

		$fields_mapper = [
			'id'                => 'ID',
			'name'              => 'post_title',
			'slug'              => 'post_name',
			'description'       => 'post_content',
			'short_description' => 'post_excerpt',
			'status'            => 'post_status',
			'category_ids'      => 'post_category',
			'tag_ids'           => 'tags_input',
			'parent_id'         => 'post_parent',
		];

		$formatted_args = [];
		foreach ($args as $key => $value) {
			if (in_array($key, array_keys($fields_mapper), true)) {
				$formatted_args[ $fields_mapper[ $key ] ] = $value;
			} else {
				$formatted_args[ $key ] = $value;
			}
		}

		/** @var array{ID?: string, post_title?: string, post_name?: string, post_content?: string, post_excerpt?: string, post_status?: string, post_category?: string[], tags_input?: string[], post_parent?: string} $formatted_args */
		return $formatted_args;
	}

	/**TODO
	 * Update a post
	 *
	 * @param string|int           $id   post id.
	 * @param array<string, mixed> $args Arguments.
	 *
	 * @return integer|\WP_Error
	 */
	public static function update_post( string|int $id, array $args ): int|\WP_Error {
		$default_args = [
			'ID' => $id,
		];

		$args = \wp_parse_args( $args, $default_args );

		/** @var array{ID?: int, post_author?: int, post_date?: string, post_date_gmt?: string, post_content?: string, post_content_filtered?: string, post_title?: string, post_excerpt?: string, ...} $args */
		$update_result = \wp_update_post($args);

		return $update_result;
	}


	/**TODO
	 * 分離參數
	 * 會從前端傳入 'meta_keys', 'with_description', 'depth', 'recursive_args' 等 array 參數
	 * 這個 function 會將這些參數分離出來，給後續 function 使用
	 *
	 * @param array<string, mixed> $args 參數.
	 * @return array{args: array<string, mixed>, meta_keys: array<string>, with_description: bool, depth: int, recursive_args: ?array<string, mixed>}
	 */
	public static function handle_args( array $args ): array {
		$default = [
			'meta_keys'        => [],
			'with_description' => false,
			'depth'            => 0,
			'recursive_args'   => null,
		];

		$args = \wp_parse_args( $args, $default );

		[
			'meta_keys'        => $meta_keys,
			'with_description' => $with_description,
			'depth'            => $depth,
			'recursive_args'   => $recursive_args,
		] = $args;

		unset($args['meta_keys']);
		unset($args['with_description']);
		unset($args['depth']);
		unset($args['recursive_args']);

		return [
			'args'             => $args,
			'meta_keys'        => $meta_keys,
			'with_description' => (bool) $with_description,
			'depth'            => $depth,
			'recursive_args'   => $recursive_args,
		];
	}


	/**TODO
	 * Format terms, 例如 分類、標籤, product_cat, product_tag
	 * 只回簡單的欄位，通常是做 options 使用
	 *
	 * @param array<string, mixed> $params Params.
	 *
	 * @return array{id:string, name:string}[]
	 */
	public static function format_terms( array $params = [] ): array {
		// it seems no need to add post_per_page, get_terms will return all terms
		$default_args = [
			'taxonomy'   => 'product_cat',
			'fields'     => 'id=>name',
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		];

		$args = \wp_parse_args(
				$params,
				$default_args,
			);
		/** @var array<int, string> $terms id=>name */
		$terms = \get_terms( $args );

		$formatted_terms = [];
		foreach ($terms as $term_id => $term_name) {
			$formatted_terms[] = [
				'id'   => (string) $term_id,
				'name' => $term_name,
			];
		}

		return $formatted_terms;
	}
}
