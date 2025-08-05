<?php

declare (strict_types = 1);

namespace J7\Powerhouse\Domains\Subscription\Shared\Enums;

/**
 * Action 信件觸發的時間點
 *  */
enum Action: string {

	/**  @var string 訂閱創建後 */
	case DATE_CREATED = 'date_created';

	/** @var string 訂閱首次付款成功後 */
	case INITIAL_PAYMENT_COMPLETE = 'initial_payment_complete';

	/** @var string 訂閱從成功到失敗 */
	case SUBSCRIPTION_FAILED = 'subscription_failed';

	/** @var string 訂閱從失敗到成功 */
	case SUBSCRIPTION_SUCCESS = 'subscription_success';

	/** @var string 訂閱付款重試 */
	case PAYMENT_RETRY = 'payment_retry';

	/** @var string 試用結束前|後 */
	case TRIAL_END = 'trial_end';

	/** @var string 下次付款前|後 */
	case NEXT_PAYMENT = 'next_payment';

	/** @var string 新的續訂訂單創建 */
	case RENEWAL_ORDER_CREATED = 'renewal_order_created';

	/** @var string 訂閱結束 */
	case END = 'end';

	/** @var string 訂閱結束，如果訂閱有 "cancelled" 或 "pending-cancel" 狀態，會觸發這個 */
	case END_OF_PREPAID_TERM = 'end_of_prepaid_term';

	/**
	 * 取得 訂閱觸發的時間點的 action hook
	 *
	 * @return string
	 */
	public function get_action_hook(): string {
		return "powerhouse_subscription_at_{$this->value}";
	}
}
