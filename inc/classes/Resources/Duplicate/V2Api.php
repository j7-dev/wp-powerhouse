<?php
/**
 * Duplicate API
 * 主要功能:
 * 1. 希望可以複製一切，目前只能複製文章、商品
 * 2. 複製文章時，可以複製子文章
 */

declare(strict_types=1);

namespace J7\Powerhouse\Resources\Duplicate;

use J7\WpUtils\Classes\ApiBase;

/**
 * Class V2Api
 */
final class V2Api extends ApiBase {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Namespace
	 *
	 * @var string
	 */
	protected $namespace = 'v2/powerhouse';

	/**
	 * APIs
	 *
	 * @var array{endpoint:string,method:string,permission_callback: ?callable }[]
	 */
	protected $apis = [
		[
			'endpoint'            => 'duplicate/(?P<id>\d+)',
			'method'              => 'post',
			'permission_callback' => null,
		],
	];


	/**
	 * 複製
	 *
	 * @param \WP_REST_Request $request 包含更新選項所需資料的REST請求對象。
	 * @return \WP_REST_Response 返回包含操作結果的REST響應對象。成功時返回選項資料，失敗時返回錯誤訊息。
	 * @phpstan-ignore-next-line
	 */
	public function post_duplicate_with_id_callback( \WP_REST_Request $request ): \WP_REST_Response {
		$id = $request['id'] ?? null;

		if (!$id || !is_numeric( $id ) ) {
			return new \WP_REST_Response(
				[
					'message' => 'id is required',
				],
				400
			);
		}

		$duplicate = Duplicate::instance();
		$new_id    = $duplicate->process( (int) $id, true, true );

		return new \WP_REST_Response(
			[
				'code'    => 'post_duplicate_success',
				'message' => '複製成功',
				'data'    => $new_id,
			],
			200
			);
	}
}
