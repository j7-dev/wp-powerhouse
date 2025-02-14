<?php
/**
 * 商品如果綁項目權限
 * 相關資料都存在 {bound_items_data} 這個 post meta 中
 * 還會把 id 個別存到 {bound_items_data}_ids 這個 post meta 中
 */

declare ( strict_types=1 );

namespace J7\Powerhouse\Domains\Limit\Models;

/**
 * Class BoundItemsData
 */
class BoundItemsData {

	/**
	 * 不一定會有商品 id 因為有時候是從訂單身上拿資料
	 *
	 * @var int|null $product_id 商品 id
	 */
	public int|null $product_id = null;


	/**
	 * @var string $meta_key meta key 預設為 bound_items_data
	 */
	private string $meta_key = 'bound_items_data';

	/**
	 * @var BoundItemData[] $bound_items_data 綁定的課程資料
	 */
	private array $bound_items_data = [];

	/**
	 * Constructor
	 * 從有綁項目權限的商品身上拿 bound_items_data 資料
	 *
	 * @param int    $product_id 商品 id
	 * @param string $meta_key meta key 預設為 bound_items_data
	 */
	public function __construct( int $product_id, string $meta_key = 'bound_items_data' ) {
		/**
		 * @var array<int, array{
		 *     id: int,
		 *     name: string,
		 *     limit_type: string,
		 *     limit_value: int|null,
		 *     limit_unit: string|null,
		 * }> $bound_items_data
		 */
		$bound_items_data = \get_post_meta( $product_id, $meta_key, true ) ?: [];

		$this->product_id = $product_id;
		$this->meta_key   = $meta_key;

		foreach ($bound_items_data as $bind_course_data) {
			$this->bound_items_data[] = new BoundItemData( (int) $bind_course_data['id'], $bind_course_data['limit_type'], (int) $bind_course_data['limit_value'], $bind_course_data['limit_unit'] );
		}
	}

	/**
	 * 取得綁定的項目 ids
	 *
	 * @return array<string>
	 */
	public function get_ids(): array {
		return \wp_list_pluck( $this->get_data(), 'id' );
	}

	/**
	 * 檢查項目是否已經綁定
	 *
	 * @param int $item_id 項目 id
	 * @return bool
	 */
	public function included( int $item_id ): bool {
		return \in_array( $item_id, $this->get_ids() );
	}


	/**
	 * 新增課程資料
	 * 如果原本的資料裡面有這次新增的，那就跳過不動
	 *
	 * @param int   $item_id 項目 id
	 * @param Limit $limit 限制
	 * @return self
	 */
	public function add_item_data( int $item_id, Limit $limit ): self {
		if ($this->included( $item_id )) {
			// 如果原本的資料裡面有這次新增的，那就移除舊有的
			$this->remove_item_data( $item_id );
		}
		// 原本的資料沒有這次新增的，那就新增
		$this->bound_items_data[] = new BoundItemData( $item_id, $limit->limit_type, $limit->limit_value, $limit->limit_unit );

		return $this;
	}

	/**
	 * 更新項目資料
	 *
	 * @param int   $item_id 項目 id
	 * @param Limit $limit 限制
	 * @return self
	 */
	public function update_item_data( int $item_id, Limit $limit ): self {
		$this->remove_item_data( $item_id );
		$this->bound_items_data[] = new BoundItemData( $item_id, $limit->limit_type, $limit->limit_value, $limit->limit_unit );
		return $this;
	}

	/**
	 * 移除項目資料
	 *
	 * @param int $item_id 項目 id
	 * @return self
	 */
	public function remove_item_data( int $item_id ): self {
		/** @var BoundItemData[] $bound_items_data */
		$bound_items_data       = $this->get_data();
		$this->bound_items_data = array_filter( $bound_items_data, fn( $bound_item_data ) => $bound_item_data->id !== $item_id );
		return $this;
	}

	/**
	 * 取得綁定的課程資料
	 *
	 * @param string|null $output 輸出格式 OBJECT | ARRAY_N
	 * @return BoundItemData[]|array<int, array{
	 *     id: int,
	 *     name: string,
	 *     limit_type: string,
	 *     limit_value: int|null,
	 *     limit_unit: string|null,
	 * }>
	 */
	public function get_data( ?string $output = OBJECT ): array {
		$data = [];
		foreach ($this->bound_items_data as $bound_item_data) {
			if ($output === ARRAY_N) {
				$data[] = $bound_item_data->to_array();
			} else {
				$data[] = $bound_item_data;
			}
		}

		return $data;
	}

	/**
	 * 儲存
	 * TODO 改成 $product 操作方法!?
	 */
	public function save(): void {
		if (!$this->product_id) {
			return;
		}
		$bound_items_data = $this->get_data( ARRAY_N );

		// 儲存 array 資料到 post meta
		\update_post_meta( $this->product_id, $this->meta_key, $bound_items_data );

		// 為了方便檢索，所以把 ids 個別存到 post meta
		$ids          = $this->get_ids();
		$ids_meta_key = "{$this->meta_key}_ids";
		\delete_post_meta( $this->product_id, $ids_meta_key );
		foreach ($ids as $id) {
			\add_post_meta( $this->product_id, $ids_meta_key, $id );
		}
	}
}
