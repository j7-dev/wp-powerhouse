<?php
/**
 * Base
 */

declare ( strict_types=1 );

namespace J7\Powerhouse\Utils;

use J7\Powerhouse\Api;

if ( class_exists( 'J7\Powerhouse\Utils\Base' ) ) {
	return;
}

/**
 * Class Base
 */
abstract class Base {
	public const PRIMARY_COLOR = '#0284c7'; // '#1677ff';

	/**
	 * Set API auth
	 *
	 * @param Api\Base    $api 實例
	 * @param string|null $env 環境名稱

	 * @return void
	 */
	public static function set_api_auth( Api\Base $api, ?string $env = null ): void {

		if (!defined('WP_ENVIRONMENT_TYPE')) {
			define('WP_ENVIRONMENT_TYPE', 'production');
		}

		$env = $env ?? WP_ENVIRONMENT_TYPE;

		switch ($env) { // phpcs:ignore
			case 'local': // LOCAL 麗寶之星家裡
				$username = 'j7.dev.gg';
				$psw      = '5NTw cqYl uhJU pixF Myj6 rBuA';
				$base_url = 'http://cloud.local';
				break;
			case 'local-company': // LOCAL 辦公室
				$username = 'powerpartner';
				$psw      = 'WDdk K7nm SSNr AwGy Dhab sipK';
				$base_url = 'http://cloud.local';
				break;
			case 'staging': // STAGING
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

		$api->username = $username;
		$api->psw      = $psw;
		$api->base_url = $base_url;
		$api->api_url  = "{$base_url}/wp-json/power-partner-server";
		// @phpstan-ignore-next-line
		$api->default_args = [
			'headers' => [
				'Content-Type'  => 'application/json; charset=UTF-8',
				'Authorization' => 'Basic ' . \base64_encode( $username . ':' . $psw ), // phpcs:ignore
				'Origin'        => \wp_parse_url(\site_url(), PHP_URL_HOST),
			],
			'timeout' => 30, // 30 秒
		];
	}

	/**
	 * 取得模組的 URL
	 *
	 * @param string $module_name 模組名稱
	 * @return string
	 */
	public static function get_module_url( string $module_name ): string {
		$base_url = \admin_url('admin.php');
		$url      = \add_query_arg(
			[
				'page'   => 'powerhouse',
				'module' => $module_name,
			],
			$base_url
			);

		return $url;
	}
}
