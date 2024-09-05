<?php
/**
 * Api
 */

declare ( strict_types=1 );

namespace J7\Powerhouse\Api;

if ( class_exists( 'J7\Powerhouse\Api\Base' ) ) {
	return;
}
/**
 * Class Base
 */
final class Base {
	use \J7\WpUtils\Traits\SingletonTrait;

	private const USER_NAME = 'j7.dev.gg';
	private const PASSWORD  = 'YQLj xV2R js9p IWYB VWxp oL2E';

	/**
	 * Base url
	 *
	 * @var string $base_url
	 */
	private $base_url;

	/**
	 * Api url
	 * 可以透過 Plugin::$is_local 調整呼叫本地 API 或 cloud API
	 *
	 * @var string $api_url
	 */
	private $api_url;

	/**
	 * 預設的 API 參數
	 * body 若要夾帶參數，需要自己額外加上
	 * 'body'    => \wp_json_encode( $props )
	 *
	 * @var array{body?: string, headers: array<string, string>, timeout: int} $default_args
	 */
	private $default_args;

	/**
	 * Constructor
	 */
	public function __construct() {
		// TEST START
		\add_filter(
				'powerhouse_product_names',
				function ( $names ) {
					return $names + [
						'power-course' => 'Power Course',
					]; }
				);
		// TEST END

		$this->base_url = WP_DEBUG ? 'http://cloud.local' : 'https://cloud.luke.cafe';
		$this->api_url  = "{$this->base_url}/wp-json/power-partner-server";
		// @phpstan-ignore-next-line
		$this->default_args = [
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Basic ' . \base64_encode( Base::USER_NAME . ':' . Base::PASSWORD ), // phpcs:ignore
				'Origin'        => \wp_parse_url(\site_url(), PHP_URL_HOST),
			],
			'timeout' => 30, // 30 秒
		];
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
		$endpoint     = "{$this->api_url}/{$endpoint}";
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
}
