<?php
/**
 * Api
 * TODO 加密
 */

declare ( strict_types=1 );

namespace J7\Powerhouse\Api;

use J7\Powerhouse\Utils;

if ( class_exists( 'J7\Powerhouse\Api\Base' ) ) {
	return;
}
/**
 * Class Base
 */
final class Base {
	use \J7\WpUtils\Traits\SingletonTrait;

	public $username = ''; // phpcs:ignore
	public $psw      = ''; // phpcs:ignore
	public $base_url = ''; // phpcs:ignore

	/**
	 * Api url
	 * 可以透過 Plugin::$is_local 調整呼叫本地 API 或 cloud API
	 *
	 * @var string $api_url
	 */
	public $api_url;

	/**
	 * 預設的 API 參數
	 * body 若要夾帶參數，需要自己額外加上
	 * 'body'    => \wp_json_encode( $props )
	 *
	 * @var array{body?: string, headers: array<string, string>, timeout: int} $default_args
	 */
	public $default_args;

	/**
	 * Constructor
	 */
	public function __construct() {
		// TODO 環境變數
		Utils\Base::set_api_auth( $this, 'staging');
	}

	/**
	 * 發送 GET 請求
	 *
	 * @param string $endpoint 請求路徑
	 * @param array  $url_params 請求參數
	 * @return array|\WP_Error
	 * @phpstan-ignore-next-line
	 */
	public function remote_get( string $endpoint, array $url_params = [] ): array|\WP_Error {
		$endpoint = "{$this->api_url}/{$endpoint}";
		$endpoint = \add_query_arg($url_params, $endpoint);

		$config = $this->default_args;

		// @phpstan-ignore-next-line
		$response = \wp_remote_get($endpoint, $config);

		return $response;
	}

	/**
	 * 發送 POST 請求
	 *
	 * @param string $endpoint 請求路徑
	 * @param array  $body_params 請求參數
	 * @return array|\WP_Error
	 * @phpstan-ignore-next-line
	 */
	public function remote_post( string $endpoint, array $body_params = [] ): array|\WP_Error {
		$endpoint = "{$this->api_url}/{$endpoint}";
		ob_start();
		var_dump( $this->default_args);
		\J7\WpUtils\Classes\Log::info('' . ob_get_clean());
		$default_args = $this->default_args;
		$args         = $body_params ? \array_merge(
			$default_args,
			[
				'body' => \wp_json_encode($body_params),
			]
			) : $default_args;

		// @phpstan-ignore-next-line
		$response = \wp_remote_post($endpoint, $args);

		return $response;
	}

	/**
	 * 發送 DELETE 請求
	 *
	 * @param string $endpoint 請求路徑
	 * @param array  $body_params 請求參數
	 * @return array|\WP_Error
	 * @phpstan-ignore-next-line
	 */
	public function remote_delete( string $endpoint, array $body_params = [] ): array|\WP_Error {
		$endpoint     = "{$this->api_url}/{$endpoint}";
		$default_args = $this->default_args;
		$args         = $body_params ? \array_merge(
			$default_args,
			[
				'body' => \wp_json_encode($body_params),
			]
			) : $default_args;

		$args['method'] = 'DELETE';

		// @phpstan-ignore-next-line
		$response = \wp_remote_request($endpoint, $args);

		return $response;
	}
}
