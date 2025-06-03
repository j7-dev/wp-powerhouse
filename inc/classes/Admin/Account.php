<?php

declare(strict_types=1);

namespace J7\Powerhouse\Admin;

use J7\Powerhouse\Settings\Model\Settings;

if ( class_exists( 'J7\Powerhouse\Admin\Account' ) ) {
	return;
}
/**  Account 相關 */
final class Account {
	use \J7\WpUtils\Traits\SingletonTrait;

	/** Constructor */
	public function __construct() {
		$last_name_optional = Settings::instance()->last_name_optional;

		if (!\wc_string_to_bool($last_name_optional)) {
			return;
		}

		\add_filter( 'woocommerce_save_account_details_required_fields', [ __CLASS__, 'set_last_name_optional' ] );
	}

	/**
	 * 設定姓氏為非必填
	 *
	 * @see my-account/edit-account/
	 *
	 * @param array $required_fields 必填欄位
	 * @return array 必填欄位
	 * @phpstan-ignore-next-line
	 */
	public static function set_last_name_optional( array $required_fields ): array {
		// 移除姓氏作为必填项的要求
		unset( $required_fields['account_last_name'] );
		return $required_fields;
	}
}
