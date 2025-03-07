<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Order\Utils;

use J7\WpUtils\Classes\WP;
use Automattic\WooCommerce\Admin\Overrides\OrderRefund;
use J7\Powerhouse\Domains\User\Utils\CRUD as UserCRUD;


/** Post CRUD */
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
	 *  type: string,
	 *  depth: int,
	 *  name: string,
	 *  slug: string,
	 *  date_created: string,
	 *  date_modified: string,
	 *  status: string,
	 *  menu_order: int,
	 *  permalink: string,
	 *  category_ids: string[],
	 *  tag_ids: string[],
	 *  images: array<array{id: string, url: string, width: int, height: int, alt: string}>,
	 *  parent_id: string,
	 *  children?: array<array{id: string, type: string, depth: int, name: string, slug: string, date_created: string, date_modified: string, status: string, menu_order: int, permalink: string, category_ids: string[], tag_ids: string[], images: array<array{id: string, url: string, width: int, height: int, alt: string}>, parent_id: string}>,
	 *  description?: string,
	 *  short_description?: string,
	 * }
	 */
	public static function format_order_details( \WC_Order|OrderRefund $order ) {
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

		$customer = UserCRUD::format_user_details( $order->get_customer_id() ) ?? [];
		$customer = \array_merge(
			$customer,
			[
				'ip_address' => $order->get_customer_ip_address(),
			]
			);

		$base_array = [
			'id'                   => $order->get_id(),
			'order_number'         => $order->get_order_number(),
			'customer'             => $customer,
			'items'                => $items_array,
			'date_created'         => $order->get_date_created()?->date( 'Y-m-d H:i' ),
			'date_modified'        => $order->get_date_modified()?->date( 'Y-m-d H:i' ),
			'status'               => $order->get_status(),
			'total'                => $order->get_formatted_order_total(),
			'total_discount'       => $order->get_total_discount(),
			'payment_method_title' => $order->get_payment_method_title(),
			'payment_complete'     => $order->payment_complete(),
			'date_paid'            => $order->get_date_paid()?->date( 'Y-m-d H:i' ),
			'created_via'          => $order->get_created_via(),
		];

		$formatted_array = array_merge(
			$base_array,
			// $meta_keys_array
		);

		// @phpstan-ignore-next-line
		return $formatted_array;
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
	 * 取得遞迴文章 array
	 *
	 * @param \WP_Post                  $post 文章.
	 * @param array<string, mixed>|null $recursive_args 遞迴參數.
	 * @param int                       $depth 深度.
	 * @param array<string>             $meta_keys 要暴露出來的 meta keys.
	 * @return array{children: array<mixed>}|array{}
	 */
	public static function get_recursive_array( \WP_Post $post, array $recursive_args = null, int $depth = 0, array $meta_keys = [] ): array {
		if (null ===$recursive_args) {
			return [];
		}

		$default_args = [
			'post_parent' => $post->ID,
			'post_type'   => $post->post_type,
			'numberposts' => -1,
			'post_status' => 'any',
			'orderby'     => [
				'menu_order' => 'ASC',
				'ID'         => 'DESC',
				'date'       => 'DESC',
			],
		];

		$args = \wp_parse_args( $recursive_args, $default_args );

		/** @var \WP_Post[] $children */
		$children = \get_children($args);

		$children_to_array = [];
		foreach ($children as $child) {
			$children_to_array[] = self::format_post_details(
				$child,
				false,
				$depth + 1,
				$recursive_args,
				$meta_keys
			);
		}

		return $children_to_array ? [
			'children' => $children_to_array,
		] : [];
	}

	/**TODO
	 * Sort posts
	 * 改變文章順序
	 *
	 * @param array{from_tree: array<array{id: string}>, to_tree: array<array{id: string}>} $params Parameters.
	 *
	 * @return true|\WP_Error
	 */
	public static function sort_posts( array $params ): bool|\WP_Error {
		$from_tree = $params['from_tree'] ?? []; // @phpstan-ignore-line
		$to_tree   = $params['to_tree'] ?? []; // @phpstan-ignore-line

		$delete_ids = [];
		foreach ($from_tree as $from_node) {
			$from_id = $from_node['id'];
			$to_node = array_filter($to_tree, fn ( $node ) => $node['id'] === $from_id);
			if (empty($to_node)) {
				$delete_ids[] = $from_id;
			}
		}
		foreach ($to_tree as $node) {
			$to_id       = $node['id'];
			$is_new_post = strpos($to_id, 'new-') === 0; // 用 new- 開頭的 id 是新章節
			$args        = self::converter($node);

			if ($is_new_post) {
				$insert_result = self::create_post($args);
			} else {
				$insert_result = self::update_post($to_id, $args);
			}
			if (\is_wp_error($insert_result)) {
				return $insert_result;
			}
		}

		foreach ($delete_ids as $id) {
			\wp_trash_post( (int) $id );
		}

		return true;
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
