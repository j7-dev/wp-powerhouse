<?php
/**
 * LicenseCodes
 */

declare (strict_types = 1);

namespace J7\Powerhouse;

use J7\Powerhouse\Plugin;



if ( class_exists( 'J7\Powerhouse\LicenseCodes' ) ) {
	return;
}
/**
 * Class LicenseCodes
 */
final class LicenseCodes {
	use \J7\WpUtils\Traits\SingletonTrait;

	const KEY = 'powerhouse_license_codes';

	/**
	 * Render Powerhouse Page Callback
	 */
	public static function powerhouse_license_codes_page_callback(): void {
		$is_saved = self::handle_save();

		$lc_array = self::get_lc_array();

		printf(
		/*html*/'
		<sl-alert class="mb-8" variant="success" %1$s>
			<sl-icon slot="icon" name="check2-circle"></sl-icon>
			儲存成功
		</sl-alert>
',
		$is_saved ? 'open' : '',
		);

		echo '<div class="grid grid-cols-4 gap-6 my-8 pr-5">';
		foreach ( $lc_array as $lc ) {
			Plugin::safe_get(
				'license-codes/item',
				[
					'license_code' => $lc,
					'key'          => self::KEY,
				]
			);
		}
		echo '</div>';
	}

	/**
	 * 儲存表單
	 *
	 * @return bool 是否儲存
	 */
	private static function handle_save(): bool {
		// phpcs:disable
		// 檢查是否提交了表單
		$is_submit = \in_array($_POST['submit_button'] ?? '', ['activate', 'deactivate'], true);
		if (!$is_submit) {
			return false;
		}

		$key = self::KEY;

		// 驗證 nonce
		if (!isset($_POST[ "{$key}_nonce" ]) || !\wp_verify_nonce($_POST[ "{$key}_nonce" ], "{$key}_action")) {
			\wp_die('安全檢查失敗');
		}

		return true;
		// phpcs:enable
	}

	public static function get_lc_array(): array {

		$product_names = \apply_filters( 'powerhouse_product_names', [] );

		$default_lc = [
			'code'         => '',
			'status'       => '',
			'expired_date' => '',
			'type'         => '',
		];

		$lc_array = [];

		// @phpstan-ignore-next-line
		foreach ( $product_names as $product_key => $product_name ) {
			$lc = \get_transient("lc_{$product_key}");
			if (false === $lc) {
				$lc                 = $default_lc;
				$lc['product_key']  = $product_key;
				$lc['product_name'] = $product_name;
				\set_transient("lc_{$product_name}", self::encode($lc), 24 * HOUR_IN_SECONDS);

				$lc_array[] = $lc;
				continue;
			}
			$lc_array[] = self::decode($lc);
		}

		return $lc_array;
	}

	public static function lc( $code, $product_key ) {
		$response = \wp_remote_post(
			'https://api.powerhouse.com/v1/license-codes',
			[
				'body' => [
					'code'        => $code,
					'product_key' => $product_key,
				],
			]
		);

		if (\is_wp_error($response)) {
			return $response->get_error_message();
		}

		$body = \wp_remote_retrieve_body($response);
		$data = \json_decode($body, true);

		return $data;
	}

	public static function decode( $value ): array {
		try {
			return \json_decode( $value, true );
		} catch ( \Exception $e ) {
			return [];
		}
	}

	public static function encode( $value ): string {
		return \wp_json_encode( $value );
	}
}
