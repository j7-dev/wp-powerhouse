<?php
/**
 * Base
 */

declare ( strict_types=1 );

namespace J7\Powerhouse\Utils;

use J7\Powerhouse\Api;
use J7\Powerhouse\Plugin;

if ( class_exists( 'J7\Powerhouse\Utils\Base' ) ) {
	return;
}

/**
 * Class Base
 */
abstract class Base {
	const PRIMARY_COLOR = 'var(--fallback-p,oklch(var(--p)/1))';
	const APP1_SELECTOR = '#powerhouse_settings';

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

	/**
	 * 簡單加密 array
	 *
	 * @param array<string, mixed> $data 要加密的陣列
	 * @return string 加密後的字串
	 */
	public static function simple_encrypt( array $data ): string {
		// 先將陣列轉成 JSON 字串
		$json_str = json_encode($data, JSON_UNESCAPED_UNICODE);
		// 先轉成 base64
		$encoded = $json_str ? base64_encode($json_str) : '[]';

		// 對每個字元做位移
		$result = '';
		$strlen = strlen($encoded);
		for ($i = 0; $i < $strlen; $i++) {
			$result .= chr(ord($encoded[ $i ]) + 1);
		}

		return $result;
	}

	/**
	 * 渲染 admin layout
	 *
	 * @param array{title: string, id: string} $args 參數
	 * @return void
	 */
	public static function render_admin_layout( array $args ): void {
		Plugin::get('admin-layout', $args, true, true);
	}

	/**
	 * 取得插件連結，用於顯示在 admin-layout 的 admin bar 上
	 *
	 * @return array<array{label: string, url: string, current: bool, disabled: bool}>
	 */
	public static function get_plugin_links(): array {
		$show_plugins   = [
			'powerhouse/plugin.php',
			'power-course/plugin.php',
			'power-docs/plugin.php',
			'power-partner/plugin.php',
			'power-payment/plugin.php',
		];
		$active_plugins = \get_option( 'active_plugins', [] );
		$active_plugins = is_array($active_plugins) ? $active_plugins : [];

		$plugin_links = [];
		foreach ( $show_plugins as $plugin ) {
			$plugin_slug    = str_replace('/plugin.php', '', $plugin);
			$plugin_links[] = [
				'label'    => self::get_plugin_name($plugin_slug),
				'url'      => \admin_url("admin.php?page={$plugin_slug}"),
				'current'  => \is_admin() && @$_GET['page'] === $plugin_slug, // phpcs:ignore
				'disabled' => !in_array($plugin, $active_plugins, true),
			];
		}

		return $plugin_links;
	}

	/**
	 * 取得插件名稱
	 * - 轉成 空白，首字母大寫
	 *
	 * @param string $plugin 插件名稱
	 * @return string
	 */
	public static function get_plugin_name( string $plugin ): string {
		// 把 $plugin 用 - 拆成 array
		$names = explode('-', $plugin);
		// 把每個片段第一個字大寫，並且用空白連接
		$name = implode(' ', array_map(fn( $name ) => ucfirst($name), $names));
		return $name;
	}
}
