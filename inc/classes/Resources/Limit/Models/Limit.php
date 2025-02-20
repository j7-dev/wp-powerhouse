<?php

declare ( strict_types=1 );

namespace J7\Powerhouse\Resources\Limit\Models;

/**
 * 課程的觀看限制 Limit

 * 可以指定為 "無期限"、"購買後固定時間"、"指定日期"、"跟隨訂閱"
 * 可以自己繼承來加上 prefix 以及覆寫 instance 實例化方法
 * prefix 只影響 instance, get_meta_keys 方法
 */
class Limit {

	/**
	 * 欄位前綴
	 *
	 * @var string $prefix 前綴
	 */
	public static string $prefix = '';

	/**
	 * 限制類型
	 *
	 * @var string $limit_type 限制類型 'unlimited' | 'fixed' | 'assigned' | 'follow_subscription'
	 */
	public string $limit_type;

	/**
	 * 限制值
	 *
	 * @var int|null $limit_value 限制值
	 */
	public int|null $limit_value;

	/**
	 * 限制單位
	 *
	 * @var string|null $limit_unit 限制單位 'timestamp' | 'day' | 'month' | 'year'
	 */
	public string|null $limit_unit;

	/**
	 * Constructor
	 *
	 * @param string      $limit_type 限制類型 'unlimited' | 'fixed' | 'assigned' | 'follow_subscription'
	 * @param int|null    $limit_value 限制值
	 * @param string|null $limit_unit 限制單位 'timestamp' | 'day' | 'month' | 'year'
	 * @param string      $prefix 欄位前綴
	 */
	public function __construct( string $limit_type, int|null $limit_value, string|null $limit_unit, string $prefix = '' ) {
		self::$prefix = $prefix;
		$this->set_limit_type($limit_type);
		$this->set_limit_value($limit_value);
		$this->set_limit_unit($limit_unit);
	}

	/**
	 * 取得限制實例
	 *
	 * @param int $post_id 文章 id
	 * @return self
	 * @throws \Exception 如果課程不存在
	 */
	public static function instance( int $post_id ): self {
		$prefix      = self::$prefix;
		$limit_type  = (string) \get_post_meta( $post_id, "{$prefix}limit_type", true );
		$limit_value = (int) \get_post_meta( $post_id, "{$prefix}limit_value", true ) ?: null;
		$limit_unit  = (string) \get_post_meta( $post_id, "{$prefix}limit_unit", true ) ?: null;

		return new self($limit_type, $limit_value, $limit_unit);
	}

	/**
	 * 計算到期日 expire_date
	 *
	 * @param ?\WC_Order $order 訂單
	 * @return int|string 到期日 timestamp | subscription_{訂閱id}
	 */
	public function calc_expire_date( ?\WC_Order $order ): int|string {

		$expire_date = 0;

		if ('unlimited' === $this->limit_type) {
			return $expire_date;
		}
		if ('assigned' === $this->limit_type) {
			return (int) $this->limit_value; // timestamp
		}
		if ('fixed' === $this->limit_type) {
			$expire_date_timestamp = (int) strtotime("+{$this->limit_value} {$this->limit_unit}");
			// 將 timestamp 轉換為當天的日期，並固定在當天的 15:59:00
			$expire_date_string = date('Y-m-d', $expire_date_timestamp) . ' 15:59:00';
			return (int) strtotime($expire_date_string);
		}

		if (!$order) {
			return $expire_date;
		}

		// 所有條件都判斷完了，剩下的就是 follow_subscription
		// 'follow_subscription' === $limit_type
		if (!class_exists('WC_Subscription')) {
			\J7\WpUtils\Classes\WC::log("訂單 {$order->get_id()} 的 expire_date 計算失敗，因為 WC_Subscription 不存在", 'CourseUtils::calc_expire_date');
			return $expire_date;
		}

		$subscriptions = \wcs_get_subscriptions_for_order( $order, [ 'order_type' => 'parent' ] );

		if (!!$subscriptions && count($subscriptions) === 1) {
			$subscription    = reset($subscriptions);
			$subscription_id = $subscription->get_id();
			return "subscription_{$subscription_id}";
		}

		return $expire_date;
	}

	/**
	 * 取得 ExpireDate 實例
	 *
	 * @param ?\WC_Order $order 訂單
	 * @return ExpireDate
	 */
	public function get_expire_date( ?\WC_Order $order ): ExpireDate {
		$expire_date = $this->calc_expire_date($order);
		return new ExpireDate($expire_date);
	}

	/**
	 * 取得限制標籤文字
	 * {類型} {值}
	 * ex: 固定時間 10 天, 指定日期 2024-01-01, 跟隨訂閱, 無限制
	 *
	 * @return object{type:string, value:string}
	 */
	public function get_limit_label(): object {
		$limit_type_label = match ( $this->limit_type ) {
			'fixed'    => '固定時間',
			'assigned' => '指定日期',
			'follow_subscription' => '跟隨訂閱',
			default    => '無限制',
		};

		$limit_value_label = match ( $this->limit_unit ) {
			'timestamp' => strlen( (string) $this->limit_value) !== 10 ? '' : \wp_date( 'Y-m-d H:i', $this->limit_value ),
			'month'  => "{$this->limit_value} 月",
			'year'   => "{$this->limit_value} 年",
			default  => $this->limit_value ? "{$this->limit_value} 天" : '',
		};

		if ( in_array($this->limit_type, [ 'unlimited', 'follow_subscription' ], true) ) {
			$limit_value_label = '';
		}

		/** @var string $limit_value_label */
		return (object) [
			'type'  => $limit_type_label,
			'value' => $limit_value_label,
		];
	}

	/**
	 * 取得限制的 meta keys (存在 post meta 中)
	 *
	 * @return array<string>
	 */
	public static function get_meta_keys(): array {
		$prefix = self::$prefix;
		return [ "{$prefix}limit_type", "{$prefix}limit_value", "{$prefix}limit_unit" ];
	}

	/**
	 * 設定限制類型
	 *
	 * @param string $limit_type 限制類型 'unlimited' | 'fixed' | 'assigned' | 'follow_subscription'
	 * @throws \Exception 如果限制類型無效
	 */
	private function set_limit_type( string $limit_type ): void {
		if (!in_array($limit_type, [ 'unlimited', 'fixed', 'assigned', 'follow_subscription' ], true)) {
			\J7\WpUtils\Classes\WC::log($limit_type, 'set_limit_type Invalid limit type');
		}
		$this->limit_type = $limit_type;
	}

	/**
	 * 設定限制值
	 *
	 * @param int|null $limit_value 限制值
	 */
	private function set_limit_value( int|null $limit_value ): void {
		if (!$limit_value) {
			$this->limit_value = null;
			return;
		}
		$this->limit_value = $limit_value;
	}

	/**
	 * 設定限制單位
	 *
	 * @param string|null $limit_unit 限制單位 'timestamp' | 'day' | 'month' | 'year'
	 * @throws \Exception 如果限制單位無效
	 */
	private function set_limit_unit( string|null $limit_unit ): void {
		if (!$limit_unit) {
			$this->limit_unit = null;
			return;
		}
		if (!in_array($limit_unit, [ 'timestamp', 'day', 'month', 'year' ], true)) {
			\J7\WpUtils\Classes\WC::log($limit_unit, 'set_limit_unit Invalid limit unit');
		}
		$this->limit_unit = $limit_unit;
	}
}
