<?php
/**
 * LicenseCodes
 * 1. 先用 powerhouse_product_names filter hook 註冊 product_key 和 product_name
 * 2. 每個 transient 會記錄各個 product 的授權狀態，加密後存為 string
 */

declare (strict_types = 1);

namespace J7\Powerhouse;

use J7\Powerhouse\Plugin;
use J7\WpUtils\Classes\General;


if ( class_exists( 'J7\Powerhouse\LC' ) ) {
	return;
}
/**
 * Class LC
 */
final class LC {
	use \J7\WpUtils\Traits\SingletonTrait;

	const KEY        = 'powerhouse_license_codes';
	const CACHE_TIME = 24 * HOUR_IN_SECONDS;

	/**
	 * Render Powerhouse Page Callback
	 */
	public static function powerhouse_license_codes_page_callback(): void {
		$save_result = self::handle_save();
		$show_alert  = $save_result['show_alert'];

		$lc_array = self::get_lc_array();

		echo '<div class="pr-5 my-8">';
		printf(
		/*html*/'
		<sl-alert class="mb-8" variant="%1$s" %2$s>
			<sl-icon slot="icon" name="check2-circle"></sl-icon>
			%3$s
		</sl-alert>
',
		$save_result['type'],
		$show_alert ? 'open' : '',
		$save_result['message']
		);
		echo '<div class="grid grid-cols-4 gap-6">';
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
		echo '</div>';
	}

	/**
	 * 儲存表單
	 *
	 * @return array{type: string, message: string, show_alert: bool} 是否儲存
	 */
	private static function handle_save(): array {
		// phpcs:disable
		// 檢查是否提交了表單
		$is_submit = \in_array($_POST['submit_button'] ?? '', ['activate', 'deactivate'], true);
		if (!$is_submit) {
			return [
				'type'    => 'danger',
				'message' => '非表單提交',
				'show_alert' => false,
			];
		}

		$key = self::KEY;

		// 驗證 nonce
		if (!isset($_POST[ "{$key}_nonce" ]) || !\wp_verify_nonce($_POST[ "{$key}_nonce" ], "{$key}_action")) {
			return [
				'type'    => 'danger',
				'message' => '安全檢查失敗',
				'show_alert' => true,
			];
		}



		// 如果是按下棄用按鈕
		if('deactivate' === $_POST['submit_button']){
			$product_key = $_POST['product_key'] ?? '';
			$data = self::deactivate($_POST['code'], $_POST['product_key']);
			$message = self::get_deactivate_message($data, $product_key);
			return array_merge($message, ['show_alert' => true]);
		}


		$data = self::activate($_POST['code'], $_POST['product_key']);

		if(\is_wp_error($data)){
			return [
				'type'    => 'danger',
				'message' => $data->get_error_message(),
				'show_alert' => true,
			];
		}

			$message = self::get_activate_message($data);
			return array_merge($message, ['show_alert' => true]);
		// phpcs:enable
	}

	/**
	 * 取得所有 licenseCode 的 array
	 *
	 * @return array<array{code: string, status: string, expire_date: string, type: string, product_key: string, product_name: string}>
	 */
	public static function get_lc_array(): array {

		/**
		 * @var array<string, array{name?: string, link?: string}>  key:name
		 */
		$product_infos = \apply_filters( 'powerhouse_product_names', [] );

		$default_lc = [
			'code'        => '',
			'status'      => '',
			'expire_date' => '',
			'type'        => '',
		];

		$lc_array = [];

		foreach ( $product_infos as $product_key => $product_info ) {
			$lc           = \get_transient("lc_{$product_key}");
			$product_name = $product_info['name'] ?? '';
			if (!$product_name) {
				continue;
			}
			if (false === $lc) {
				$lc                 = $default_lc;
				$lc['product_key']  = $product_key;
				$lc['product_name'] = $product_name;
				$lc['link']         = $product_info['link'] ?? '';
				// @phpstan-ignore-next-line
				\set_transient("lc_{$product_name}", self::encode($lc), self::CACHE_TIME);

				$lc_array[] = $lc;
				continue;
			}
			// @phpstan-ignore-next-line
			$lc_array[] = self::decode($lc);
		}

		/**
		 * @var array<array{code: string, status: string, expire_date: string, type: string, product_key: string, product_name: string}> $lc_array
		 */
		return $lc_array;
	}

