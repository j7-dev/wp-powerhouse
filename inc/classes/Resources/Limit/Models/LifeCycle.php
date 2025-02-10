<?php

declare ( strict_types=1 );

namespace J7\Powerhouse\Resources\Limit\Models;

use J7\Powerhouse\Resources\Limit\Utils\MetaCRUD;

/**
 * 項目的觀看限制生命週期
 */
class LifeCycle {
	use \J7\WpUtils\Traits\SingletonTrait;

	// 開通用戶權限的鉤子
	const ADD_USER_TO_ITEM_ACTION = 'powerhouse_add_user_to_item';
	// 開通用戶權限後
	const AFTER_GRANT_USER_TO_ITEM_ACTION = 'powerhouse_after_grant_user_to_item';
	// 更新用戶觀看後
	const AFTER_UPDATE_USER_FROM_ITEM_ACTION = 'powerhouse_after_update_user_from_item';
	// 移除用戶後
	const AFTER_REMOVE_USER_FROM_ITEM_ACTION = 'powerhouse_after_remove_user_from_item';


	/**
	 * 授權用戶存取項目，開通用戶權限
	 *
	 * @param int        $user_id 用戶 id
	 * @param int        $post_id 項目 id
	 * @param int|string $expire_date 到期日 10位 timestamp | subscription_{訂閱id}
	 * @param ?\WC_Order $order 訂單
	 * @return void
	 * @throws \Exception 新增學員失敗
	 */
	public static function grant_user_to_item( int $user_id, int $post_id, int|string $expire_date, ?\WC_Order $order = null ): void {

		$update_success = MetaCRUD::update( (int) $post_id, (int) $user_id, 'expire_date', $expire_date );

		\do_action(self::AFTER_GRANT_USER_TO_ITEM_ACTION, $user_id, $post_id, $expire_date, $order);

		if ( false === $update_success) {
			throw new \Exception(
				sprintf(
				__('grant user_id #%1$s access to post_id #%2$s failed, expire_date %3$s %4$s', 'powerhouse'),
			$user_id,
			$post_id,
			$expire_date,
			$order ? ", order_id #{$order->get_id()}" : ''
			)
				);
		}
	}
}
