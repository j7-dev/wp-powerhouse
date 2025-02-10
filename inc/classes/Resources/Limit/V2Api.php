<?php
/**
 * Limit API
 */

declare(strict_types=1);

namespace J7\Powerhouse\Resources\Limit;

use J7\WpUtils\Classes\WP;
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
			'endpoint'            => 'limit/add-users',
			'method'              => 'post',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'limit/update-users',
			'method'              => 'post',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'limit/remove-users',
			'method'              => 'post',
			'permission_callback' => null,
		],
	];

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		Models\LifeCycle::instance();
	}


	/**
	 * 新增用戶到項目上
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response
	 * @throws \Exception 缺少 user_ids, item_ids, expire_date, meta_key
	 * @phpstan-ignore-next-line
	 */
	public function post_limit_add_users_callback( \WP_REST_Request $request ): \WP_REST_Response {

		$body_params = $request->get_body_params();
		try {
			WP::include_required_params( $body_params, [ 'user_ids', 'item_ids', 'expire_date' ] );
			$body_params = WP::sanitize_text_field_deep($body_params, false );

			/** @var array<string, mixed> $body_params */
			$user_ids    = \is_array( $body_params['user_ids'] ) ? $body_params['user_ids'] : [];
			$item_ids    = \is_array( $body_params['item_ids'] ) ? $body_params['item_ids'] : [];
			$expire_date = $body_params['expire_date'] ?? 0;

			if (!$user_ids || !$item_ids) {
				throw new \Exception('新增用戶失敗，缺少 user_ids 或 item_ids');
			}

			foreach ($item_ids as $item_id) {
				foreach ($user_ids as  $user_id) {
					\do_action( Models\LifeCycle::ADD_USER_TO_ITEM_ACTION, (int) $user_id, (int) $item_id, $expire_date, null );
				}
			}

			return new \WP_REST_Response(
			[
				'code'    => 'add_users_success',
				'message' => '新增用戶成功',
				'data'    => [
					'user_ids'    => \implode(',', $user_ids),
					'item_ids'    => \implode(',', $item_ids),
					'expire_date' => $expire_date,
				],
			],
			200
			);
		} catch (\Throwable $th) {
			return new \WP_REST_Response(
				[
					'code'    => 'add_users_failed',
					'message' => $th->getMessage(),
					'data'    => [
						'user_ids'    => \implode(',', $body_params['user_ids']),
						'item_ids'    => \implode(',', $body_params['item_ids']),
						'expire_date' => $body_params['expire_date'],
					],

				],
				400
			);
		}
	}

	/**
	 * 更新用戶觀看時間
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response
	 * @throws \Exception 缺少 user_ids, item_ids, timestamp
	 * @phpstan-ignore-next-line
	 */
	public function post_limit_update_users_callback( \WP_REST_Request $request ): \WP_REST_Response {
		$body_params = $request->get_body_params();
		try {
			WP::include_required_params( $body_params, [ 'user_ids', 'item_ids', 'timestamp' ] );
			$body_params = WP::sanitize_text_field_deep( $body_params, false );

			/** @var array<string, mixed> $body_params */
			$user_ids  = \is_array( $body_params['user_ids'] ) ? $body_params['user_ids'] : [];
			$timestamp = (int) ( $body_params['timestamp'] ?? 0 ); // 一般為 10 位數字，如果是0就是無期限 //TODO 可能會跟隨訂閱!?
			$item_ids  = \is_array( $body_params['item_ids'] ) ? $body_params['item_ids'] : [];

			foreach ($item_ids as $item_id) {
				foreach ($user_ids as  $user_id) {
					\do_action(Models\LifeCycle::AFTER_UPDATE_USER_FROM_ITEM_ACTION, $user_id, $item_id, $timestamp);
				}
			}

			return new \WP_REST_Response(
			[
				'code'    => 'update_users_success',
				'message' => '批量調整觀看期限成功',
				'data'    => [
					'user_ids'  => \implode(',', $user_ids),
					'item_ids'  => \implode(',', $item_ids),
					'timestamp' => $timestamp,
				],
			]
			);
		} catch (\Throwable $th) {
			return new \WP_REST_Response(
				[
					'code'    => 'update_users_failed',
					'message' => $th->getMessage(),
					'data'    => [
						'user_ids'  => \implode(',', $body_params['user_ids']),
						'item_ids'  => \implode(',', $body_params['item_ids']),
						'timestamp' => $body_params['timestamp'],
					],
				],
				400
			);
		}
	}


	/**
	 * 移除用戶
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response
	 * @throws \Exception 缺少 user_ids, item_ids
	 * @phpstan-ignore-next-line
	 */
	public function post_limit_remove_users_callback( \WP_REST_Request $request ): \WP_REST_Response {
		$body_params = $request->get_body_params();
		try {
			WP::include_required_params( $body_params, [ 'user_ids', 'item_ids' ] );
			$body_params = WP::sanitize_text_field_deep( $body_params, false );
			/** @var array<string, mixed> $body_params */
			$user_ids = \is_array( $body_params['user_ids'] ) ? $body_params['user_ids'] : [];
			$item_ids = \is_array( $body_params['item_ids'] ) ? $body_params['item_ids'] : [];

			if (!$user_ids || !$item_ids) {
				throw new \Exception('移除用戶失敗，缺少 user_ids 或 item_ids');
			}

			foreach ($item_ids as $item_id) {
				foreach ($user_ids as $user_id) {
					\do_action(Models\LifeCycle::AFTER_REMOVE_USER_FROM_ITEM_ACTION, $user_id, $item_id);
				}
			}

			return new \WP_REST_Response(
				[
					'code'    => 'remove_users_success',
					'message' => '移除用戶成功',
					'data'    => [
						'user_ids' => \implode(',', $user_ids),
						'item_ids' => \implode(',', $item_ids),
					],
				],
				200
			);
		} catch (\Throwable $th) {
			return new \WP_REST_Response(
				[
					'code'    => 'remove_users_failed',
					'message' => $th->getMessage(),
					'data'    => [
						'user_ids' => \implode(',', $body_params['user_ids']),
						'item_ids' => \implode(',', $body_params['item_ids']),
					],

				],
				400
			);
		}
	}
}