	/**
	 * 啟用授權碼
	 *
	 * @param string $code 授權碼
	 * @param string $product_key 產品 key
	 * @return array|\WP_Error 驗證結果
	 * @phpstan-ignore-next-line
	 */
	public static function activate( string $code, string $product_key ): array|\WP_Error {
		$api      = Api\Base::instance();
		$endpoint = 'license-codes/activate';
		$response = $api->remote_post(
			$endpoint,
			[
				'code'        => $code,
				'product_key' => $product_key,
			]
			);

		return $response;
	}

	/**
	 * 棄用授權碼
	 *
	 * @param string $code 授權碼
	 * @param string $product_key 產品 key
	 * @return array|\WP_Error 驗證結果
	 * @phpstan-ignore-next-line
	 */
	public static function deactivate( string $code, string $product_key ): array|\WP_Error {
		$api      = Api\Base::instance();
		$endpoint = 'license-codes/deactivate';
		$response = $api->remote_post(
			$endpoint,
			[
				'code' => $code,
			]
			);

		return $response;
	}

	/**
	 * 取得啟用訊息
	 *
	 * @param array|\WP_Error $response 回傳的 response
	 * @return array{type: string, message: string} 訊息
	 * @phpstan-ignore-next-line
	 */
	public static function get_activate_message( array|\WP_Error $response ): array {
		if (\is_wp_error($response)) {
			return [
				'type'    => 'danger',
				'message' => $response->get_error_message(),
			];
		}

		$body = \wp_remote_retrieve_body($response);

		/**
		 * @var array{id: int, status: string, code: string, type: string, expire_date: int, domain: string, product_id: int, product_key: string, product_name: string}|array{code: string, message: string, data: array{status: int}} $data 成功|失敗
		 */
		$data = General::json_parse($body, []);

		// get header status code
		$status_code = \wp_remote_retrieve_response_code($response);

		if (200 !== $status_code) {
			return [
				'type'    => 'danger',
				'message' => $data['message'] ?? 'unknown error',
			];
		}

		// @phpstan-ignore-next-line
		self::handle_response($data);

		return [
			'type'    => 'success',
			'message' => '啟用授權成功',
		];
	}

	/**
	 * 取得棄用訊息
	 *
	 * @param array|\WP_Error $response 回傳的 response
	 * @param string          $product_key 產品 key
	 * @return array{type: string, message: string} 訊息
	 * @phpstan-ignore-next-line
	 */
	public static function get_deactivate_message( array|\WP_Error $response, string $product_key ): array {
		if (\is_wp_error($response)) {
			return [
				'type'    => 'danger',
				'message' => $response->get_error_message(),
			];
		}

		$body = \wp_remote_retrieve_body($response);

		/**
		 * @var array{message: string}|array{code: string, message: string, data: array{status: int}} $data 成功|失敗
		 */
		$data = General::json_parse($body, []);

		// get header status code
		$status_code = \wp_remote_retrieve_response_code($response);

		if (200 !== $status_code) {
			return [
				'type'    => 'danger',
				// @phpstan-ignore-next-line
				'message' => $data['message'] ?? 'unknown error',
			];
		}

		\delete_transient("lc_{$product_key}");

		return [
			'type'    => 'success',
			// @phpstan-ignore-next-line
			'message' => $data['message'] ?? '停用授權成功',
		];
	}

	/**
	 * 處理 response
	 *
	 * @param array{id: int, status: string, code: string, type: string, expire_date: int, domain: string, product_id: int, product_key: string, product_name: string} $data 成功
	 * @return void
	 */
	public static function handle_response( array $data ): void {
		$product_key = $data['product_key'];
		\set_transient("lc_{$product_key}", self::encode($data), self::CACHE_TIME);
	}

	/**
	 * 解密
	 *
	 * @param string $value 加密後的 string
	 * @return array{code: string, status: string, expire_date: string, type: string, product_key: string, product_name: string} 單個授權狀態
	 */
	public static function decode( string $value ): array {
		try {

			/**
			 * @var array{code: string, status: string, expire_date: string, type: string, product_key: string, product_name: string} $lc_status
			 */
			$lc_status = \json_decode( $value, true );
			return $lc_status;
			// @phpstan-ignore-next-line
		} catch ( \Exception $e ) {
			ob_start();
			var_dump($e->getMessage());
			\J7\WpUtils\Classes\Log::info('decode error: ' . ob_get_clean());
			return [];
		}
	}

	/**
	 * 加密函數
	 *
	 * @param array{id: int, status: string, code: string, type: string, expire_date: int, domain: string, product_id: int, product_key: string, product_name: string} $license_code 單個授權狀態
	 * @return string 加密後的 string
	 */
	public static function encode( array $license_code ): string {
		return \wp_json_encode( $license_code ) ?: '';
	}
}
