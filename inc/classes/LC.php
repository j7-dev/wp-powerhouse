<?php
/**
 * LicenseCodes
 * 1. 先用 powerhouse_product_names filter hook 註冊 product_slug 和 product_name
 * 2. 每個 transient 會記錄各個 product 的授權狀態，加密後存為 string
 */

declare (strict_types = 1);

namespace J7\Powerhouse;

use J7\Powerhouse\Plugin;
use J7\WpUtils\Classes\General;
use Nullix\JsAesPhp\JsAesPhp;


if ( class_exists( 'J7\Powerhouse\LC' ) ) {
	return;
}
/**
 * Class LC
 */
final class LC {

	const KEY        = 'powerhouse_license_codes';
	const CACHE_TIME = 24 * HOUR_IN_SECONDS;

	/**
	 * Render Powerhouse Page Callback
	 */
	public static function powerhouse_license_codes_page_callback(): void {
		$save_result = self::handle_save();

		// 如果啟用成功，自動刷新頁面
		if ('success' === $save_result['type']) {
			\wp_safe_redirect(\admin_url('admin.php?page=powerhouse-license-codes'));
			exit;
		}

		$show_alert = $save_result['show_alert'];
		$lc_array   = self::get_lc_array();

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
		echo '<div class="flex flex-wrap gap-6">';
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
		$code = str_replace(' ', '', $_POST['code'] ?? '');
		$product_slug =str_replace(' ', '', $_POST['product_slug'] ?? '');

		// 如果是按下棄用按鈕
		if('deactivate' === $_POST['submit_button']){
			$data = self::deactivate($code, $product_slug);
			$message = self::get_deactivate_message($data, $product_slug);
			return array_merge($message, ['show_alert' => true]);
		}
		// phpcs:enable

		// 如果是按下啟用按鈕
		$data = self::activate($code, $product_slug);

		$message = self::get_activate_message($data);
		return array_merge($message, [ 'show_alert' => true ]);
	}

	/**
	 * 取得所有 licenseCode 的 array
	 * 發起檢查
	 *
	 * @return array<array{code: string, post_status: string, expire_date: string, type: string, product_slug: string, product_name: string}>
	 */
	public static function get_lc_array(): array {

		/**
		 * @var array<string, array{name?: string, link?: string}>  key,info
		 */
		$product_infos = \apply_filters( 'powerhouse_product_infos', [] );

		// 存在 db 的 product_slug 和 code
		/**
		 * @var array<string, string> $saved_codes 產品 key 和 code
		 */
		$saved_codes = \get_option(self::KEY, []);

		if (!is_array($saved_codes)) {
			$saved_codes = [];
		}

		$lc_array = [];

		foreach ( $product_infos as $product_slug => $product_info ) {
			$saved_code   = $saved_codes[ $product_slug ] ?? '';
			$lc           = \get_transient("lc_{$product_slug}");
			$product_name = $product_info['name'] ?? '';
			if (!$product_name) {
				continue;
			}

			$decoded = false;
			if (false !== $lc) {
				$decoded = self::decode( (string) $lc);
			}

			$default_lc = self::get_default_lc($product_slug, $product_name, $product_info);
			// 如果 transient 不存在|過期，且 saved_code 不存在，則新增預設的 transient
			if (!is_array($decoded) && !$saved_code ) {
				self::delete_lc_transient($product_slug);
				$lc_array[] = $default_lc;
				continue;
			}

			// 如果 transient 不存在|過期，且 saved_code 存在，則重新發 API 獲取
			if (!is_array($decoded) && $saved_code ) {
				$response = self::activate( (string) $saved_code, $product_slug, true);
				if (\is_wp_error($response)) {
					$default_lc['code']        = '500 ' . $response->get_error_message();
					$default_lc['post_status'] = 'activated';
					$lc_array[]                = $default_lc;
					// 失敗的話，就先暫存一個預設啟用值，等於說跳過這次，下次到期再檢查
					self::set_lc_transient($default_lc);
					// 失敗不清除原本的 saved_code
					// self::delete_lc_transient($product_slug);
					continue;
				}

				// get header status code
				$status_code = \wp_remote_retrieve_response_code($response);

				if (200 !== $status_code) {
					$default_lc['code']        = (string) $status_code;
					$default_lc['post_status'] = 'activated';
					$lc_array[]                = $default_lc;
					// 失敗的話，就先暫存一個預設啟用值，等於說跳過這次，下次到期再檢查
					self::set_lc_transient($default_lc);
					// 失敗不清除原本的 saved_code
					// self::delete_lc_transient($product_slug);
					continue;
				}

				$body = \wp_remote_retrieve_body($response);

				/**
				 * @var array{id: int, post_status: string, code: string, type: string, expire_date: int, domain: string, product_id: int, product_slug: string, product_name: string}|array{code: string, message: string, data: array{status: int}} $data 成功|失敗
				 */
				$data = General::json_parse($body, []);

				// @phpstan-ignore-next-line
				self::set_lc_transient($data);

				$lc_array[] = $data;
				continue;
			}

			// 如果 transient 存在
			$lc_array[] = $decoded;
		}

		/**
		 * @var array<array{code: string, post_status: string, expire_date: string, type: string, product_slug: string, product_name: string}> $lc_array
		 */
		return $lc_array;
	}

	/**
	 * 啟用授權碼
	 *
	 * @param string $code 授權碼
	 * @param string $product_slug 產品 key
	 * @param bool   $is_system_check 是否為系統檢查
	 * @return array|\WP_Error 驗證結果
	 * @phpstan-ignore-next-line
	 */
	public static function activate( string $code, string $product_slug, bool $is_system_check = false ): array|\WP_Error {
		$api      = Api\Base::instance();
		$endpoint = 'license-codes/activate';

		$params = [
			'code'         => $code,
			'product_slug' => $product_slug,
		];
		if ($is_system_check) {
			$params['post_status'] = [ 'available', 'activated' ];
		}
		$response = $api->remote_post(
			$endpoint,
			$params
		);

		return $response;
	}

