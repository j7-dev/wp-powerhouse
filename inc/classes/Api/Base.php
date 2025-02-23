<?php

declare ( strict_types=1 );

namespace J7\Powerhouse\Api;

if ( class_exists( 'J7\Powerhouse\Api\Base' ) ) {
	return;
}
/**
 * Api Base
 * 方便發 API 給 cloud.luke.cafe 或 http://cloud.local
 *
 * @package power-partner 有使用到，修改須注意
 */
final class Base {
	use \J7\WpUtils\Traits\SingletonTrait;

	/** @var string $username */
	private string $username = ''; // @phpstan-ignore-line

	/** @var string $psw */
	private string $psw = ''; // @phpstan-ignore-line

	/** @var string $base_url */
	private string $base_url = ''; // @phpstan-ignore-line

	/**  @var string $api_url Api url 可以透過 WP_ENVIRONMENT_TYPE 調整呼叫本地 API 或 cloud API  */
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
		// 設定環境變數
		$this->init();
	}

	/**
	 * 設定環境變數
	 */
	public function init(): void {

		$env = \wp_get_environment_type();

		$is_home = defined('IS_HOME');

		switch ($env) { // phpcs:ignore
			// local 麗寶之星家裡
			// $username = 'j7.dev.gg';
			// $psw      = '5NTw cqYl uhJU pixF Myj6 rBuA';
			// $base_url = 'https://cloud.local';
			case 'local': // local 辦公室
				$username = $is_home ? 'j7.dev.gg' : 'powerpartner';
				$psw      = $is_home ? '5NTw cqYl uhJU pixF Myj6 rBuA' : 'WDdk K7nm SSNr AwGy Dhab sipK';
				$base_url = $is_home ? 'http://cloud.local' : 'http://cloud.local';
				break;
			case 'staging': // staging 線上測試站
				$username = 'powerpartner';
				$psw      = '9Nve BO2G oe8y B19G SDNd v68Q';
				$base_url = 'https://cloud-staging.wpsite.pro';
				break;
			default: // PROD
				$username = 'powerpartner';
				$psw      = 'uJsk Gu3S pwUG r6ia P9zy Xjrj';
				$base_url = 'https://cloud.luke.cafe';
				break;
		}

		$this->username = $username;
		$this->psw      = $psw;
		$this->base_url = $base_url;
		$this->api_url  = "{$base_url}/wp-json/power-partner-server";

		// @phpstan-ignore-next-line
		$this->default_args = [
			'headers' => [
				'Content-Type'  => 'application/json; charset=UTF-8',
				'Authorization' => 'Basic ' . \base64_encode( $username . ':' . $psw ), // phpcs:ignore
				'Origin'        => \wp_parse_url(\site_url(), PHP_URL_HOST),
			],
			'timeout' => 30, // 30 秒
		];
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
		$config   = $this->default_args;

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
