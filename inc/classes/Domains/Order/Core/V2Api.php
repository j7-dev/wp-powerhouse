<?php
/**
 * Order CRUD API
 * 可以用 filter 來 filter 參數
 */

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Order\Core;

use J7\WpUtils\Classes\WP;
use J7\WpUtils\Classes\General;
use J7\WpUtils\Classes\ApiBase;
use J7\Powerhouse\Domains\Order\Utils\CRUD;

/** Class V2Api */
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
			'endpoint'            => 'orders',
			'method'              => 'get',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'orders/(?P<id>\d+)',
			'method'              => 'get',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'orders',
			'method'              => 'post',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'orders/(?P<id>\d+)',
			'method'              => 'post',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'orders',
			'method'              => 'delete',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'orders/(?P<id>\d+)',
			'method'              => 'delete',
			'permission_callback' => null,
		],
	];

	/**
	 * Get posts callback 取得文章列表
	 * 傳入 post_type 可以取得特定文章類型
	 *
	 * @see https://github.com/woocommerce/woocommerce/wiki/wc_get_orders-and-WC_Order_Query
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 * @phpstan-ignore-next-line
	 */
	public function get_orders_callback( $request ) { // phpcs:ignore

		$params = $request->get_query_params();

		$params = WP::sanitize_text_field_deep( $params, false );

		$default_args = [
			'paginate' => true,
			'limit'    => 30,
			'paged'    => 1,
			'type'     => 'shop_order',
			// ------ 其他參數 ------
			// 'type' => 'shop_order_refund', // shop_order | shop_order_refund
			// 'created_via' => 'checkout',
			// 'parent' => 20,
			// 'parent_exclude' => array( 20, 21 ),
			// 'exclude' => array( $order->get_id() ),
			// 'status' => array('wc-processing', 'wc-on-hold'),
			// 'orderby' => 'modified',
			// 'order' => 'DESC',
			// 'return' => 'ids', // object  ids
			// 'currency' => 'USD',
			// 'prices_include_tax' => 'yes',
			// 'payment_method' => 'cheque',
			// 'payment_method_title' => 'Check payments',
			// 'discount_total' => 20.00
			// 'customer' => 'woocommerce@woocommerce.com', // 接受 billing email or customer id.
			// 'customer_id' => 12,
			// 'billing_country' => 'US',
		// 'billing_first_name' => 'Claudio',
		// 'billing_last_name' => 'Sanches',
		// 'date_paid' => '2016-02-12',
		// 'date_created' => '<' . ( time() - HOUR_IN_SECONDS ), // 最近 1小時 內的訂單
		// 'date_completed' => '1494971177...1494938777',
		];

		$args = \wp_parse_args(
			$params,
			$default_args,
		);

		// 將 '[]' 轉為 [], 'true' 轉為 true, 'false' 轉為 false
		$args = General::parse( $args );

		$query       = \wc_get_orders($args);
		$orders      = $query->orders;
		$total       = $query->total;
		$total_pages = $query->max_num_pages;

		$formatted_posts = [];
		foreach ($orders as $order) {
			/** @var \WP_Post $post */
			$formatted_posts[] = CRUD::format_order_details($order );
		}

		$response = new \WP_REST_Response( $formatted_posts );

		// set pagination in header
		$response->header( 'X-WP-Total', (string) $total );
		$response->header( 'X-WP-TotalPages', (string) $total_pages );
		$response->header( 'X-WP-CurrentPage', (string) $args['paged'] );
		$response->header( 'X-WP-PageSize', (string) $args['posts_per_page'] );

		return $response;
	}




	/**TODO
	 * Get posts callback
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 * @throws \Exception 當文章不存在時拋出異常
	 * @phpstan-ignore-next-line
	 */
	public function get_orders_with_id_callback( $request ) { // phpcs:ignore
		$id = $request['id'] ?? null;
		if (!is_numeric($id)) {
			throw new \Exception(
				sprintf(
				__('post id format not match #%s', 'powerhouse'),
				$id
			)
			);
		}

		$post = \get_post( (int) $id );

		if (!$post) {
			throw new \Exception(
				sprintf(
				__('post not found #%s', 'powerhouse'),
				$id
			)
			);
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
			] = CRUD::handle_args($params);

		/** @var \WP_Post $post */
		$post_array = CRUD::format_post_details( $post, $with_description, $depth, $recursive_args, $meta_keys );

		$response = new \WP_REST_Response( $post_array );

		return $response;
	}


	/**TODO
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

		// 將前端傳過來的欄位轉換成 wp_update_post 能吃的參數
		$body_params = CRUD::converter( $body_params );

		$skip_keys = [
			'post_content',
		];
		/** @var array<string, mixed> $body_params 過濾字串，防止 XSS 攻擊 */
		$body_params = WP::sanitize_text_field_deep($body_params, true, $skip_keys);

		// 將 '[]' 轉為 [], 'true' 轉為 true, 'false' 轉為 false
		$body_params = General::parse( $body_params );

		/** @var array<string, mixed> $body_params */
		$body_params = \apply_filters('powerhouse/post/separator_body_params', $body_params, $request);

		$separated_data = WP::separator( $body_params, 'post', $file_params['images'] ?? [] );

		if ('delete' === ( $separated_data['meta_data']['images'] ?? '' )) {
			$separated_data['meta_data']['_thumbnail_id'] = '';
		}
		unset($separated_data['meta_data']['images']);

		return $separated_data;
	}

	/**TODO
	 * Post post callback
	 * 創建文章
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 * @throws \Exception 當新增文章失敗時拋出異常
	 * @phpstan-ignore-next-line
	 */
	public function post_orders_callback( $request ): \WP_REST_Response|\WP_Error {
		[
				'data'      => $data,
				'meta_data' => $meta_data,
			] = $this->separator( $request );

		$qty = (int) ( $meta_data['qty'] ?? 1 );
		unset($meta_data['qty']);

		$data['meta_input'] = $meta_data;

		$success_ids = [];

		for ($i = 0; $i < $qty; $i++) {
			$post_id = CRUD::create_post( $data );
			if (is_numeric($post_id)) {
				$success_ids[] = $post_id;
			} else {
				throw new \Exception(
					sprintf(
					__('create post failed, %s', 'powerhouse'),
					$post_id->get_error_message()
				)
				);
			}
		}

		return new \WP_REST_Response(
				[
					'code'    => 'create_success',
					'message' => __('create post success', 'powerhouse'),
					'data'    => $success_ids,
				],
			);
	}


	/**TODO
	 * Patch post callback
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 * @throws \Exception 當更新文章失敗時拋出異常
	 * @phpstan-ignore-next-line
	 */
	public function post_orders_with_id_callback( $request ): \WP_REST_Response|\WP_Error {
		$id = $request['id'] ?? null;
		if (!is_numeric($id)) {
			throw new \Exception(
				sprintf(
				__('post id format not match #%s', 'powerhouse'),
				$id
			)
			);
		}

		[
			'data'      => $data,
			'meta_data' => $meta_data,
			] = $this->separator( $request );

		$data['meta_input'] = $meta_data;

		$update_result = CRUD::update_post(
				(int) $id,
				$data
			);

		/** @var int|\WP_Error $update_result */
		if ( !is_numeric( $update_result ) ) {
			return $update_result;
		}

		return new \WP_REST_Response(
			[
				'code'    => 'update_success',
				'message' => __('update post success', 'powerhouse'),
				'data'    => [
					'id' => $id,
				],
			]
			);
	}

	/**TODO
	 * 批量刪除文章資料
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 * @throws \Exception 當刪除文章資料失敗時拋出異常
	 * @phpstan-ignore-next-line
	 */
	public function delete_orders_callback( $request ): \WP_REST_Response|\WP_Error {

		$body_params = $request->get_json_params();

		/** @var array<string, mixed> $body_params */
		$body_params = WP::sanitize_text_field_deep( $body_params, false );

		$ids = $body_params['ids'] ?? [];
		/** @var array<string> $ids */
		$ids = is_array( $ids ) ? $ids : [];

		foreach ($ids as $id) {
			$result = \wp_trash_post( (int) $id );
			if (!$result) {
				throw new \Exception(
					sprintf(
					__('delete post data failed #%s', 'powerhouse'),
					$id
				)
				);
			}
		}

		return new \WP_REST_Response(
				[
					'code'    => 'delete_success',
					'message' => __('delete post data success', 'powerhouse'),
					'data'    => $ids,
				]
			);
	}

	/**TODO
	 * Delete post callback
	 * 刪除文章
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 * @throws \Exception 當刪除文章失敗時拋出異常
	 * @phpstan-ignore-next-line
	 */
	public function delete_orders_with_id_callback( $request ): \WP_REST_Response {
		$id = $request['id'] ?? null;
		if (!is_numeric($id)) {
			throw new \Exception(
				sprintf(
				__('post id format not match #%s', 'powerhouse'),
				$id
			)
			);
		}
		$result = \wp_trash_post( (int) $id );
		if (!$result) {
			throw new \Exception(
				sprintf(
				__('delete post failed #%s', 'powerhouse'),
				$id
			)
			);
		}

		return new \WP_REST_Response(
			[
				'code'    => 'delete_success',
				'message' => __('delete post success', 'powerhouse'),
				'data'    => [
					'id' => $id,
				],
			]
			);
	}
}
