<?php
/**
 * Debug 選單
 */

declare(strict_types=1);

namespace J7\Powerhouse\Admin;

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
	}


	/**
	 * 添加 debug 子選單
	 *
	 * @return void
	 */
	public function add_debug_submenu_page(): void {
		global $submenu;

		// 检查 tools.php 菜单是否存在
		if (isset($submenu['tools.php'])) {
			$debug_log_exists = false;

			// 遍历 tools.php 的子菜单
			foreach ($submenu['tools.php'] as $item) {
				// 检查是否已存在 debug-log-viewer
				if ($item[2] === 'debug-log-viewer') {
					$debug_log_exists = true;
					break;
				}
			}

			// 如果 debug-log-viewer 不存在，则添加子菜单
			if (!$debug_log_exists) {
				\add_submenu_page(
							'tools.php', // 父菜单文件，指向工具菜单
							'Debug Log Viewer', // 页面标题
							'Debug Log', // 菜单标题
							'manage_options', // 所需的权限，例如管理选项
							'debug-log-viewer', // 菜单slug
							[ $this, 'debug_log_page_content' ], // 用于渲染页面内容的回调函数
							1000
					);
			}
		}
	}

	/**
	 * Debug Log Page Content
	 *
	 * @return void
	 */
	public function debug_log_page_content(): void {
		// 这里是渲染内容的函数，可以调用之前创建的 read_debug_log() 函数
		printf(
		/*html*/'
		<div class="wrap">
			<h1>Debug Log</h1>
			<p>只顯示 <code>/wp-content/debug.log</code> 最後 1000 行，
				<a href="%s" target="_blank">下載 debug.log</a>
			</p>
		</div>
		',
		\site_url( '/wp-content/debug.log' )
		);

		echo '<pre style="line-height: 0.75;">' . $this->read_debug_log() . '</pre>';
	}


	/**
	 * 讀取 debug.log
	 *
	 * @return string
	 */
	private function read_debug_log(): string {
		$log_path = WP_CONTENT_DIR . '/debug.log'; // 使用 WP_CONTENT_DIR 常量定义日志文件路径
		if ( \file_exists( $log_path ) ) { // 检查文件是否存在
			$lines       = \file( $log_path ); // 读取文件到数组中，每行是一个数组元素
			$last_lines  = \array_slice( $lines, -1000 ); // 获取最后1000行
			$log_content = \implode( '', $last_lines ); // 将数组元素合并成字符串
			if ( !$log_content ) {
				// 处理读取错误
				return 'Error reading log file.';
			}
			return \nl2br( \esc_html( $log_content ) ); // 将换行符转换为HTML换行，并转义内容以避免XSS攻击
		}
		return 'Log file does not exist.';
	}
}
