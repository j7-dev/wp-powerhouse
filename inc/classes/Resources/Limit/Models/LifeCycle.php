<?php

declare ( strict_types=1 );

namespace J7\Powerhouse\Resources\Limit\Models;

use J7\Powerhouse\Resources\Limit\Utils\MetaCRUD;

/**
 * 項目的觀看限制生命週期
 * TODO 接入 log
 */
class LifeCycle {
	use \J7\WpUtils\Traits\SingletonTrait;

	// 開通用戶權限的鉤子
	const GRANT_USER_TO_ITEM_ACTION = 'powerhouse/limit/grant_user_to_item';
	// 開通用戶權限後
	const AFTER_GRANT_USER_TO_ITEM_ACTION = 'powerhouse/limit/after_grant_user_to_item';
	// 更新用戶觀看後
	const AFTER_UPDATE_USER_FROM_ITEM_ACTION = 'powerhouse/limit/after_update_user_from_item';
	// 移除用戶後
	const AFTER_REVOKE_USER_FROM_ITEM_ACTION = 'powerhouse/limit/after_revoke_user_from_item';

	/**
	 * Constructor
	 */
	public function __construct() {
		// 開通權限
		\add_action( self::GRANT_USER_TO_ITEM_ACTION, [ __CLASS__, 'grant_user_to_item' ], 10, 4 );

		// 直接更新用戶觀看時間
		\add_action(self::AFTER_UPDATE_USER_FROM_ITEM_ACTION, [ __CLASS__, 'update_user_from_item' ], 10, 3);

		// 移除
		\add_action(self::AFTER_REVOKE_USER_FROM_ITEM_ACTION, [ __CLASS__, 'revoke_user_from_item' ], 10, 2);
	}


	/**
	 * 授權用戶存取項目，開通用戶權限
	 *
	 * @param int        $user_id 用戶 id
	 * @param int        $post_id 項目 id
	 * @param int|string $expire_date 到期日 10位 timestamp | subscription_{訂閱id}
	 * @param ?\WC_Order $order 訂單
	 * @return void
	 * @throws \Exception 新增用戶失敗
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


	/**
	 * 更新用戶
	 *
	 * @param int $user_id 用戶 id
	 * @param int $post_id 項目 id
	 * @param int $timestamp 觀看時間
	 * @return void
	 * @throws \Exception 更新用戶觀看項目期限失敗
	 */
	public static function update_user_from_item( int $user_id, int $post_id, int $timestamp ): void {
		$success = MetaCRUD::update( (int) $post_id, (int) $user_id, 'expire_date', $timestamp );

		if (!$success) {
			throw new \Exception(\sprintf(__('Failed to update user item expiration time, user_id #%1$s, post_id #%2$s, timestamp #%3$s', 'powerhouse'), $user_id, $post_id, $timestamp));
		}
	}

	/**
	 * 移除用戶
	 *
	 * @param int $user_id 用戶 id
	 * @param int $post_id 項目 id
	 * @return void
	 * @throws \Exception 移除用戶失敗
	 */
	public static function revoke_user_from_item( int $user_id, int $post_id ): void {

		// 移除上課權限時，也把 avl_course_meta 相關資料刪除
		$success = MetaCRUD::delete( (int) $post_id, (int) $user_id );

		if (!$success) {
			throw new \Exception(\sprintf(__('Failed to remove user, user_id #%1$s, post_id #%2$s', 'powerhouse'), $user_id, $post_id));
		}
	}
}
