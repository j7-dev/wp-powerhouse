<?php
/**
 * 課程的觀看期限 ExpireDate
 * 由 Limit 的 get_expire_date 傳入後初始化
 */

declare ( strict_types=1 );

namespace J7\Powerhouse\Domains\Limit;

/**
 * Class ExpireDate
 */
class ExpireDate {

	/**
	 * 是否為"跟隨訂閱"
	 *
	 * @var bool $is_subscription 是否為訂閱
	 */
	public bool $is_subscription = false;

	/**
	 * 是否過期
	 *
	 * @var bool $is_expired 是否過期
	 */
	public bool $is_expired;

	/**
	 * 訂閱ID
	 *
	 * @var int|null $subscription_id 如果是"跟隨訂閱"，就會有訂閱ID
	 */
	public int|null $subscription_id = null;

	/**
	 * 到期日標籤
	 *
	 * @var string $expire_date_label 到期日標籤
	 */
	public string $expire_date_label = '';

	/**
	 * Constructor
	 *
	 * @param int|string $expire_date 到期日 timestamp | subscription_{訂閱id}
	 */
	public function __construct( public int|string $expire_date ) {
		if (\is_numeric($expire_date)) {
			$this->expire_date = (int) $expire_date;
		}

		if (class_exists('WC_Subscription')) {
			$this->set_subscription();
		}

		$this->set_label();
		$this->set_is_expired();
	}

	/**
	 * 設定標籤
	 *
	 * @param string|null $format 日期格式
	 * @return void
	 */
	public function set_label( ?string $format = 'Y-m-d H:i:s' ): void {
		if ($this->is_subscription) {
			$this->expire_date_label = "跟隨訂閱 #{$this->subscription_id}";
			return;
		}
		/** @var int $expire_date */
		$expire_date = $this->expire_date;
		if ( ! $expire_date ) {
			$this->expire_date_label = '無期限';
			return;
		}
		$this->expire_date_label = \wp_date( $format ?? 'Y-m-d H:i:s', $expire_date ) ?: '無法取得時間';
	}

	/**
	 * 是否過期
	 *
	 * @return void
	 */
	public function set_is_expired(): void {
		if (!$this->is_subscription) {
			$expire_date = (int) $this->expire_date;
			// 0 = 無期限，不會過期
			$this->is_expired = $expire_date && $expire_date < time();
			return;
		}

		$subscription = \wcs_get_subscription($this->subscription_id);
		if (!$subscription) {
			$this->is_expired = true;
			return;
		}
		$this->is_expired = !$subscription->has_status('active');
	}


	/**
	 * 轉換成 array
	 *
	 * @return array{is_subscription: bool, subscription_id: int|null, is_expired: bool, timestamp: int|string|null}
	 */
	public function to_array(): array {
		return [
			'is_subscription' => $this->is_subscription,
			'subscription_id' => $this->subscription_id,
			'is_expired'      => $this->is_expired,
			'timestamp'       => $this->is_subscription ? null : $this->expire_date,
		];
	}

	/**
	 * 初始化訂閱
	 *
	 * @return void
	 */
	private function set_subscription(): void {
		$this->is_subscription = str_starts_with( (string) $this->expire_date, 'subscription_');
		if ( $this->is_subscription ) {
			$this->subscription_id = (int) str_replace('subscription_', '', (string) $this->expire_date);
		}
	}
}
