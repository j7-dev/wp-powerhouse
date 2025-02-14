<?php

namespace J7\Powerhouse\Domains\Limit\Utils;

use J7\WpUtils\Classes\WP;

if ( class_exists( 'AbstractTable' ) ) {
	return;
}

/**
 * 抽象類別，用來創建 table
 */
abstract class CreateTable {

	// 存取限制的 item meta table
	const ACCESS_ITEMMETA_TABLE_NAME = 'ph_access_itemmeta';

	/**
	 * 創建存取限制的 item meta table
	 *
	 * @return void
	 * @throws \Exception Exception.
	 */
	public static function create_itemmeta_table(): void {
		try {
			global $wpdb;
			$table_name      = $wpdb->prefix . self::ACCESS_ITEMMETA_TABLE_NAME;
			$is_table_exists = WP::is_table_exists( $table_name );
			if ( $is_table_exists ) {
				return;
			}

			$wpdb->access_itemmeta = $table_name;

			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
										meta_id bigint(20) NOT NULL AUTO_INCREMENT,
										post_id bigint(20) NOT NULL,
										user_id bigint(20) NOT NULL,
										meta_key varchar(255) DEFAULT NULL,
										meta_value longtext,
										PRIMARY KEY  (meta_id),
										KEY post_id (post_id),
										KEY user_id (user_id),
										KEY meta_key (meta_key(191))
								) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			$result = \dbDelta($sql);
		} catch (\Throwable $th) {
			throw new \Exception($th->getMessage());
		}
	}
}
