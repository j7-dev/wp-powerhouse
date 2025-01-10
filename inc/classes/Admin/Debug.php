<?php
/**
 * Debug 選單
 */

declare(strict_types=1);

namespace J7\Powerhouse\Admin;

use J7\WpUtils\Classes\General;

if ( class_exists( 'J7\Powerhouse\Admin\Debug' ) ) {
	return;
}
/**
 * Class Debug
 */
final class Debug {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action( 'admin_menu', [ $this, 'add_debug_submenu_page' ], -10 );
		\add_action('admin_bar_menu', [ $this, 'add_debug_admin_bar_menu' ], 100);
	}

	/**
	 * 添加 debug 子選單
	 *
	 * @return void
	 */
	public function add_debug_submenu_page(): void {
		global $submenu;

		// 檢查 tools.php 選單是否存在
		if (!isset($submenu['tools.php'])) {
			return;
		}

		// 是否已添加過 debug_submenu
		$debug_submenu = General::array_find($submenu['tools.php'], fn( $item ) => $item[2] === 'debug-log-viewer');

		// 沒找到 = 未添加過 debug_submenu
		if (!$debug_submenu) {
			\add_submenu_page(
						'tools.php', // 父選單檔案，指向工具選單
						'Debug Log Viewer', // 頁面標題
						'Debug Log', // 選單標題
						'manage_options', // 所需的權限，例如管理選項
						'debug-log-viewer', // 選單slug
						[ $this, 'debug_log_page_content' ], // 用於渲染頁面內容的回調函數
						1000
				);
		}
	}

	/**
	 * Debug Log Page Content
	 *
	 * @return void
	 */
	public function debug_log_page_content(): void {
		printf(
		/*html*/'
		<div class="wrap">
			<h1>Debug Log</h1>
			<p>只顯示 <code>/wp-content/debug.log</code> 最後 1000 行，
				<a href="%s" target="_blank">下載 debug.log</a>
			</p>
			<p><a href="#bottom">前往底部</a></p>
		</div>
		',
		\site_url( '/wp-content/debug.log' )
		);

		echo '<pre style="line-height: 0.75;">' . $this->read_debug_log() . '</pre>';
		echo '<div id="bottom"></div>'; // 添加底部錨點
	}


	/**
	 * 讀取 debug.log
	 *
	 * @return string
	 */
	private function read_debug_log(): string {
		$log_path = WP_CONTENT_DIR . '/debug.log'; // 使用 WP_CONTENT_DIR 常量定義日誌檔案路徑
		if ( \file_exists( $log_path ) ) { // 檢查檔案是否存在
			$lines       = \file( $log_path ); // 讀取檔案到陣列中，每行是一個陣列元素
			$lines       = is_array($lines) ? $lines : [];
			$last_lines  = \array_slice( $lines, -1000 ); // 獲取最後1000行
			$log_content = \implode( '', $last_lines ); // 將陣列元素合併成字串
			if ( !$log_content ) {
				// 處理讀取錯誤
				return 'Error reading log file.';
			}
			return \nl2br( \esc_html( $log_content ) ); // 將換行符轉換為HTML換行，並轉義內容以避免XSS攻擊
		}
		return 'Log file does not exist.';
	}

	/**
	 * 在 Admin Bar 添加除錯工具連結
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Admin Bar 實例
	 * @return void
	 */
	public function add_debug_admin_bar_menu( \WP_Admin_Bar $wp_admin_bar ): void {
		// 只有管理員可以看到這個選單
		if (!\current_user_can('manage_options')) {
			return;
		}

		// 添加主選單到右側
		$wp_admin_bar->add_node(
			[
				'id'     => 'debug-tools',
				'title'  => 'Logs',
				'parent' => 'top-secondary',
				'href'   => \admin_url('admin.php?page=wc-status&tab=logs&view=single_file&file_id=debugger-' . \wp_date('Y-m-d')),
			]
			);

		// 添加 WC Logger 子選單
		$wp_admin_bar->add_node(
			[
				'id'     => 'wc-logger',
				'parent' => 'debug-tools',
				'title'  => 'WC Logger',
				'href'   => \admin_url('admin.php?page=wc-status&tab=logs'),
			]
			);

		// 添加 Debug Log 子選單
		$wp_admin_bar->add_node(
			[
				'id'     => 'debug-log-viewer',
				'parent' => 'debug-tools',
				'title'  => 'Debug Log',
				'href'   => \admin_url('tools.php?page=debug-log-viewer'),
			]
			);
	}
}
