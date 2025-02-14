<?php

declare ( strict_types=1 );

namespace J7\Powerhouse\Domains\Limit\Utils;

use J7\Powerhouse\Domains\Limit\Abstracts\MetaCRUD as BaseMetaCRUD;

/**
 *  對 存取限制的 item meta table 的 CRUD 抽象
 */
abstract class MetaCRUD extends BaseMetaCRUD {
	/**
	 * 對應的 table name with wpdb prefix
	 *
	 * @var string
	 */
	public static string $table_name = CreateTable::ACCESS_ITEMMETA_TABLE_NAME;
}
