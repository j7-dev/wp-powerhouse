<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Subscription\Utils;

/** Base  */
abstract class Base {

	/**
	 * 取得訂閱的最新訂單
	 *
	 * @param \WC_Subscription $subscription 訂閱
	 * @return \WC_Order|null
	 */
	public static function get_last_order( \WC_Subscription $subscription ): \WC_Order|null {
		/** @var numeric-string|false $last_order_id */
		$last_order_id = $subscription->get_last_order('ids');
		if ( !$last_order_id ) {
			return null;
		}

		$last_order = \wc_get_order( $last_order_id );
		if ( ! $last_order instanceof \WC_Order ) {
			return null;
		}

		return $last_order;
	}
}
