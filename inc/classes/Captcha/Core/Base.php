<?php

declare(strict_types=1);

namespace J7\Powerhouse\Captcha\Core;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
/**
 * Class Base
 */
abstract class Base {

	/** @var string 驗證碼欄位名稱 */
	protected $captcha_name = 'powerhouse_captcha';

	/** @var string 驗證碼 session 鍵名 */
	protected $session_key = 'powerhouse_phrase';

	/** @var int 驗證碼長度 */
	protected $length = 4;

	/** @var string 驗證碼字符集不支援中文、特殊字符 */
	protected $charset = '0123456789';

	/** @var string 驗證碼 */
	protected $phrase;

	/** @var CaptchaBuilder 實例 */
	protected $builder;

	/** @var PhraseBuilder 實例 */
	protected $phrase_builder;

	/** @var string 驗證碼 nonce 名稱 */
	protected $nonce_action = 'powerhouse_captcha_nonce';

	/** @var login|register 驗證碼容器 class 用來區分是 login 還是 register */
	protected $container_class = 'login';

	/** Constructor */
	public function __construct() {
		\add_action('init', [ $this, 'session_start' ], 1);

		// ajax
		\add_action('wp_ajax_nopriv_get_captcha', [ $this, 'generate_captcha' ]);
	}

	/** 開啟 session 支持 */
	public function session_start(): void {
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
	}

	/** 渲染驗證碼欄位 */
	public function render_captcha_field(): void {
		$name = $this->captcha_name;
		// render
		printf(
		/*html*/'<div class="captcha_container %1$s" style="display: %2$s;">
		',
		$this->container_class,
		$this->container_class === 'login' ? 'none' : 'block'
		);
		printf(/*html*/'<label for="%s">驗證碼</label>', $name);
		printf(
			/*html*/'
					<div>
							<div style="display: flex; align-items: end; gap: 10px; margin-bottom: 10px;">
								<img class="captcha-img" src="" />
								<span class="refresh-captcha" style="cursor: pointer;">換一組</span>
							</div>
							<input type="text" name="%1$s" class="input" />
					</div>
					',
			$name
			);
		echo '</div>';

		?>
				<script type="module" defer>
					(function($){
						const type = '<?php echo $this->container_class; ?>';
						$(document).ready(function(){
							const $container = $(`.captcha_container.${type}`);
							const $img = $container.find('.captcha-img');
							const $refresh = $container.find('.refresh-captcha');
							const $form = $container.closest('form');

							let shouldBlock = true; // 是否需要阻擋提交

							function getCaptcha(){
								$.ajax({
									url: '<?php echo \admin_url('admin-ajax.php'); ?>',
									type: 'POST',
									data: {
										action: 'get_captcha',
										nonce: '<?php echo \wp_create_nonce($this->nonce_action); ?>',
									},
									success: function(response){
										if(!response?.success){
											alert(`獲取驗證碼生成失敗: ${response?.data}`);
											return;
										}
										$img.attr('src', response?.data);
										$container.show();
										shouldBlock = false;
									},
									error: function(response){
										alert(`獲取驗證碼生成失敗: ${response?.data}`);
										return;
									},
								});
							}

							function needCaptcha(form){
								// wp-admin & woocommerce 的 username 欄位名稱不同
								const $username = $form.find('#user_login, input[name="username"]').first();
								$.ajax({
									url: '<?php echo \admin_url('admin-ajax.php'); ?>',
									type: 'POST',
									data: {
										action: 'need_captcha',
										nonce: '<?php echo \wp_create_nonce($this->nonce_action); ?>',
										username: $username.val(),
									},
									success: function(response){
										// 如果不需要驗證就表單提交
										// 檢查 response.data 是否為 true
										if(response?.data !== true || !response?.success){
											shouldBlock = response?.data !== false
											submitForm(form)
											return;
										}
										// 如果需要就取得授權碼
										getCaptcha()
									},
									error: function(response){
										console.error(response?.data)
										return;
									},
								});
							}

							function blockSubmit(){
								$form.on('submit', function(e){
									if(shouldBlock){
										e.preventDefault();
										e.stopPropagation();
										needCaptcha(this)
										return;
									}
								});
							}

							function submitForm(form){
								const submitButton = $(form).find('[type="submit"]')
								if(submitButton?.length){
									submitButton.click()
								}else{
									form.submit()
								}
							}

							function init(){
								$refresh.on('click', getCaptcha);
								if(type === 'login'){
									blockSubmit();
								}else{
									getCaptcha()
								}
							}

							init();
						});
					})(jQuery);
				</script>
		<?php
	}

	/** AJAX 生成驗證碼 */
	public function generate_captcha(): void {
			if (!\wp_verify_nonce($_POST['nonce'] ?? '', $this->nonce_action)) { // phpcs:ignore
			\wp_send_json_error('驗證 nonce 錯誤');
		}

		$this->init();

		\wp_send_json(
			[
				'success' => true,
				'data' => $_SESSION[ "{$this->session_key}_url" ], // phpcs:ignore
			]
			);
	}

	/**
	 * 測試驗證碼
	 *
	 * @param string $user_input 用戶輸入的驗證碼
	 * @return bool
	 */
	protected function test_phrase( string $user_input ): bool {
		if (!isset($_SESSION[ $this->session_key ])) {
			return false;
		}
		return $user_input == $_SESSION[ $this->session_key ]; // phpcs:ignore
	}

	/** 初始化 */
	private function init(): void {
		$this->phrase_builder = new PhraseBuilder($this->length, $this->charset);
		$this->builder        = new CaptchaBuilder(null, $this->phrase_builder);
		$this->builder->setInterpolation(false); // 更快、圖更醜
		$this->builder->build(); // 生成圖片

		// $builder->save('test/out.jpg');  // 保存圖片，從網站跟目錄開始的 folder，目錄必須存在

		// 存入 session
		$_SESSION[ $this->session_key ]         = $this->builder->getPhrase();
		$_SESSION[ "{$this->session_key}_url" ] = $this->builder->inline();
	}
}
