<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Subscription\DTOs;

use J7\Powerhouse\Domains\Subscription\Shared\Enums\Action;
use J7\Powerhouse\Domains\Subscription\Shared\Enums\Status;

/**
	* 註冊訂閱生命週期
	* 生命週期列表可以看 Action::get_action_hook()
 *  */
final class LifeCycle {
	use \J7\WpUtils\Traits\SingletonTrait;

	/** Constructor */
	public function __construct() {
		if (!class_exists('\WC_Subscriptions')) {
			return;
		}

		/** @category 訂閱首次付款成功後 */
		\add_action( 'woocommerce_subscription_payment_complete', [ $this, Action::INITIAL_PAYMENT_COMPLETE->value ], 10, 1 );

		/** @category 訂閱從成功到失敗 */
		\add_action( 'woocommerce_subscription_pre_update_status', [ $this, 'subscription_failed' ], 10, 3 );

		/** @category 訂閱從失敗到成功 */
		\add_action( 'woocommerce_subscription_pre_update_status', [ $this, 'subscription_success' ], 10, 3 );

		\add_filter( 'wcs_renewal_order_created', [ $this, 'renewal_order_created' ], 10, 2 );
	}

	/**
	 * 訂閱首次付款成功後
	 *
	 * @param \WC_Subscription $subscription 訂閱
	 * @return void
	 */
	public function initial_payment_complete( \WC_Subscription $subscription ): void {
		$related_order_ids = $subscription->get_related_orders();
		$parent_order      = $subscription->get_parent();
		if ( ! ( $parent_order instanceof \WC_Order ) ) {
			return;
		}

		$parent_order_id = $parent_order->get_id();

		// 確保只有一筆訂單 (parent order) 才會觸發，續訂不觸發
		if ( count( $related_order_ids ) !== 1 ) {
			return;
		}

		// 唯一一筆關聯訂單必須要 = parent order id
		if ( ( (int) reset( $related_order_ids ) ) !== ( (int) $parent_order_id )) {
			return;
		}

		// 執行生命週期
		\do_action( Action::INITIAL_PAYMENT_COMPLETE->get_action_hook(), $subscription );
	}

	/**
	 * 訂閱從成功到失敗
	 *
	 * @param string           $from_status old status
	 * @param string           $to_status new status
	 * @param \WC_Subscription $subscription post
	 * @return void
	 */
	public function subscription_failed( $from_status, $to_status, $subscription ): void {

		if ( ! ( $subscription instanceof \WC_Subscription ) ) {
			return;
		}

		$from_status = Status::tryFrom( $from_status );
		$to_status   = Status::tryFrom( $to_status );

		if ( ! $from_status || ! $to_status ) {
			return;
		}

		// 如果訂閱不是從成功轉變為失敗 就不處理
		if ( $from_status->is_failed() || !$to_status->is_failed() ) {
			return;
		}

		\do_action( Action::SUBSCRIPTION_FAILED->get_action_hook(), $subscription );
	}


	/**
	 * 訂閱從失敗到成功
	 *
	 * @param string           $from_status old status
	 * @param string           $to_status new status
	 * @param \WC_Subscription $subscription post
	 * @return void
	 */
	public function subscription_success( $from_status, $to_status, $subscription ): void {

		if ( ! ( $subscription instanceof \WC_Subscription ) ) {
			return;
		}

		$from_status = Status::tryFrom( $from_status );
		$to_status   = Status::tryFrom( $to_status );

		if ( ! $from_status || ! $to_status ) {
			return;
		}

		// 如果訂閱不是從失敗轉變為成功 就不處理
		if ( !$from_status->is_failed() || $to_status !== Status::ACTIVE ) {
			return;
		}

		\do_action( Action::SUBSCRIPTION_SUCCESS->get_action_hook(), $subscription );
	}


	/**
	 * 續訂訂單建立後
	 *
	 * @param \WC_Order            $renewal_order 續訂訂單
	 * @param int|\WC_Subscription $subscription 訂閱
	 * @return \WC_Order
	 */
	public function renewal_order_created( \WC_Order $renewal_order, int|\WC_Subscription $subscription ): \WC_Order {
		\do_action( Action::RENEWAL_ORDER_CREATED->get_action_hook(), $renewal_order, $subscription );
		return $renewal_order;
	}
}
