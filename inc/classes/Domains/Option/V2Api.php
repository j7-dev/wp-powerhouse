<?php
/**
 * Option API
 */

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Option;

use J7\Powerhouse\Settings\DTO;
use J7\WpUtils\Classes\WP;
use J7\WpUtils\Classes\ApiBase;

/**
 * Class V2Api
 */
final class V2Api extends ApiBase {
	use \J7\WpUtils\Traits\SingletonTrait;

	/** @var string Namespace */
	protected $namespace = 'v2/powerhouse';

	/** @var array{endpoint:string,method:string,permission_callback: ?callable }[] */
	protected $apis = [
		[
			'endpoint'            => 'options',
			'method'              => 'get',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'options',
			'method'              => 'post',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'duplicate/(?P<id>\d+)',
			'method'              => 'post',
			'permission_callback' => null,
		],
	];

	/** @var array<string, mixed> $fields 允許獲取 & 預設值，預設與 DTO 同步 */
	private $fields = [];

	/**
	 * 不需要 sanitize 的 key
	 *
	 * @var array<string>
	 */
	private $skip_sanitize_keys = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		$this->fields             = [
			'bunny_library_id'     => '',
			'bunny_cdn_hostname'   => '',
			'bunny_stream_api_key' => '',
			DTO::SETTINGS_KEY      => [],
		];
		$this->fields             = \apply_filters( 'powerhouse/option/allowed_fields', $this->fields ); // @phpstan-ignore-line
		$this->skip_sanitize_keys = \apply_filters( 'powerhouse/option/skip_sanitize_keys', $this->skip_sanitize_keys ); // @phpstan-ignore-line
	}

	/**
	 * 獲取選項
	 *
	 * @param \WP_REST_Request $request REST請求對象。
	 * @return \WP_REST_Response 返回包含選項資料的REST響應對象。
	 * @phpstan-ignore-next-line
	 */
	public function get_options_callback( \WP_REST_Request $request ): \WP_REST_Response {
		$options = [];
		$fields  = $this->fields;

		foreach ( $fields as $option_name => $default ) {
			$options[ $option_name ] = \get_option( $option_name, $default );
		}

		return new \WP_REST_Response(
			[
				'code'    => 'get_options_success',
				'message' => '獲取選項成功',
				'data'    => $options,
			],
			200
		);
	}

	/**
	 * 更新選項
	 *
	 * @param \WP_REST_Request $request 包含更新選項所需資料的REST請求對象。
	 * @return \WP_REST_Response 返回包含操作結果的REST響應對象。成功時返回選項資料，失敗時返回錯誤訊息。
	 * @phpstan-ignore-next-line
	 */
	public function post_options_callback( \WP_REST_Request $request ): \WP_REST_Response {
		$body_params = $request->get_json_params();

		/** @var array<string, mixed> $body_params */
		$body_params = WP::sanitize_text_field_deep( $body_params, false, $this->skip_sanitize_keys );

		$allowed_fields = array_keys( $this->fields );

		foreach ( $body_params as $key => $value ) {
			if ( in_array( $key, $allowed_fields, true ) ) {
				\update_option( $key, $value );
			}
		}

		return new \WP_REST_Response(
			[
				'code'    => 'post_user_success',
				'message' => '修改成功',
				'data'    => $body_params,
			],
			200
			);
	}
}
