<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Term\Utils;

use J7\WpUtils\Classes\WP;

/** Term CRUD */
abstract class CRUD {

	/**
	 * Create a new term
	 *
	 * @see https://developer.wordpress.org/reference/functions/wp_insert_term/
	 * @param array<string, mixed> $args Arguments.
	 *
	 * @return int|\WP_Error
	 */
	public static function create_term( array $args = [] ): int|\WP_Error {
		$handled_args = self::handle_args( $args );

		[
			'term'     => $term,
			'taxonomy' => $taxonomy,
			'args'     => $args,
		] = $handled_args;

		[
			'data' => $data,
			'meta_data' => $meta_data,
		] = WP::separator($args);

		/** @var array{term_id: int, term_taxonomy_id: int}|\WP_Error $result */
		$result = \wp_insert_term($term, $taxonomy, $data);

		if (\is_wp_error($result)) {
			return $result;
		}

		$term_id = $result['term_id'];
		foreach ($meta_data as $meta_key => $meta_value) {
			\update_term_meta($term_id, $meta_key, $meta_value);
		}

		return $term_id;
	}

	/**
	 * Sort terms
	 * 改變 term 順序
	 *
	 * @param array{from_tree: array<array{id: string}>, to_tree: array<array{id: string}>} $params Parameters.
	 *
	 * @return true|\WP_Error
	 */
	public static function sort_terms( array $params ): bool|\WP_Error {
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
			$is_new_term = \str_starts_with($to_id, 'new-'); // 用 new- 開頭的 id 是新章節
			$args        = $node;
			unset($args['id']); // 不存 id

			if ($is_new_term) {
				$insert_result = self::create_term($args);
			} else {
				$insert_result = self::update_term($to_id, $args);
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
	 * Update a term
	 *
	 * @param string|int           $term_id   term id.
	 * @param array<string, mixed> $args Arguments.
	 *
	 * @return int|\WP_Error
	 */
	public static function update_term( string|int $term_id, array $args ): int|\WP_Error {

		$handled_args = self::handle_args($args);

		[
			'taxonomy' => $taxonomy,
			'args'     => $args,
		] = $handled_args;

		[
			'data' => $data,
			'meta_data' => $meta_data,
		] = WP::separator($args);

		/** @var array{term_id: int, term_taxonomy_id: int}|\WP_Error $result */
		$result = \wp_update_term($term_id, $taxonomy, $data);

		if (\is_wp_error($result)) {
			return $result;
		}

		foreach ($meta_data as $meta_key => $meta_value) {
			\update_term_meta($term_id, $meta_key, $meta_value);
		}

		return (int) $term_id;
	}

	/**
	 * 取得最上層的 term ID
	 *
	 * @param int    $term_id  term ID.
	 * @param string $taxonomy taxonomy.
	 *
	 * @return int
	 */
	public static function get_top_term_id( int $term_id, string $taxonomy ): int {

		$cache_key   = "top_term_id_{$term_id}";
		$top_term_id = \wp_cache_get( $cache_key );
		if ( $top_term_id ) {
			return (int) $top_term_id;
		}

		$ancestors = \get_ancestors( $term_id, $taxonomy, 'taxonomy' );
		if ( empty( $ancestors ) ) {
			// 如果沒有祖先，自己就是最上層
			return $term_id;
		}
		// 取最後一個
		$top_term_id = $ancestors[ count( $ancestors ) - 1 ];

		\wp_cache_set( $cache_key, $top_term_id );
		return (int) $top_term_id;
	}


	/**
	 * 取得扁平的子孫 term ids，不包含頂層 id
	 * 階層子孫結構都打平
	 *
	 * @param int $term_id 文章 ID.
	 * @return array<int>
	 */
	public static function get_flatten_term_ids( int $term_id, string $taxonomy ): array {
		return \get_terms(
			[
				'taxonomy' => $taxonomy,
				'child_of' => $term_id,
				'fields'   => 'ids',
			]
			);
	}

	/**
	 * 分離參數
	 * 會從前端傳入 'term', 'taxonomy', 等 array 參數
	 * 這個 function 會將這些參數分離出來，給後續 function 使用
	 *
	 * @param array<string, mixed> $args 參數.
	 * @return array{args: array<string, mixed>, term: string, taxonomy: string}
	 */
	public static function handle_args( array $args ): array {
		$default = [
			'term'        => '', // 應該是 term slug
			'taxonomy'    => '',
			'name'        => '',
			'alias_of'    => '',
			'description' => '',
			'parent'      => 0,
			'slug'        => '',
		];

		$args = \wp_parse_args( $args, $default );

		[
			'term'        => $term,
			'taxonomy' => $taxonomy,
		] = $args;

		unset($args['term']);
		unset($args['taxonomy']);

		return [
			'args'     => $args,
			'term'     => $term,
			'taxonomy' => $taxonomy,
		];
	}
}
