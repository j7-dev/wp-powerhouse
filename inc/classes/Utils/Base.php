<?php
/**
 * Base
 */

declare ( strict_types=1 );

namespace J7\Powerhouse\Utils;

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
			'power-shop/plugin.php',
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

	/**
	 * 通用批次處理高階函數
	 *
	 * @param array<int, mixed> $items 需要處理的項目陣列
	 * @param callable          $callback 處理每個項目的回調函數，接收項目和索引參數，回傳布林值表示成功或失敗
	 * @param array{
	 *  batch_size: int,
	 *  pause_ms: int,
	 *  flush_cache: bool,
	 * }    $options 設定選項
	 * @return array 處理結果統計
	 */
	public static function batch_process( $items, $callback, $options = [] ) {
		// 默认选项
		$default_options = [
			'batch_size'  => 10,  // 每批次處理的項目數量
			'pause_ms'    => 750, // 每批次之間暫停的毫秒數
			'flush_cache' => true, // 每批次後是否清除 WordPress 快取
		];

		// 合併選項
		$options = \wp_parse_args( $options, $default_options );

		// 初始化結果統計
		$result = [
			'total'        => count($items),
			'success'      => 0,
			'failed'       => 0,
			'failed_items' => [],
		];

		// 分批處理
		$batches = array_chunk($items, $options['batch_size']);

		foreach ($batches as $batch_index => $batch) {
			// 處理每一批
			foreach ($batch as $index => $item) {
				$success = call_user_func($callback, $item, $index);

				if ($success) {
					++$result['success'];
				} else {
					++$result['failed'];
					$result['failed_items'][] = $item;
				}
			}

			// 如果不是最後一批，執行批次間操作
			if ($batch_index < count($batches) - 1) {
				// 清除快取，釋放記憶體
				if ($options['flush_cache']) {
					\wp_cache_flush();
				}

				// 暫停指定時間
				if ($options['pause_ms'] > 0) {
					usleep($options['pause_ms'] * 1000); // 轉換為微秒
				}
			}
		}

		return $result;
	}
}
