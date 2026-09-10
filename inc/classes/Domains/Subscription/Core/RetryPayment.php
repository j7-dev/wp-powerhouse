<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Subscription\Core;

/**
 * 重新嘗試付款
 *  */
final class RetryPayment {
	use \J7\WpUtils\Traits\SingletonTrait;

	/** Constructor */
	public function __construct() {
		/** @category 超過付款重試上限後，訂閱轉為取消狀態 (原本會停在保留狀態) */
		\add_filter('woocommerce_subscription_max_failed_payments_exceeded', [ $this, 'max_failed_payments_exceeded' ], 100, 2);

		/** @category 修改預設的 5 次，共 7 天 的重試付款設定 */
		\add_filter('wcs_default_retry_rules', [ $this, 'set_retry_rule' ]);
	}

	/**
	 * 是否已達付款失敗次數上限（回傳 true 時 WooCommerce 會把訂閱轉為 cancelled）
	 *
	 * ⚠️ `WC_Subscription::payment_failed()` 在「每一次」扣款失敗都會跑這個 filter，
	 * 不是只在重試用盡時才跑。過去這裡掛 `__return_true`，等同於第一次扣款失敗就取消訂閱，
	 * `set_retry_rule()` 定義的 3 次重試從來沒有機會執行；又因為訂閱在
	 * `do_action('woocommerce_subscription_renewal_payment_failed')` 之前就被改成 cancelled，
	 * 重試規則接著要把訂閱設回 on-hold 時必然失敗，會多留一筆
	 * 「狀態轉換期間發生錯誤。無法將訂閱狀態變更為 on-hold」備註。
	 *
	 * 判斷準則：這次失敗之後「還有沒有下一條重試規則可以套用」
	 * - 有 → 回 false，讓 `WCS_Retry_Manager::maybe_apply_retry_rule()` 把訂閱轉 on-hold 並排程重試
	 * - 沒有 → 回 true，重試用盡才轉為 cancelled（符合原本註解的設計意圖）
	 *
	 * 判斷邏輯刻意對齊 `WCS_Retry_Manager::maybe_apply_retry_rule()` 的前置條件，
	 * 凡是「不可能發生重試」的情境（重試系統關閉、非續訂單、手動訂閱、金流不支援改日期）
	 * 都維持原本「付款失敗即取消」的行為，不改變既有站台的生命週期。
	 *
	 * @param mixed                       $exceeded     前面的 filter 判定的結果
	 * @param \WC_Subscription|mixed|null $subscription 付款失敗的訂閱
	 * @return bool 是否已達上限
	 */
	public function max_failed_payments_exceeded( $exceeded = false, $subscription = null ): bool {
		// 其他外掛 / 金流已經判定超過上限，尊重之
		if ( $exceeded ) {
			return true;
		}

		// 重試系統不存在或後台未啟用自動重試 → 不會有任何重試發生，維持原本行為
		if ( ! class_exists('\WCS_Retry_Manager') || ! \WCS_Retry_Manager::is_retry_enabled() ) {
			return true;
		}

		// 拿不到訂閱物件就無從判斷重試次數，保守起見不取消（訂閱會停在 on-hold）
		if ( ! ( $subscription instanceof \WC_Subscription ) ) {
			return false;
		}

		// 與 WC_Subscription::payment_failed() 取得的 $last_order 一致（'any' 等同展開成這三種）
		$last_order_id = $subscription->get_last_order( 'ids', [ 'parent', 'renewal', 'switch' ] );

		/**
		 * 只有「續訂單」會套用重試規則
		 * WCS_Retry_Manager 掛在 woocommerce_subscription_renewal_payment_failed，
		 * 該 action 只在 wcs_order_contains_renewal() 成立時才觸發
		 */
		if ( ! $last_order_id || ! \wcs_order_contains_renewal( $last_order_id ) ) {
			return true;
		}

		// 手動訂閱 / 金流不支援改日期 → maybe_apply_retry_rule() 會直接 return，不會有重試
		if ( $subscription->is_manual() || ! $subscription->payment_method_supports( 'subscription_date_changes' ) ) {
			return true;
		}

		$retry_count = (int) \WCS_Retry_Manager::store()->get_retry_count_for_order( $last_order_id );

		// 還有下一條重試規則可用 → 尚未達上限
		return ! \WCS_Retry_Manager::rules()->has_rule( $retry_count, $last_order_id );
	}

	/**
	 * 修改預設的 5 次，共 7 天 的重試付款設定
	 *
	 * @param array<array<string, mixed>> $retry_rules 重試規則
	 * @return array<array<string, mixed>> 重試規則
	 */
	public function set_retry_rule( array $retry_rules ): array {
		$new_retry_rules = [
			[
				'retry_after_interval'            => \HOUR_IN_SECONDS, // how long to wait before retrying
				'email_template_customer'         => '', // don't bother the customer yet
				'email_template_admin'            => 'WCS_Email_Payment_Retry',
				'status_to_apply_to_order'        => 'pending',
				'status_to_apply_to_subscription' => 'on-hold',
			],
			[
				'retry_after_interval'            => \HOUR_IN_SECONDS, // how long to wait before retrying
				'email_template_customer'         => '', // don't bother the customer yet
				'email_template_admin'            => 'WCS_Email_Payment_Retry',
				'status_to_apply_to_order'        => 'pending',
				'status_to_apply_to_subscription' => 'on-hold',
			],
			[
				'retry_after_interval'            => \HOUR_IN_SECONDS, // how long to wait before retrying
				'email_template_customer'         => '', // don't bother the customer yet
				'email_template_admin'            => 'WCS_Email_Payment_Retry',
				'status_to_apply_to_order'        => 'pending',
				'status_to_apply_to_subscription' => 'on-hold',
			],
		];
		return $new_retry_rules;
	}
}
