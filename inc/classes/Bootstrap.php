<?php
/**
 * Bootstrap
 */

declare (strict_types = 1);

namespace J7\Powerhouse;

if ( class_exists( 'J7\Powerhouse\Bootstrap' ) ) {
	return;
}
/**
 * Class Bootstrap
 */
final class Bootstrap {
	use \J7\WpUtils\Traits\SingletonTrait;

	const KEY = 'powerhouse_settings';

	/**
	 * @var array
	 * Store instances of classes
	 */
	public $instances = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->instances['Admin\OrderDetail'] = Admin\OrderDetail::instance();
		$this->instances['Admin\OrderList']   = Admin\OrderList::instance();
		$this->instances['Admin\DelayEmail']  = Admin\DelayEmail::instance();

		\add_action( 'admin_menu', [ __CLASS__ , 'add_power_plugin_menu' ], 10 );
		\add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
	}

	/**
	 * 取得設定值
	 *
	 * @param string|null $key 設定值的鍵
	 *
	 * @return mixed 設定值
	 */
	public static function get( ?string $key = null ) {

		$default_value = [
			'delay_email' => 'yes',
		];

		$settings = \get_option(self::KEY, $default_value);
		$settings = \wp_parse_args($settings, $default_value);

		if (!$key) {
			return $settings;
		}

		return $settings[ $key ] ?? '';
	}

	/**
	 * Add Power Plugin Menu
	 */
	public static function add_power_plugin_menu(): void {
		\add_menu_page(
		__( 'Powerhouse', 'power_house' ),
		__( 'Powerhouse', 'power_house' ),
		'manage_options',
		'power_house',
		[ __CLASS__, 'power_house_page_callback' ],
		'dashicons-superhero',
		3
		);

		\add_submenu_page( 'power_house', __( '其他', 'power_house' ), __( '其他', 'power_house' ), 'manage_options', 'power_house', [ __CLASS__, 'power_house_page_callback' ], 1000 );
	}

	/**
	 * Render Powerhouse Page Callback
	 */
	public static function power_house_page_callback(): void {
		$key      = self::KEY;
		$fields   = [ 'delay_email' ];
		$is_saved = self::handle_save($fields);

		foreach ($fields as $field) {
			$$field = self::get( $field );
		}

		printf(
		/*html*/'
		<div id="powerhouse-settings" class="tailwind">
			<form id="powerhouse-settings-form" class="pr-5 mt-8" method="post" action="">
				<sl-alert variant="success" %1$s>
					<sl-icon slot="icon" name="check2-circle"></sl-icon>
					儲存成功
				</sl-alert>
				%2$s
				<div class="grid grid-cols-[20rem_1fr] gap-4">
					<div>
						<p class="text-sm text-gray-800 font-bold mt-0 mb-2">使用非同步方式寄送 Email，加快結帳速度</p>
						<p class="text-xs text-gray-400 mt-0 mb-2">可以前往 <a href="%3$s" target="_blank">Scheduled Actions</a> 查看信件寄送的狀況</p>
					</div>
					<div>
						<sl-switch class="block" name="%4$s" value="yes" %5$s></sl-switch>
					</div>
				</div>
				<sl-button class="mt-12" type="submit" variant="primary" name="submit_button" value="1">儲存</sl-button>
			</form>
		</div>
		',
		$is_saved ? 'open' : '',
		\wp_nonce_field("{$key}_action", "{$key}_nonce", true, false),
		\admin_url('admin.php?page=wc-status&tab=action-scheduler&s=powerhouse_delay_email&action=-1&paged=1&action2=-1'),
		$fields[0],
		\checked($delay_email, 'yes', false),
		);
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public static function enqueue_assets(): void {
		\wp_enqueue_script(
		Plugin::$kebab,
		Plugin::$url . '/inc/assets/dist/index.js',
		[ 'jquery' ],
		Plugin::$version,
		[
			'strategy' => 'defer',
		]
		);

		\wp_enqueue_style( Plugin::$snake, Plugin::$url . '/inc/assets/dist/css/index.css', [], Plugin::$version );

		// CDN shoelace
		// phpcs:disable
		echo '
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.16.0/cdn/themes/light.css" />
		<script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.16.0/cdn/shoelace-autoloader.js"></script>
		';
		// phpcs:enable
	}

	/**
	 * 儲存表單
	 *
	 * @param array<string> $fields 表單欄位
	 *
	 * @return bool 是否儲存
	 */
	private static function handle_save( $fields = [] ): bool {
		// phpcs:disable
		// 檢查是否提交了表單
		if (($_POST['submit_button'] ?? '') !== '1' || !$fields) {
			return false;
		}

		$key = self::KEY;

		// 驗證 nonce
		if (!isset($_POST[ "{$key}_nonce" ]) || !\wp_verify_nonce($_POST[ "{$key}_nonce" ], "{$key}_action")) {
			\wp_die('安全檢查失敗');
		}

		// 獲取並清理表單數據
		$data = [];
		foreach ($fields as $field) {
			$data[ $field ] = \sanitize_text_field($_POST[ $field ] ?? '');
		}

		return \update_option($key, $data);
		// phpcs:enable
	}
}
