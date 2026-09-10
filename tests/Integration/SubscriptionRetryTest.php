<?php
/**
 * Subscription RetryPayment 整合測試
 * 驗證 RetryPayment 類別的重試規則設定與「付款失敗次數上限」判定
 */

declare( strict_types=1 );

namespace Tests\Integration;

use J7\Powerhouse\Domains\Subscription\Core\RetryPayment;

/**
 * Class SubscriptionRetryTest
 *
 * @group subscription
 */
class SubscriptionRetryTest extends TestCase {

	/**
	 * 刻意不做 class 層級的 skipIfSubscriptionsMissing()
	 * RetryPayment 只掛 filter、不碰 WCS API，多數斷言在沒有 WC Subscriptions 的環境
	 * （例如 CI 的 wp-env，只裝 WooCommerce）也應該跑，才擋得住 issue #10 的迴歸。
	 * 真的需要 WCS 的測試自行用 class_exists() 判斷後 skip。
	 */
	public function set_up(): void {
		parent::set_up();
	}

	/**
	 * 取得一個「保證剛跑過建構子」的實例，用來斷言 filter 註冊狀態
	 *
	 * SingletonTrait::instance() 只有第一次呼叫會執行建構子，而 WP_UnitTestCase 會在每個
	 * 測試結束時還原 $wp_filter，因此靠 instance() 取得的 hook 註冊狀態會隨測試順序與
	 * 環境（有沒有裝 WC Subscriptions、Loader 有沒有在 bootstrap 時初始化）漂移。
	 * 此處直接建構是「驗證建構子副作用」的測試專用例外；正式程式碼一律用 RetryPayment::instance()。
	 */
	private function freshly_constructed(): RetryPayment {
		return new RetryPayment();
	}

	// ========== 🔥 冒煙測試 ==========

	/**
	 * @test
	 * @group smoke
	 */
	public function retrypayment_類別應在訂閱載入時初始化(): void {
		$this->freshly_constructed();

		// 驗證 filter 被註冊
		$this->assertGreaterThan(
			0,
			\has_filter( 'woocommerce_subscription_max_failed_payments_exceeded' ),
			'max_failed_payments_exceeded filter 應被註冊'
		);
		$this->assertGreaterThan(
			0,
			\has_filter( 'wcs_default_retry_rules' ),
			'wcs_default_retry_rules filter 應被註冊'
		);
	}

	// ========== ✅ 快樂路徑 ==========

	/**
	 * @test
	 * @group happy
	 */
	public function set_retry_rule_應回傳_3_筆規則(): void {
		$retry = RetryPayment::instance();
		$rules = $retry->set_retry_rule( [] );

		$this->assertCount( 3, $rules, '應回傳 3 筆重試規則' );
	}

	/**
	 * @test
	 * @group happy
	 */
	public function 重試規則應使用_HOUR_IN_SECONDS_作為間隔(): void {
		$retry = RetryPayment::instance();
		$rules = $retry->set_retry_rule( [] );

		foreach ( $rules as $rule ) {
			$this->assertSame(
				\HOUR_IN_SECONDS,
				$rule['retry_after_interval'],
				'每筆重試規則間隔應為 HOUR_IN_SECONDS'
			);
		}
	}

	/**
	 * @test
	 * @group happy
	 */
	public function 重試規則應設定正確的狀態(): void {
		$retry = RetryPayment::instance();
		$rules = $retry->set_retry_rule( [] );

		foreach ( $rules as $rule ) {
			$this->assertSame( 'pending', $rule['status_to_apply_to_order'] );
			$this->assertSame( 'on-hold', $rule['status_to_apply_to_subscription'] );
			$this->assertSame( 'WCS_Email_Payment_Retry', $rule['email_template_admin'] );
			$this->assertSame( '', $rule['email_template_customer'] );
		}
	}

	/**
	 * @test
	 * @group happy
	 */
	public function 重試規則應覆蓋原有規則(): void {
		$retry = RetryPayment::instance();

		// 即使傳入舊規則，也應全部覆蓋
		$old_rules = [
			[ 'retry_after_interval' => 100 ],
			[ 'retry_after_interval' => 200 ],
		];
		$new_rules = $retry->set_retry_rule( $old_rules );

		$this->assertCount( 3, $new_rules );
		$this->assertNotSame( 100, $new_rules[0]['retry_after_interval'] );
	}

	// ========== 🚨 迴歸測試：付款失敗次數上限判定 ==========

