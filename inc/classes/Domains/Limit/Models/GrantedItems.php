<?php

declare ( strict_types=1 );

namespace J7\Powerhouse\Domains\Limit\Models;

use J7\Powerhouse\Domains\Limit\Utils\CreateTable;

/**
 * 用戶已授權的項目們
 */
class GrantedItems {

	const CACHE_KEY = 'granted_items';


	/**
	 * 初始化
	 *
	 * @param int    $user_id 用戶ID
	 * @param string $meta_key 元數據鍵名
	 */
	public function __construct( public int $user_id, public string $meta_key = 'expire_date' ) {
	}

	/**
	 * 取得授權的項目們
	 *
	 * @param array<string, string> $where LEFT JOIN wp_posts 的條件，例如指定 post_type 為 product
	 *
	 * @return array{id: string, name: string, expire_date: array{is_subscription: bool, subscription_id: int|null, is_expired: bool, timestamp: int|null}|null}[] $granted_items
	 */
	public function get_granted_items( $where = [] ): array {
		$item_ids      = $this->get_item_ids( $where );
		$granted_items = [];
		foreach ( $item_ids as $item_id ) {

			$granted_item    = new GrantedItem( (int) $item_id, $this->user_id, $this->meta_key );
			$granted_items[] = [
				'id'          => (string) $item_id,
				'name'        => \get_the_title( (int) $item_id ),
				'expire_date' => $granted_item->expire_date?->to_array(),
			];
		}
		return $granted_items;
	}

	/**
	 * 取得項目IDs
	 *
	 * @param array<string, string> $where LEFT JOIN wp_posts 的條件，例如指定 post_type 為 product
	 * @return array<string|int>
	 */
	public function get_item_ids( $where = [] ): array {
		$where_json_string = \wp_json_encode( $where );
		$cache_key         = self::CACHE_KEY . '_' . $this->user_id . '_where_' . $where_json_string;

		$item_ids = \wp_cache_get( $cache_key );

		if ( false !== $item_ids && is_array( $item_ids ) ) {
			/** @var array<string|int> $item_ids */
			return $item_ids;
		}

		global $wpdb;
		$table_name     = $wpdb->prefix . CreateTable::ACCESS_ITEMMETA_TABLE_NAME;
		$wp_posts_table = $wpdb->prefix . 'posts';
		$where_sql      = '';
		if ($where ) {
			foreach ( $where as $key => $value ) {
				$where_sql .= " AND p.{$key} = '$value'";
			}
		}

		$item_ids = $wpdb->get_col(
			\wp_unslash(// phpcs:ignore
			$wpdb->prepare(
			"SELECT post_id
			FROM  %1\$s pm
			LEFT JOIN %2\$s p
			ON pm.post_id = p.ID
			WHERE pm.user_id = %3\$d
			AND pm.meta_key = '%4\$s'
			%5\$s
			",
			$table_name,
			$wp_posts_table,
			$this->user_id,
			$this->meta_key,
			$where_sql
			)
				)
			);

		\wp_cache_set( $cache_key, $item_ids );

		return $item_ids;
	}
}