	/**
	 * 棄用授權碼
	 *
	 * @param string $code 授權碼
	 * @param string $product_slug 產品 key
	 * @return array|\WP_Error 驗證結果
	 * @phpstan-ignore-next-line
	 */
	public static function deactivate( string $code, string $product_slug ): array|\WP_Error {
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
		 * @var array{id: int, post_status: string, code: string, type: string, expire_date: int, domain: string, product_id: int, product_slug: string, product_name: string}|array{code: string, message: string, data: array{status: int}} $data 成功|失敗
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
		self::set_lc_transient($data);

		return [
			'type'    => 'success',
			'message' => '啟用授權成功，重新整理頁面後就可以使用',
		];
	}

	/**
	 * 取得棄用訊息
	 *
	 * @param array|\WP_Error $response 回傳的 response
	 * @param string          $product_slug 產品 key
	 * @return array{type: string, message: string} 訊息
	 * @phpstan-ignore-next-line
	 */
	public static function get_deactivate_message( array|\WP_Error $response, string $product_slug ): array {
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

		self::delete_lc_transient($product_slug);

		if (200 !== $status_code) {
			return [
				'type'    => 'danger',
				// @phpstan-ignore-next-line
				'message' => $data['message'] ?? 'unknown error',
			];
		}

		return [
			'type'    => 'success',
			// @phpstan-ignore-next-line
			'message' => $data['message'] ?? '停用授權成功',
		];
	}

	/**
	 * 設置預設的 transient
	 *
	 * @param string               $product_slug 產品 key
	 * @param string               $product_name 產品名稱
	 * @param array{link?: string} $product_info 產品資訊
	 * @return array{code: string, post_status: string, expire_date: string, type: string, product_slug: string, product_name: string} 單個授權狀態
	 */
	public static function get_default_lc( string $product_slug, string $product_name, array $product_info ): array {
		$default_lc = [
			'code'        => '',
			'post_status' => '',
			'expire_date' => '',
			'type'        => '',
		];

		$lc                 = $default_lc;
		$lc['product_slug'] = $product_slug;
		$lc['product_name'] = $product_name;
		$lc['link']         = $product_info['link'] ?? '';
		return $lc;
	}

	/**
	 * 設置 LC transient
	 * 儲存 product_slug 和 code 到 option
	 *
	 * @param array{id?: int, post_status: string, code: string, type: string, expire_date: int|string, domain?: string, product_id?: int, product_slug: string, product_name: string} $data 成功
	 * @return void
	 */
	public static function set_lc_transient( array $data ): void {
		$product_slug = $data['product_slug'];
		/**
		 * @var array<string, string> $saved_codes 產品 key 和 code
		 */
		$saved_codes = \get_option(self::KEY, []);
		if (!is_array($saved_codes)) {
			$saved_codes = [];
		}
		$saved_codes[ $product_slug ] = $data['code'];

		\update_option(self::KEY, $saved_codes);
		\set_transient("lc_{$product_slug}", self::encode($data), self::CACHE_TIME);
	}


	/**
	 * 刪除 LC transient
	 * 刪除 product_slug 和 code 到 option
	 *
	 * @param string $product_slug 產品 slug
	 * @return bool 是否刪除成功
	 */
	public static function delete_lc_transient( string $product_slug ): bool {
		/**
		 * @var array<string, string> $saved_codes 產品 key 和 code
		 */
		$saved_codes = \get_option(self::KEY, []);
		if (!is_array($saved_codes)) {
			$saved_codes = [];
		}
		unset($saved_codes[ $product_slug ]);
		\update_option(self::KEY, $saved_codes);
		$delete_transient_result = \delete_transient("lc_{$product_slug}");
		return $delete_transient_result;
	}

	/**
	 * 解密
	 *
	 * @param string $value 加密後的 string
	 * @return array{code: string, post_status: string, expire_date: string, type: string, product_slug: string, product_name: string}|false 單個授權狀態，false 表示解密失敗
	 */
	public static function decode( string $value ): array|false {

		try {
			/**
		 * @var array{code: string, post_status: string, expire_date: string, type: string, product_slug: string, product_name: string} $lc_status
		 */
			$lc_status = JsAesPhp::decrypt($value, Plugin::$kebab);

			if (!is_array($lc_status)) {
				return false;
			}

			return $lc_status;
		} catch ( \Error $e ) {
			\J7\WpUtils\Classes\ErrorLog::info('decode error: ' . $value . $e->getMessage());
			return false;
		}
	}

	/**
	 * 加密函數
	 *
	 * @param array{id?: int, post_status: string, code: string, type: string, expire_date: int|string, domain?: string, product_id?: int, product_slug: string, product_name: string} $license_code 單個授權狀態
	 * @return string|false 加密後的 string，失敗回傳 false
	 */
	public static function encode( array $license_code ): string|false {
		return JsAesPhp::encrypt($license_code, Plugin::$kebab, 1);
	}

	/**
	 * @param string $product_slug 產品 key
	 * @return bool ia
	 */
	public static function ia( string $product_slug ): bool {
		$lc_string = \get_transient("lc_{$product_slug}");

		if (false !== $lc_string) {
			$lc = self::decode( (string) $lc_string);

			if (!is_array($lc)) {
				return false;
			}
			if ('activated' === ( $lc['post_status'] ?? '' )) { // @phpstan-ignore-line
				return true;
			}
		}
		return false;
	}
}
