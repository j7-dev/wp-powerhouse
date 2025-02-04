<?php
/**
 * Post Utils
 */

declare(strict_types=1);

namespace J7\Powerhouse\Resources\Post;

use J7\WpUtils\Classes\WP;

/**
 * Class Utils
 */
abstract class Utils {

	const TEMPLATE = '';

	/**
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
		$args['post_title']    = $args['post_title'] ?? '新文章';
		$args['post_name']     = $args['post_name'] ?? 'new';
		$args['menu_order']    = $args['menu_order'] ?? -1; // 這樣在 sortable list 才會在最上方
		$args['post_status']   = 'publish';
		$args['post_author']   = \get_current_user_id();
		$args['page_template'] = self::TEMPLATE;

		/** @var array{ID?: int, post_author?: int, post_date?: string, post_date_gmt?: string, post_content?: string, post_content_filtered?: string, post_title?: string, post_excerpt?: string, ...} $args */
		return \wp_insert_post($args);
	}

	/**
	 * Format Post details
	 * WP_Post 轉 array
	 *
	 * @param \WP_Post                  $post             Post.
	 * @param bool                      $with_description With description.
	 * @param int                       $depth            Depth.
	 * @param array<string, mixed>|null $recursive_args 遞迴參數 預設 null 不遞迴.
	 * @param array<string>             $meta_keys        要暴露出來的 meta keys.
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
	public static function format_post_details(
		\WP_Post $post,
		?bool $with_description = false,
		?int $depth = 0,
		?array $recursive_args = null,
		?array $meta_keys = []
	) {
		$date_created  = $post->post_date;
		$date_modified = $post->post_modified;

		$image_id = \get_post_thumbnail_id($post->ID);
		/** @var int[] $image_ids */
		$image_ids = $image_id ? [ $image_id ] : [];
		$images    = [];
		foreach ($image_ids as $image_id) {
			$image_info = WP::get_image_info($image_id);
			if ($image_info) {
				$images[] = $image_info;
			}
		}

		$description_array = $with_description ? [
			'description'       => $post->post_content,
			'short_description' => $post->post_excerpt,
		] : [];

		$children        = self::get_recursive_array($post, $recursive_args, (int) $depth, $meta_keys);
		$meta_keys_array = self::get_meta_keys_array($post, $meta_keys);

		$base_array = [
			// Get Product General Info
			'id'            => (string) $post->ID,
			'depth'         => $depth,
			'name'          => $post->post_title,
			'slug'          => $post->post_name,
			'date_created'  => $date_created,
			'date_modified' => $date_modified,
			'status'        => $post->post_status,
			'menu_order'    => (int) $post->menu_order,
			'permalink'     => \get_permalink($post->ID),
			'category_ids'  => [],
			'tag_ids'       => [],
			'images'        => $images,
			'parent_id'     => (string) $post->post_parent,
		];

		$formatted_array = array_merge(
			$description_array,
			$base_array,
			$children,
			$meta_keys_array
		);

		// @phpstan-ignore-next-line
		return $formatted_array;
	}

	/**
	 * 取得 meta keys array
	 *
	 * @param \WP_Post      $post 文章.
	 * @param array<string> $meta_keys 要暴露出來的 meta keys.
	 * @return array<string, mixed>
	 */
	public static function get_meta_keys_array( \WP_Post $post, ?array $meta_keys = [] ): array {
		if (!$meta_keys) {
			return [];
		}

		$meta_keys_array = [];
		foreach ($meta_keys as $meta_key) {
			$meta_keys_array[ $meta_key ] = \get_post_meta( $post->ID, $meta_key, true );
		}

		// 可以改寫 meta_keys
		// @phpstan-ignore-next-line
		return \apply_filters( 'powerhouse/post/get_meta_keys_array', $meta_keys_array, $post );
	}

	/**
	 * 取得遞迴文章 array
	 *
	 * @param \WP_Post                  $post 文章.
	 * @param array<string, mixed>|null $recursive_args 遞迴參數.
	 * @param int                       $depth 深度.
	 * @param array<string>             $meta_keys 要暴露出來的 meta keys.
	 * @return array{children: array<mixed>}|array{}
	 */
	public static function get_recursive_array( \WP_Post $post, ?array $recursive_args = null, int $depth = 0, ?array $meta_keys = [] ): array {
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
				'ID'         => 'ASC',
				'date'       => 'ASC',
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

		return !!$children_to_array ? [
			'children' => $children_to_array,
		] : [];
	}

	/**
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

	/**
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

	/**
	 * Update a post
	 *
	 * @param string               $id   post id.
	 * @param array<string, mixed> $args Arguments.
	 *
	 * @return integer|\WP_Error
	 */
	public static function update_post( string $id, array $args ): int|\WP_Error {

		$args['ID']            = $id;
		$args['post_title']    = $args['post_title'] ?? '新文章';
		$args['post_status']   = $args['status'] ?? 'publish';
		$args['post_author']   = \get_current_user_id();
		$args['page_template'] = self::TEMPLATE;

		/** @var array{ID?: int, post_author?: int, post_date?: string, post_date_gmt?: string, post_content?: string, post_content_filtered?: string, post_title?: string, post_excerpt?: string, ...} $args */
		$update_result = \wp_update_post($args);

		return $update_result;
	}

	/**
	 * 取得最上層的文章 ID
	 *
	 * @param int $post_id 文章 ID.
	 * @return int|null
	 */
	public static function get_top_post_id( int $post_id ): int|null {
		$ancestors = \get_post_ancestors( $post_id );
		if ( empty( $ancestors ) ) {
			return null;
		}
		// 取最後一個
		return $ancestors[ count( $ancestors ) - 1 ] ?? null;
	}


	/**
	 * 取得扁平的子孫 post ids
	 * 階層子孫結構都打平
	 *
	 * @param int $post_id 文章 ID.
	 * @return array<int>
	 */
	public static function get_flatten_post_ids( int $post_id ): array {
		$post = \get_post( $post_id );
		if ( !$post ) {
			return [];
		}
		/** @var \WP_Post $post */
		$post_array = self::format_post_details( $post, false, 0, [], [] );

		if (!is_array($post_array['children'] ?? null)) {
			return [];
		}

		$flatten_post_ids = [];
		foreach ($post_array['children'] as $child) {
			$flatten_post_ids[] = (int) $child['id'];
			if (is_array($child['children'] ?? null)) { // @phpstan-ignore-line
				$flatten_post_ids = [
					...$flatten_post_ids,
					...self::get_flatten_post_ids( (int) $child['id'] ),
				];
			}
		}
		return $flatten_post_ids;
	}
}
