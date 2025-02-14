<?php
/**
 * 商品如果綁項目權限
 * 相關資料都存在 bind_items_data 這個 post meta 中
 * 此類為 bind_items_data 的單一項目資料
 */

declare ( strict_types=1 );

namespace J7\Powerhouse\Domains\Limit\Models;

/**
 * Class BoundItemData
 */
class BoundItemData extends Limit {

	/**
	 * 項目 id
	 *
	 * @var int
	 */
	public int $id;


	/**
	 * 項目名稱
	 *
	 * @var string
	 */
	public string $name;

	/**
	 * Constructor
	 *
	 * @param int         $item_id 項目 id
	 * @param string      $limit_type 限制類型 'unlimited' | 'fixed' | 'assigned' | 'follow_subscription'
	 * @param int|null    $limit_value 限制值
	 * @param string|null $limit_unit 限制單位 'timestamp' | 'day' | 'month' | 'year'
	 * @param string      $prefix limit 欄位前綴
	 */
	public function __construct( int $item_id, string $limit_type, int|null $limit_value, string|null $limit_unit, string $prefix = '' ) {
		parent::__construct( $limit_type, $limit_value, $limit_unit, $prefix );

		$this->id   = $item_id;
		$this->name = \get_the_title($item_id);
	}


	/**
	 * 轉換為陣列
	 *
	 * @return array{
	 *     id: int,
	 *     name: string,
	 *     limit_type: string,
	 *     limit_value: int|null,
	 *     limit_unit: string|null,
	 * }
	 */
	public function to_array(): array {
		return [
			'id'          => $this->id,
			'name'        => $this->name,
			'limit_type'  => $this->limit_type,
			'limit_value' => $this->limit_value,
			'limit_unit'  => $this->limit_unit,
		];
	}
}
