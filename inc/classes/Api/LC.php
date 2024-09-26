<?php
/**
 * LC Api
 */

declare( strict_types=1 );

namespace J7\Powerhouse\Api;

use J7\Powerhouse\Plugin;
use J7\Powerhouse\LC as LC_CLASS;

/**
 * Class LC
 */
final class LC {
	use \J7\WpUtils\Traits\SingletonTrait;
	use \J7\WpUtils\Traits\ApiRegisterTrait;

	/**
	 * APIs
	 *
	 * @var array<int, array{endpoint: string, method: string, permission_callback?:callable}>
	 */
	protected $apis = [
		[
			'endpoint' => 'lc/invalidate',
			'method'   => 'post',
		],
	];

	/**
	 * Constructor.
	 */
	public function __construct() {
		\add_action( 'rest_api_init', [ $this, 'register_api_license_codes' ] );
	}

	/**
	 * Register products API
	 *
	 * @return void
	 */
	public function register_api_license_codes(): void {
		$this->register_apis(
		apis: $this->apis,
		namespace: Plugin::$kebab,
		default_permission_callback: '__return_true',
		);
	}

	/**
	 * 清除 LC 快取
	 *
	 * @param \WP_REST_Request $request 包含新增用戶所需資料的REST請求對象。
	 * @return \WP_REST_Response 返回包含操作結果的REST響應對象。成功時返回用戶資料，失敗時返回錯誤訊息。
	 * @phpstan-ignore-next-line
	 */
	public function post_lc_invalidate_callback( \WP_REST_Request $request ): \WP_REST_Response {
		$body_params  = $request->get_json_params();
		$product_slug = $body_params['product_slug'];
		if ( ! $product_slug ) {
			return new \WP_REST_Response(
				[
					'code'    => 'invalidate_lc_cache_failed',
					'message' => '產品 Slug 不能為空',
				],
				400
			);
		}

		$delete_transient_result = LC_CLASS::delete_lc_transient( $product_slug );

		return new \WP_REST_Response(
			[
				'code'    => 'invalidate_lc_cache_success',
				'message' => '清除快取成功',
				'data'    => [
					'delete_transient_result' => $delete_transient_result,
					'product_slug'            => $product_slug,
				],
			],
			200
			);
	}
}
