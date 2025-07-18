<?php

declare(strict_types=1);

namespace J7\Powerhouse\Admin;

use J7\Powerhouse\Settings\Model\Settings;

if ( class_exists( 'J7\Powerhouse\Admin\DelayEmail' ) ) {
	return;
}
/** DelayEmail 延遲寄送 Email */
final class DelayEmail {
	use \J7\WpUtils\Traits\SingletonTrait;

	/** Constructor */
	public function __construct() {
		$delay_email = Settings::instance()->delay_email;

		if (!\wc_string_to_bool($delay_email)) {
			return;
		}

		\add_action( 'init', [ __CLASS__, 'remove_origin_email_sending' ], 100 );
		\add_action( 'powerhouse_delay_email', [ __CLASS__, 'schedule_email' ], 10, 3 );
	}


	/**
	 * 移除 EMAIL
	 * 測試可用 \as_schedule_single_action( time() + 3600, 'powerhouse_delay_email', [ $class_name, ...$args ] );
	 */
	public static function remove_origin_email_sending(): void {
		$class_name_and_hooks = [
			'WC_Email_New_Order' => [
				'woocommerce_order_status_pending_to_processing_notification',
				'woocommerce_order_status_pending_to_completed_notification',
				'woocommerce_order_status_pending_to_on-hold_notification',
				'woocommerce_order_status_failed_to_processing_notification',
				'woocommerce_order_status_failed_to_completed_notification',
				'woocommerce_order_status_failed_to_on-hold_notification',
				'woocommerce_order_status_cancelled_to_processing_notification',
				'woocommerce_order_status_cancelled_to_completed_notification',
				'woocommerce_order_status_cancelled_to_on-hold_notification',
				// 'woocommerce_email_footer',
			],
			'WC_Email_Customer_Completed_Order' => [
				'woocommerce_order_status_completed_notification',
			],
			'WC_Email_Customer_Processing_Order' => [
				'woocommerce_order_status_cancelled_to_processing_notification',
				'woocommerce_order_status_failed_to_processing_notification',
				'woocommerce_order_status_on-hold_to_processing_notification',
				'woocommerce_order_status_pending_to_processing_notification',
			],
		];

		foreach ($class_name_and_hooks as $class_name => $hooks) {
			foreach ($hooks as $hook) {
				\remove_action( $hook, [ \WC()->mailer()->emails[ $class_name ], 'trigger' ] );
				\add_action(
					$hook,
					function ( ...$args ) use ( $class_name ) {
						/** @var array<mixed> $args */
						\as_enqueue_async_action( 'powerhouse_delay_email', [ $class_name, ...$args ] );
					},
					10
					);
			}
		}
	}

	/**
	 * Schedule Email
	 *
	 * @param string $class_name Class name
	 * @param mixed  ...$args Arguments 有2個參數
	 * @return void
	 */
	public static function schedule_email( $class_name, ...$args ): void {
		/**
		 * @var \WC_Email_New_Order $email_instance
		 * 實際型別為 繼承 \WC_Email 的類別，只是以 \WC_Email_New_Order 舉例
		 */
		$email_instance = \WC()->mailer()->emails[ $class_name ];

		// check if the method exists
		if ( ! method_exists(   $email_instance, 'trigger' ) ) { // @phpstan-ignore-line
			return;
		}

		$email_instance->trigger( ...$args ); // @phpstan-ignore-line
	}
}
