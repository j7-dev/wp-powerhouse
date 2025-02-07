<?php
/**
 * User CRUD API
 * TODO可以用 filter 來 filter 參數
 */

declare(strict_types=1);

namespace J7\Powerhouse\Resources\User;

use J7\WpUtils\Classes\WP;
use J7\WpUtils\Classes\General;
use J7\WpUtils\Classes\ApiBase;

/**
 * Class V2Api
 */
final class V2Api extends ApiBase {
	use \J7\WpUtils\Traits\SingletonTrait;

	// 批量上傳一次處理的數量
	const BATCH_SIZE = 100;

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
			'endpoint'            => 'users',
			'method'              => 'get',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'users/(?P<id>\d+)',
			'method'              => 'get',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'users',
			'method'              => 'post',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'users/(?P<id>\d+)',
			'method'              => 'post',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'users',
			'method'              => 'delete',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'users/(?P<id>\d+)',
			'method'              => 'delete',
			'permission_callback' => null,
		],
	];


	/**
	 * Get users callback
	 * 通用的用戶查詢
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response
	 * @phpstan-ignore-next-line
	 */
	public function get_users_callback( $request ): \WP_REST_Response {
		$params = $request->get_query_params();
		$params = WP::sanitize_text_field_deep( $params, false );

		$posts_per_page = intval($params['posts_per_page'] ?? 20); // @phpstan-ignore-line
		$paged          = intval($params['paged'] ?? 1); // @phpstan-ignore-line
		$offset         = ( $paged - 1 ) * $posts_per_page;
		$search         = (string) ( $params['s'] ?? $params['search'] ?? '' ); // @phpstan-ignore-line
		$orderby        = (string) ( $params['orderby'] ?? 'ID' ); // @phpstan-ignore-line
		$order          = (string) ( $params['order'] ?? 'DESC' ); // @phpstan-ignore-line

		global $wpdb;

		// 基礎 SQL
		$select_sql = 'SELECT DISTINCT u.ID';
		$count_sql  = 'SELECT COUNT(DISTINCT u.ID)';
		$from_sql   = " FROM {$wpdb->users} u";
		$where_sql  = ' WHERE 1=1';

		// 搜尋條件，'ID', 'user_login', 'user_email', 'user_nicename', 'display_name'
		if ($search) {
			$search     = '%' . $wpdb->esc_like($search) . '%';
			$where_sql .= $wpdb->prepare(
				' AND (u.ID LIKE %s OR u.user_login LIKE %s OR u.user_email LIKE %s OR u.user_nicename LIKE %s OR u.display_name LIKE %s)',
				$search,
				$search,
				$search,
				$search,
				$search
			);
		}

		// Meta 查詢
		// TODO 可以再優化更複雜的查詢
		if (isset($params['meta_key'])) {
			$from_sql  .= " LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id";
			$where_sql .= $wpdb->prepare(
				' AND um.meta_key = %s',
				$params['meta_key']
			);

			if (isset($params['meta_value'])) {
				$where_sql .= $wpdb->prepare(
					' AND um.meta_value = %s',
					$params['meta_value']
				);
			}
		}

		// 排序
		$order_sql = " ORDER BY u.{$orderby} {$order}";

		// 分頁
		$limit_sql = $wpdb->prepare(' LIMIT %d OFFSET %d', $posts_per_page, $offset);

		// 執行查詢
		$total    = $wpdb->get_var($count_sql . $from_sql . $where_sql); // phpcs:ignore
		$user_ids = $wpdb->get_col($select_sql . $from_sql . $where_sql . $order_sql . $limit_sql); // phpcs:ignore

		$total_pages = ceil($total / $posts_per_page);

		$formatted_users = [];
		foreach ($user_ids as $user_id) {
			$formatted_users[] = Utils::format_user_details( $user_id );
		}
		$formatted_users = array_filter( $formatted_users );

		$response = new \WP_REST_Response( $formatted_users );

		// set pagination in header
		$response->header( 'X-WP-Total', (string) $total );
		$response->header( 'X-WP-TotalPages', (string) $total_pages );
		$response->header( 'X-WP-CurrentPage', (string) $paged );
		$response->header( 'X-WP-PageSize', (string) $posts_per_page );

		return $response;
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
	public function get_users_with_id_callback( $request ) { // phpcs:ignore
		try {
			$id = $request['id'] ?? null;
			if (!is_numeric($id)) {
				throw new \Exception(
					sprintf(
					__('user id format not match #%s', 'powerhouse'),
					$id
				)
					);
			}

			$user_array = Utils::format_user_details( (int) $id );

			$response = new \WP_REST_Response( $user_array );

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
	 * 批量創建/更新用戶
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 * @throws \Exception 當新增用戶失敗時拋出異常
	 * @phpstan-ignore-next-line
	 */
	public function post_users_callback( $request ): \WP_REST_Response|\WP_Error {

		try {
			$body_params = $request->get_body_params();
			$body_params = WP::sanitize_text_field_deep( $body_params, false );

			/**
			 * 有 ids 就是批量更新
			 * 有 qty 就是批量新增
			 */

			/* ---------- 批量更新 ---------- */
			if (isset($body_params['ids'])) { // 批量更新
				$ids = $body_params['ids'];
				$ids = is_array( $ids ) ? $ids : [];
				unset($body_params['ids']);
				$success_ids = [];
				foreach ($ids as $id) {
					$args       = $body_params;
					$args['ID'] = $id;
					$user_id    = Utils::update_user( $args );
					if (is_numeric($user_id)) {
						$success_ids[] = $user_id;
					} else {
						throw new \Exception(
							sprintf(
							__('update user failed #%1$s, %2$s', 'powerhouse'),
							$id,
							$user_id->get_error_message()
						)
						);
					}
				}

				return new \WP_REST_Response(
					[
						'code'    => 'update_success',
						'message' => __('update user success', 'powerhouse'),
						'data'    => $success_ids,
					],
				);
			}

			/* ---------- 批量新增 ---------- */
			$qty = (int) ( $body_params['qty'] ?? 1 );
			unset($body_params['qty']);

			$success_ids = [];

			for ($i = 0; $i < $qty; $i++) {
				$user_id = Utils::create_user( $body_params );
				if (is_numeric($user_id)) {
					$success_ids[] = $user_id;
				} else {
					throw new \Exception(
						sprintf(
						__('create user failed, %s', 'powerhouse'),
						$user_id->get_error_message()
					)
					);
				}
			}

			return new \WP_REST_Response(
					[
						'code'    => 'create_success',
						'message' => __('create user success', 'powerhouse'),
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
	 * 更新用戶
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 * @throws \Exception 當更新文章失敗時拋出異常
	 * @phpstan-ignore-next-line
	 */
	public function post_users_with_id_callback( $request ): \WP_REST_Response|\WP_Error {
		try {
			$id = $request['id'] ?? null;
			if (!is_numeric($id)) {
				throw new \Exception(
					sprintf(
					__('user id format not match #%s', 'powerhouse'),
					$id
				)
				);
			}

			$body_params = $request->get_body_params();
			$body_params = WP::sanitize_text_field_deep( $body_params, false );
			/** @var array<string, mixed> $body_params */

			$body_params['ID'] = $id;

			$update_result = Utils::update_user( $body_params );

			if ( !is_numeric( $update_result ) ) {
				return $update_result;
			}

			return new \WP_REST_Response(
			[
				'code'    => 'update_success',
				'message' => __('update user success', 'powerhouse'),
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
	 * 批量刪除用戶
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 * @throws \Exception 當刪除用戶失敗時拋出異常
	 * @phpstan-ignore-next-line
	 */
	public function delete_users_callback( $request ): \WP_REST_Response|\WP_Error {

		$body_params = $request->get_json_params();

		/** @var array<string, mixed> $body_params */
		$body_params = WP::sanitize_text_field_deep( $body_params, false );

		$ids = $body_params['ids'] ?? [];
		/** @var array<string> $ids */
		$ids = is_array( $ids ) ? $ids : [];

		try {
			foreach ($ids as $id) {
				$result = \wp_delete_user( (int) $id );
				if (!$result) {
					throw new \Exception(
						sprintf(
						__('delete user failed #%s', 'powerhouse'),
						$id
					)
					);
				}
			}

			return new \WP_REST_Response(
				[
					'code'    => 'delete_success',
					'message' => __('delete user success', 'powerhouse'),
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
	 * 刪除用戶
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 * @throws \Exception 當刪除用戶失敗時拋出異常
	 * @phpstan-ignore-next-line
	 */
	public function delete_users_with_id_callback( $request ): \WP_REST_Response {
		try {
			$id = $request['id'] ?? null;
			if (!is_numeric($id)) {
				throw new \Exception(
					sprintf(
					__('user id format not match #%s', 'powerhouse'),
					$id
				)
				);
			}
			$result = \wp_delete_user( (int) $id );
			if (!$result) {
				throw new \Exception(
					sprintf(
					__('delete user failed #%s', 'powerhouse'),
					$id
				)
					);
			}

			return new \WP_REST_Response(
			[
				'code'    => 'delete_success',
				'message' => __('delete user success', 'powerhouse'),
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
