<?php

declare(strict_types=1);

namespace J7\Powerhouse\Captcha;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use J7\Powerhouse\Settings\DTO as SettingsDTO;

/**
 * Class Core
 *
 * @see https://packagist.org/packages/gregwar/captcha
 */
final class Core {
	use \J7\WpUtils\Traits\SingletonTrait;

	/** @var string 驗證碼欄位名稱 */
	const CAPTCHA_NAME = 'powerhouse_captcha';

	/** @var string 驗證碼 session 鍵名 */
	const SESSION_KEY = 'powerhouse_phrase';

	/** @var int 驗證碼長度 */
	private $length = 4;

	/** @var string 驗證碼字符集不支援中文、特殊字符 */
	private $charset = '0123456789';

	/** @var string 驗證碼 */
	private $phrase;

	/** @var CaptchaBuilder 實例 */
	private $builder;

	/** @var PhraseBuilder 實例 */
	private $phrase_builder;

	/** @var string 驗證碼 nonce 名稱 */
	const NONCE_ACTION = 'powerhouse_captcha_nonce';

	/** Constructor */
	public function __construct() {
		$settings = SettingsDTO::instance();
		if ($settings->enable_captcha !== 'yes') {
			return;
		}

		\add_filter('authenticate', [ $this, 'authenticate' ], 999, 3);
		\add_action('init', [ $this, 'session_start' ], 1);

		// render phase
		\add_action('login_form', [ $this, 'render_captcha_field' ]);
		\add_action('woocommerce_login_form', [ $this, 'render_captcha_field' ]);

		// ajax
		\add_action('wp_ajax_nopriv_get_captcha', [ $this, 'generate_captcha' ]);
	}

	/** 驗證碼驗證
	 *
	 * @param null|\WP_User|\WP_Error $user    用戶資料
	 * @param string                  $username 用戶名稱
	 * @param string                  $password 用戶密碼
	 * @return null|\WP_User|\WP_Error
	 */
	public function authenticate( null|\WP_User|\WP_Error $user, string $username, string $password ) {
		if (!( $user instanceof \WP_User )) {
			return $user;
		}

		if (!$this->in_role_list($user)) {
			return $user;
		}

		$user_input = $_POST[ self::CAPTCHA_NAME ] ?? ''; // phpcs:ignore
		if (!$this->test_phrase($user_input)) {
			return new \WP_Error('captcha_failed', '驗證碼錯誤');
		}
		// 以下就代表驗證成功了
		return $user;
	}

	/** 開啟 session 支持 */
	public function session_start(): void {
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
	}

	/** 渲染驗證碼欄位 */
	public function render_captcha_field(): void {
		$name = self::CAPTCHA_NAME;

		// render
		printf(/*html*/'<label for="%s">驗證碼</label>', $name);
		printf(
			/*html*/'
				<p>
					<div id="powerhouse_captcha_container" style="display: none; align-items: end; gap: 10px; margin-bottom: 10px;">
						<img />
						<span id="powerhouse_captcha_refresh" style="cursor: pointer;">換一組</span>
					</div>
						<input type="text" name="%1$s" id="%1$s" class="input" />
				</p>
				',
			$name
			);

		?>
			<script type="module" defer>
				(function($){
					$(document).ready(function(){
						const $container = $('#powerhouse_captcha_container');
						const $img = $container.find('img');
						const $refresh = $container.find('#powerhouse_captcha_refresh');

						function getCaptcha(){
							$.ajax({
								url: '<?php echo \admin_url('admin-ajax.php'); ?>',
								type: 'POST',
								data: {
									action: 'get_captcha',
									nonce: '<?php echo \wp_create_nonce(self::NONCE_ACTION); ?>',
								},
								success: function(response){
									if(!response?.url){
										alert('獲取驗證碼生成失敗');
										return;
									}
									$img.attr('src', response?.url);
									$container.css('display', 'flex');
								},
								error: function(response){
									alert('獲取驗證碼生成失敗');
									return;
								},
								});
						}

						$refresh.on('click', getCaptcha);
						getCaptcha();
					});
				})(jQuery);
			</script>
		<?php
	}

	/** AJAX 生成驗證碼 */
	public function generate_captcha(): void {
		if (!\wp_verify_nonce($_POST['nonce'] ?? '', self::NONCE_ACTION)) { // phpcs:ignore
			\wp_send_json(
				[
					'error' => '驗證 nonce 錯誤',
				]
				);
		}

		$this->init();

		\wp_send_json(
			[
				'phrase' => $_SESSION[ self::SESSION_KEY ], // phpcs:ignore
				'url' => $_SESSION[ self::SESSION_KEY . '_url' ], // phpcs:ignore
			]
			);
	}


	/** 初始化 */
	private function init(): void {
		$this->phrase_builder = new PhraseBuilder($this->length, $this->charset);
		$this->builder        = new CaptchaBuilder(null, $this->phrase_builder);
		$this->builder->setInterpolation(false); // 更快、圖更醜
		$this->builder->build(); // 生成圖片

		// $builder->save('test/out.jpg');  // 保存圖片，從網站跟目錄開始的 folder，目錄必須存在

		// 存入 session
		$_SESSION[ self::SESSION_KEY ]          = $this->builder->getPhrase();
		$_SESSION[ self::SESSION_KEY . '_url' ] = $this->builder->inline();
	}

	/**
	 * 測試驗證碼
	 *
	 * @param string $user_input 用戶輸入的驗證碼
	 * @return bool
	 */
	private function test_phrase( string $user_input ): bool {
		if (!isset($_SESSION[ self::SESSION_KEY ])) {
			return false;
		}
		return $user_input == $_SESSION[ self::SESSION_KEY ]; // phpcs:ignore
	}

	/**
	 * 檢查用戶是否在角色列表中
	 *
	 * @param \WP_User $user 用戶
	 * @return bool
	 */
	private function in_role_list( \WP_User $user ): bool {
		$settings  = SettingsDTO::instance();
		$role_list = $settings->captcha_role_list;

		// 符合直接回 true
		foreach ($role_list as $role) {
			if (in_array($role, (array) $user->roles, true)) {
				return true;
			}
		}

		return false;
	}
}
