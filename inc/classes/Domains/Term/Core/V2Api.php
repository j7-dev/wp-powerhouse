<?php
/**
 * Term CRUD API
 * 可以用 filter 來 filter 參數
 */

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Term\Core;

use J7\WpUtils\Classes\WP;
use J7\WpUtils\Classes\General;
use J7\WpUtils\Classes\ApiBase;
use J7\Powerhouse\Domains\Term\Utils\CRUD;

use J7\Powerhouse\Domains\Term\Model\Term;

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
			'endpoint'            => 'terms',
			'method'              => 'get',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'terms/(?P<id>\d+)',
			'method'              => 'get',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'terms',
			'method'              => 'post',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'terms/(?P<id>\d+)',
			'method'              => 'post',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'terms',
			'method'              => 'delete',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'terms/(?P<id>\d+)',
			'method'              => 'delete',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'terms/sort',
			'method'              => 'post',
			'permission_callback' => null,
		],
	];

	/**
	 * Get terms callback 取得 term 列表
	 * 傳入 taxonomy 可以取得特定 taxonomy 的 term 列表
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @see https://developer.wordpress.org/reference/classes/wp_term_query/
	 * @return \WP_REST_Response|\WP_Error
	 * @phpstan-ignore-next-line
	 */
	public function get_terms_callback( $request ) { // phpcs:ignore

		$params = $request->get_query_params();

		$params = WP::sanitize_text_field_deep( $params, false );

		$default_args = [
			'taxonomy'       => 'product_cat',
			'hide_empty'     => false,
			'posts_per_page' => 20,
			'paged'          => 1,
			'orderby'        => 'order',
			'order'          => 'ASC',
		];

		// number offset

		$args = \wp_parse_args(
			$params,
			$default_args,
		);

		// 將 '[]' 轉為 [], 'true' 轉為 true, 'false' 轉為 false
		$args = General::parse( $args );

		// 將 posts_per_page, paged 轉換為 number offset
		$args['number'] = $args['posts_per_page'] <= 0 ? 0 : $args['posts_per_page'];
		$args['offset'] = ( $args['paged'] - 1 ) * $args['number'];
		$current_page   = $args['paged'];
		unset($args['posts_per_page']);
		unset($args['paged']);

		/** @var \WP_Term[] $terms */
		$terms = \get_terms($args);
		/** @var int $total */
		$total = \get_terms(array_merge($args, [ 'fields' => 'count' ]));

		$total_pages = $args['number'] > 0 ? \ceil($total / $args['number']) : 1;

		$formatted_terms = [];
		foreach ($terms as $term) {
			$formatted_terms[] = Term::instance( $term );
		}

		$response = new \WP_REST_Response( $formatted_terms );

		// set pagination in header
		$response->header( 'X-WP-Total', (string) $total );
		$response->header( 'X-WP-TotalPages', (string) $total_pages );
		$response->header( 'X-WP-CurrentPage', (string) $current_page );
		$response->header( 'X-WP-PageSize', (string) $args['number'] );

		return $response;
	}

	/**
	 * Get term callback
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 * @throws \Exception 當 term 不存在時拋出異常
	 * @phpstan-ignore-next-line
	 */
	public function get_terms_with_id_callback( $request ) { // phpcs:ignore
		$id = $request['id'] ?? null;
		if (!is_numeric($id)) {
			throw new \Exception(
				sprintf(
				__('term id format not match #%s', 'powerhouse'),
				$id
			)
			);
		}
		$params   = $request->get_query_params();
		$params   = WP::sanitize_text_field_deep( $params, false );
		$taxonomy = $params['taxonomy'] ?? '';

		$term = \get_term( (int) $id, $taxonomy );

		if (!$term) {
			throw new \Exception(
				sprintf(
				__('term not found #%s', 'powerhouse'),
				$id
			)
			);
		}

		if (\is_wp_error($term)) {
			throw new \Exception($term->get_error_message());
		}

		$term_array = Term::instance( $term );

		$response = new \WP_REST_Response( $term_array );

		return $response;
	}

	/**
	 * Post term callback
	 * 批量創建 term
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 * @throws \Exception 當新增 term 失敗時拋出異常
	 * @phpstan-ignore-next-line
	 */
	public function post_terms_callback( $request ): \WP_REST_Response|\WP_Error {
		$body_params = $request->get_body_params();
		$body_params = WP::sanitize_text_field_deep( $body_params, false );

		$qty = (int) ( $body_params['qty'] ?? 1 );
		unset($body_params['qty']);

		$success_ids = [];

		for ($i = 0; $i < $qty; $i++) {
			$post_id = CRUD::create_term( $body_params );
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

	/**
	 * Post post sort callback
	 * 處理排序
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 * @phpstan-ignore-next-line
	 */
	public function post_terms_sort_callback( $request ): \WP_REST_Response|\WP_Error {

		$body_params = $request->get_json_params();

		$body_params = WP::sanitize_text_field_deep( $body_params, false );

		/** @var array{from_tree: array<array{id: string}>, to_tree: array<array{id: string}>} $body_params */
		$sort_result = CRUD::sort_terms( $body_params );

		if ( $sort_result !== true ) {
			return $sort_result;
		}

		return new \WP_REST_Response(
			[
				'code'    => 'sort_success',
				'message' => __('update term sort success', 'powerhouse'),
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
	public function post_terms_with_id_callback( $request ): \WP_REST_Response|\WP_Error {
		$id = $request['id'] ?? null;
		if (!is_numeric($id)) {
			throw new \Exception(
				sprintf(
				__('term id format not match #%s', 'powerhouse'),
				$id
			)
			);
		}

		$body_params = $request->get_body_params();
		$body_params = WP::sanitize_text_field_deep( $body_params, false );

		$update_result = CRUD::update_term(
				(int) $id,
				$body_params
			);

		/** @var int|\WP_Error $update_result */
		if ( !is_numeric( $update_result ) ) {
			return $update_result;
		}

		return new \WP_REST_Response(
			[
				'code'    => 'update_success',
				'message' => __('update term success', 'powerhouse'),
				'data'    => [
					'id' => $id,
				],
			]
			);
	}

	/**
	 * 批量刪除文章資料
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 * @throws \Exception 當刪除文章資料失敗時拋出異常
	 * @phpstan-ignore-next-line
	 */
	public function delete_terms_callback( $request ): \WP_REST_Response|\WP_Error {

		$body_params = $request->get_json_params();

		/** @var array<string, mixed> $body_params */
		$body_params = WP::sanitize_text_field_deep( $body_params, false );

		$ids = $body_params['ids'] ?? [];
		/** @var array<string> $ids */
		$ids      = is_array( $ids ) ? $ids : [];
		$taxonomy = $body_params['taxonomy'] ?? '';

		foreach ($ids as $id) {
			$result = \wp_delete_term( (int) $id, $taxonomy );
			if ($result !== true) {
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

	/**
	 * Delete post callback
	 * 刪除文章
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 * @throws \Exception 當刪除文章失敗時拋出異常
	 * @phpstan-ignore-next-line
	 */
	public function delete_terms_with_id_callback( $request ): \WP_REST_Response {
		$id = $request['id'] ?? null;
		if (!is_numeric($id)) {
			throw new \Exception(
				sprintf(
				__('post id format not match #%s', 'powerhouse'),
				$id
			)
			);
		}

		$body_params = $request->get_body_params();
		$body_params = WP::sanitize_text_field_deep( $body_params, false );
		$taxonomy    = $body_params['taxonomy'] ?? '';

		$result = \wp_delete_term( (int) $id, $taxonomy );
		if ($result !== true) {
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
