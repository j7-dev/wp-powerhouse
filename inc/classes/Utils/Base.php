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
	 * @param string   $env 環境名稱
	 * @param Api\Base $api 實例
	 * @return void
	 */
	public static function set_api_auth( ?string $env = 'prod', Api\Base $api ): void {

		switch ($env) { // phpcs:ignore
			case 'local': // LOCAL
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
	}
}