	/**
	 * 迴歸測試（issue #10）
	 *
	 * 舊實作是 `add_filter(..., '__return_true')`，
	 * 但 WC_Subscription::payment_failed() 每一次扣款失敗都會跑這個 filter，
	 * 導致第一次失敗就取消訂閱、3 次重試規則永遠不執行。
	 *
	 * @test
	 * @group smoke
	 */
	public function max_failed_payments_exceeded_不應再掛_return_true(): void {
		$retry = $this->freshly_constructed();

		$this->assertFalse(
			\has_filter( 'woocommerce_subscription_max_failed_payments_exceeded', '__return_true' ),
			'不可再無條件回傳 true，否則第一次扣款失敗就會取消訂閱'
		);
		$this->assertSame(
			100,
			\has_filter( 'woocommerce_subscription_max_failed_payments_exceeded', [ $retry, 'max_failed_payments_exceeded' ] ),
			'應改掛具備判斷邏輯的 max_failed_payments_exceeded()，priority 100'
		);
	}

	/**
	 * 前面的 filter（其他外掛 / 金流）已判定超過上限時應尊重之
	 *
	 * @test
	 * @group happy
	 */
	public function 前一個_filter_已判定超過上限時應維持_true(): void {
		$retry = RetryPayment::instance();

		$this->assertTrue( $retry->max_failed_payments_exceeded( true, null ) );
	}

	/**
	 * 重試系統不可用（未安裝 / 後台未啟用）時，維持原本「付款失敗即取消」的行為
	 *
	 * @test
	 * @group edge
	 */
	public function 重試系統未啟用時應判定為超過上限(): void {
		if ( ! class_exists( '\WCS_Retry_Manager' ) ) {
			$this->markTestSkipped( 'WCS_Retry_Manager 不存在（此時本來就會回傳 true）' );
		}

		$retry = RetryPayment::instance();

		\add_filter( 'wcs_is_retry_enabled', '__return_false', 999 );
		try {
			$this->assertTrue(
				$retry->max_failed_payments_exceeded( false, null ),
				'沒有重試機制時，第一次失敗即視為已達上限'
			);
		} finally {
			\remove_filter( 'wcs_is_retry_enabled', '__return_false', 999 );
		}
	}

	/**
	 * 重試已啟用但拿不到訂閱物件時，保守起見不取消（訂閱停在 on-hold）
	 *
	 * @test
	 * @group edge
	 */
	public function 重試已啟用但無訂閱物件時不應判定為超過上限(): void {
		if ( ! class_exists( '\WCS_Retry_Manager' ) ) {
			$this->markTestSkipped( 'WCS_Retry_Manager 不存在' );
		}

		$retry = RetryPayment::instance();

		\add_filter( 'wcs_is_retry_enabled', '__return_true', 999 );
		try {
			$this->assertFalse(
				$retry->max_failed_payments_exceeded( false, null ),
				'無法判斷重試次數時不應取消訂閱'
			);
		} finally {
			\remove_filter( 'wcs_is_retry_enabled', '__return_true', 999 );
		}
	}

	/**
	 * 判定所依據的「重試梯度」：自訂 3 條規則 → 第 0/1/2 段有規則、第 3 段用盡
	 *
	 * 對應 max_failed_payments_exceeded() 內的
	 * `! WCS_Retry_Manager::rules()->has_rule( $retry_count, $order_id )`
	 *
	 * @test
	 * @group happy
	 */
	public function 自訂重試梯度應在第_3_次重試後才用盡(): void {
		if ( ! class_exists( '\WCS_Retry_Rules' ) ) {
			$this->markTestSkipped( 'WCS_Retry_Rules 不存在' );
		}

		// 確保 wcs_default_retry_rules filter 已註冊（WCS_Retry_Rules 於建構時套用）
		RetryPayment::instance();
		$rules = new \WCS_Retry_Rules();

		$this->assertTrue( $rules->has_rule( 0, 0 ), '第 1 次扣款失敗後仍有重試規則' );
		$this->assertTrue( $rules->has_rule( 1, 0 ), '第 1 次重試失敗後仍有重試規則' );
		$this->assertTrue( $rules->has_rule( 2, 0 ), '第 2 次重試失敗後仍有重試規則' );
		$this->assertFalse( $rules->has_rule( 3, 0 ), '第 3 次重試失敗後重試用盡，此時才該取消訂閱' );
	}
}
