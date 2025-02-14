<?php
/**
 * 商品如果綁項目權限
 * 相關資料都存在 bind_items_data 這個 post meta 中
 * 此類為 bind_items_data 的單一項目資料
 */

declare ( strict_types=1 );

namespace J7\Powerhouse\Domains\Limit\Models;

use J7\Powerhouse\Domains\Limit\Utils\MetaCRUD;

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

	/**
	 * 添加使用者 到 ph_access_itemmeta table
	 *
	 * @param int        $user_id 使用者 id
	 * @param ?\WC_Order $order 訂單，不一定有訂單
	 * @param string     $meta_key meta key 預設為 expire_date
	 * @return void
	 * @throws \Exception 授權失敗時拋出例外
	 */
	public function grant_user( int $user_id, ?\WC_Order $order = null, $meta_key = 'expire_date' ): void {

		$success = MetaCRUD::update( $this->id, $user_id, $meta_key, $this->calc_expire_date( $order ) );

		if ($success) {
			\do_action( 'powerhouse/limit/grant_user_success', $user_id, $order, $this, $meta_key );
		} else {
			\do_action( 'powerhouse/limit/grant_user_failed', $user_id, $order, $this, $meta_key );
			throw new \Exception(
			\sprintf(
			__( 'Grant user access failed, item id: %1$d, user id: #%2$d, order id: %3$s, meta_key: %4$s', 'powerhouse' ),
			$this->id,
			$user_id,
			$order ? "#{$order->get_id()}" : '',
			$meta_key
			)
			);

		}
	}

	/**
	 * 撤銷使用者
	 *
	 * @param int        $user_id 使用者 id
	 * @param ?\WC_Order $order 訂單，不一定有訂單
	 * @param string     $meta_key meta key 預設為 expire_date
	 * @return void
	 * @throws \Exception 撤銷失敗時拋出例外
	 */
	public function revoke_user( int $user_id, ?\WC_Order $order = null, $meta_key = 'expire_date' ): void {
		$success = MetaCRUD::delete( $this->id, $user_id, $meta_key );
		if ($success) {
			\do_action( 'powerhouse/limit/revoke_user_success', $user_id, $order, $this, $meta_key );
		} else {
			\do_action( 'powerhouse/limit/revoke_user_failed', $user_id, $order, $this, $meta_key );
			throw new \Exception(
			\sprintf(
			__( 'Revoke user access failed, item id: %1$d, user id: #%2$d, order id: %3$s, meta_key: %4$s', 'powerhouse' ),
			$this->id,
			$user_id,
			$order ? "#{$order->get_id()}" : '',
			$meta_key
			)
				);
		}
	}
}
