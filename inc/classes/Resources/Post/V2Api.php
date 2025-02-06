<?php
/**
 * Post CRUD API
 * 可以用 filter 來 filter 參數
 */

declare(strict_types=1);

namespace J7\Powerhouse\Resources\Post;

use J7\WpUtils\Classes\WP;
use J7\WpUtils\Classes\General;
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
			'endpoint'            => 'posts',
			'method'              => 'get',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'posts/(?P<id>\d+)',
			'method'              => 'get',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'posts',
			'method'              => 'post',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'posts/(?P<id>\d+)',
			'method'              => 'post',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'posts',
			'method'              => 'delete',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'posts/(?P<id>\d+)',
			'method'              => 'delete',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'posts/sort',
			'method'              => 'post',
			'permission_callback' => null,
		],
	];

	/**
	 * Get posts callback 取得文章列表
	 * 傳入 post_type 可以取得特定文章類型
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 * @phpstan-ignore-next-line
	 */
	public function get_posts_callback( $request ) { // phpcs:ignore

		$params = $request->get_query_params();

		$params = WP::sanitize_text_field_deep( $params, false );

		$default_args = [
			'post_type'      => 'post',
			'posts_per_page' => - 1,
			'post_parent'    => 0,
			'post_status'    => 'any',
			'orderby'        => [
				'menu_order' => 'ASC',
				'ID'         => 'ASC',
				'date'       => 'ASC',
			],
		];

		$args = \wp_parse_args(
			$params,
			$default_args,
		);

		// 將 '[]' 轉為 [], 'true' 轉為 true, 'false' 轉為 false
		$args = General::parse( $args );

		[
			'args' => $args,
			'meta_keys' => $meta_keys,
			'with_description' => $with_description,
			'depth' => $depth,
			'recursive_args' => $recursive_args,
		] = self::handle_args($args);

		// @phpstan-ignore-next-line
		$posts = \get_posts($args);

		$formatted_posts = [];
		foreach ($posts as $post) {
			/** @var \WP_Post $post */
			$formatted_posts[] = Utils::format_post_details( $post, $with_description, $depth, $recursive_args, $meta_keys );
		}

		$response = new \WP_REST_Response( $formatted_posts );

		return $response;
	}

	/**
	 * 處理參數
	 *
	 * @param array<string, mixed> $args 參數.
	 * @return array{args: array<string, mixed>, meta_keys: array<string>, with_description: bool, depth: int, recursive_args: ?array<string, mixed>}
	 */
	private function handle_args( array $args ): array {
		$default = [
			'meta_keys'        => [],
			'with_description' => false,
			'depth'            => 0,
			'recursive_args'   => null,
		];

		$args = \wp_parse_args( $args, $default );

		[
			'meta_keys'        => $meta_keys,
			'with_description' => $with_description,
			'depth'            => $depth,
			'recursive_args'   => $recursive_args,
		] = $args;

		unset($args['meta_keys']);
		unset($args['with_description']);
		unset($args['depth']);
		unset($args['recursive_args']);

		return [
			'args'             => $args,
			'meta_keys'        => $meta_keys,
			'with_description' => (bool) $with_description,
			'depth'            => $depth,
			'recursive_args'   => $recursive_args,
		];
	}


	/**
	 * Get posts callback
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 * @throws \Exception 當文章不存在時拋出異常
	 * @phpstan-ignore-next-line
	 */
	public function get_posts_with_id_callback( $request ) { // phpcs:ignore

		try {
			$id = $request['id'] ?? null;
			if (!is_numeric($id)) {
				throw new \Exception('id 格式不符合');
			}

			$post = \get_post( (int) $id );

			if (!$post) {
				throw new \Exception("文章不存在 #{$id}");
			}

			/** @var array<string, mixed>|null $params */
			$params = $request->get_query_params();
			$params = is_array($params) ? $params : [];
			/** @var array<string, mixed> $params */
			$params = WP::sanitize_text_field_deep( $params, false );

			// 將 '[]' 轉為 [], 'true' 轉為 true, 'false' 轉為 false
			$params = General::parse( $params );

			[
				'meta_keys' => $meta_keys,
				'with_description' => $with_description,
				'depth' => $depth,
				'recursive_args' => $recursive_args,
			] = self::handle_args($params);

			/** @var \WP_Post $post */
			$post_array = Utils::format_post_details( $post, $with_description, $depth, $recursive_args, $meta_keys );

			$response = new \WP_REST_Response( $post_array );

			return $response;
		} catch (\Throwable $th) {
			return new \WP_REST_Response(
				[
					'code'    => 'get_failed',
					'message' => $th->getMessage(),
					'data'    => null,
				],
				400
			);
		}
	}


	/**
	 * 處理並分離產品資訊
	 *
	 * 根據請求分離產品資訊，並處理描述欄位。
	 *
	 * @param \WP_REST_Request $request 包含產品資訊的請求對象。
	 * @throws \Exception 當找不到商品時拋出異常。.
	 * @return array{data: array<string, mixed>, meta_data: array<string, mixed>} 包含產品對象、資料和元數據的陣列。
	 * @phpstan-ignore-next-line
	 */
	private function separator( $request ): array {
		$body_params = $request->get_body_params();
		$file_params = $request->get_file_params();

		// 將 key 做轉換
		$body_params = Utils::converter( $body_params );

		$skip_keys = [
			'post_content',
		];
		/** @var array<string, mixed> $body_params 過濾字串，防止 XSS 攻擊 */
		$body_params = WP::sanitize_text_field_deep($body_params, true, $skip_keys);

		// 將 '[]' 轉為 [], 'true' 轉為 true, 'false' 轉為 false
		$body_params = General::parse( $body_params );

		$separated_data = WP::separator( $body_params, 'post', $file_params['files'] ?? [] );

		return $separated_data;
	}

	/**
	 * Post post callback
	 * 創建文章
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 * @throws \Exception 當新增文章失敗時拋出異常
	 * @phpstan-ignore-next-line
	 */
	public function post_posts_callback( $request ): \WP_REST_Response|\WP_Error {

		try {
			[
				'data'      => $data,
				'meta_data' => $meta_data,
			] = $this->separator( $request );

			$qty = (int) ( $meta_data['qty'] ?? 1 );
			unset($meta_data['qty']);

			$post_parents = $meta_data['post_parents'] ?? [];
			unset($meta_data['post_parents']);
			$post_parents = is_array( $post_parents ) ? $post_parents : [];

			// 不需要紀錄 depth，深度是由 post_parent 決定的
			unset($meta_data['depth']);
			// action 用來區分是 create 還是 update ，目前只有 create ，所以不用判斷
			unset($meta_data['action']);

			$data['meta_input'] = $meta_data;

			$success_ids = [];

			if (!empty($post_parents)) {
				foreach ($post_parents as $post_parent) {
					$data['post_parent'] = $post_parent;
					for ($i = 0; $i < $qty; $i++) {
						$post_id = Utils::create_post( $data );
						if (is_numeric($post_id)) {
							$success_ids[] = $post_id;
						} else {
							throw new \Exception( "新增文章失敗 : {$post_id->get_error_message()}");
						}
					}
				}
			} else {
				for ($i = 0; $i < $qty; $i++) {
					$post_id = Utils::create_post( $data );
					if (is_numeric($post_id)) {
						$success_ids[] = $post_id;
					} else {
						throw new \Exception( "新增文章失敗 : {$post_id->get_error_message()}");
					}
				}
			}

			return new \WP_REST_Response(
				[
					'code'    => 'create_success',
					'message' => '新增文章成功',
					'data'    => $success_ids,
				],
			);
		} catch (\Throwable $th) {
			return new \WP_REST_Response(
				[
					'code'    => 'create_failed',
					'message' => $th->getMessage(),
					'data'    => null,
				],
				400
			);
		}
	}

	/**
	 * Post post sort callback
	 * 處理排序
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 * @phpstan-ignore-next-line
	 */
	public function post_posts_sort_callback( $request ): \WP_REST_Response|\WP_Error {

		$body_params = $request->get_json_params();

		$body_params = WP::sanitize_text_field_deep( $body_params, false );

		/** @var array{from_tree: array<array{id: string}>, to_tree: array<array{id: string}>} $body_params */
		$sort_result = Utils::sort_posts( $body_params );

		if ( $sort_result !== true ) {
			return $sort_result;
		}

		return new \WP_REST_Response(
			[
				'code'    => 'sort_success',
				'message' => '修改排序成功',
				'data'    => null,
			]
		);
	}

	/**
	 * Patch post callback
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 * @throws \Exception 當更新文章失敗時拋出異常
	 * @phpstan-ignore-next-line
	 */
	public function post_posts_with_id_callback( $request ): \WP_REST_Response|\WP_Error {
		try {
			$id = $request['id'] ?? null;
			if (!$id) {
				throw new \Exception('缺少 id');
			}

			[
			'data'      => $data,
			'meta_data' => $meta_data,
			] = $this->separator( $request );

			$data['ID']         = $id;
			$data['meta_input'] = $meta_data;

			$update_result = \wp_update_post($data);

			if ( !is_numeric( $update_result ) ) {
				return $update_result;
			}

			return new \WP_REST_Response(
			[
				'code'    => 'update_success',
				'message' => '更新成功',
				'data'    => [
					'id' => $id,
				],
			]
			);

		} catch (\Throwable $th) {
			return new \WP_REST_Response(
			[
				'code'    => 'update_failed',
				'message' => $th->getMessage(),
				'data'    => null,
			],
			400
			);
		}
	}

	/**
	 * 批量刪除文章資料
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 * @throws \Exception 當刪除文章資料失敗時拋出異常
	 * @phpstan-ignore-next-line
	 */
	public function delete_posts_callback( $request ): \WP_REST_Response|\WP_Error {

		$body_params = $request->get_json_params();

		/** @var array<string, mixed> $body_params */
		$body_params = WP::sanitize_text_field_deep( $body_params, false );

		$ids = $body_params['ids'] ?? [];
		/** @var array<string> $ids */
		$ids = is_array( $ids ) ? $ids : [];

		try {
			foreach ($ids as $id) {
				$result = \wp_trash_post( (int) $id );
				if (!$result) {
					throw new \Exception(__('刪除文章資料失敗', 'power-course') . " #{$id}");
				}
			}

			return new \WP_REST_Response(
				[
					'code'    => 'delete_success',
					'message' => '刪除成功',
					'data'    => $ids,
				]
			);
		} catch (\Throwable $th) {
			return new \WP_REST_Response(
				[
					'code'    => 'delete_failed',
					'message' => $th->getMessage(),
					'data'    => $ids,
				],
				400
			);
		}
	}

	/**
	 * Delete post callback
	 * 刪除文章
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 * @throws \Exception 當刪除文章失敗時拋出異常
	 * @phpstan-ignore-next-line
	 */
	public function delete_posts_with_id_callback( $request ): \WP_REST_Response {
		try {
			$id = $request['id'] ?? null;
			if (!$id) {
				throw new \Exception('缺少 id');
			}
			$result = \wp_trash_post( (int) $id );
			if (!$result) {
				throw new \Exception('刪除失敗');
			}

			return new \WP_REST_Response(
			[
				'code'    => 'delete_success',
				'message' => '刪除成功',
				'data'    => [
					'id' => $id,
				],
			]
			);
		} catch (\Throwable $th) {
			return new \WP_REST_Response(
				[
					'code'    => 'delete_failed',
					'message' => $th->getMessage(),
					'data'    => [
						'id' => $id,
					],
				],
				400
				);
		}
	}
}
