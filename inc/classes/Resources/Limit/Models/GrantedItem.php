<?php

declare ( strict_types=1 );

namespace J7\Powerhouse\Resources\Limit\Models;

use J7\Powerhouse\Resources\Limit\Utils\MetaCRUD;

/**
 * 用戶與這個項目的關係
 */
class GrantedItem {

	/**
	 * 用戶是否可以存取這個項目
	 *
	 * @var bool
	 */
	public bool $can_access = false;

	/**
	 * 用戶的到期日
	 *
	 * @var ExpireDate|null
	 */
	public ?ExpireDate $expire_date = null;




	/**
	 * 初始化
	 *
	 * @param int    $post_id 項目ID
	 * @param int    $user_id 用戶ID
	 * @param string $meta_key 元數據鍵名
	 */
	public function __construct( public int $post_id, public int $user_id, public string $meta_key = 'expire_date' ) {
		$expire_date = MetaCRUD::get( $post_id, $user_id, $meta_key, true);

		// $expire_date = "" 如果用戶沒有觀看此項目權限
		if ('' === $expire_date) {
			$this->can_access = false;
			return;
		}

		$this->expire_date = new ExpireDate( (string) $expire_date);
	}
}
