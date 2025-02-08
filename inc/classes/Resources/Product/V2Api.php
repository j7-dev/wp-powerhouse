<?php
/**
 * Product CRUD API
 * 可以用 filter 來 filter 參數
 */

declare(strict_types=1);

namespace J7\Powerhouse\Resources\Product;

use J7\WpUtils\Classes\WP;
use J7\WpUtils\Classes\WC;
use J7\WpUtils\Classes\General;
use J7\WpUtils\Classes\ApiBase;
use J7\Powerhouse\Resources\Post\Utils as PostUtils;
use J7\Powerhouse\Domains\Limit\Limit;
use J7\WpUtils\Classes\WC\Product as WcProduct;

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
			'endpoint'            => 'products',
			'method'              => 'get',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'products/(?P<id>\d+)',
			'method'              => 'get',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'products',
			'method'              => 'post',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'products/(?P<id>\d+)',
			'method'              => 'post',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'products',
			'method'              => 'delete',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'products/(?P<id>\d+)',
			'method'              => 'delete',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'products/options',
			'method'              => 'get',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'products/bind-items', // 綁定觀看權限項目到商品上
			'method'              => 'post',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'products/unbind-items', // 解除綁定觀看權限項目到商品上
			'method'              => 'post',
			'permission_callback' => null,
		],
		[
			'endpoint'            => 'products/update-bound-items', // 更新綁定觀看權限項目到商品上
			'method'              => 'post',
			'permission_callback' => null,
		],
		// TODO 商品排序

		// [
		// 'endpoint'            => 'products/sort',
		// 'method'              => 'post',
		// 'permission_callback' => null,
		// ],
	];

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		// 擴展 wc_get_products 的 meta_query
		\add_filter('woocommerce_product_data_store_cpt_get_products_query', [ Utils::class, 'extend_meta_query' ], 10, 2,);
	}

	/**
	 * Get products callback 取得商品列表
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 * @phpstan-ignore-next-line
	 */
	public function get_products_callback( $request ) { // phpcs:ignore

		$params = $request->get_query_params();

		$params = WP::sanitize_text_field_deep( $params, false );

		$default_args = [
			'status'         => [ 'publish', 'draft' ],
			'paginate'       => true,
			'posts_per_page' => 20,
			'paged'          => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
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
		] = PostUtils::handle_args($args);

		$args['fields'] = 'ids';  // 確保只返回 id

		/** @var object{total:int, max_num_pages:int, products:array<int, \WC_Product>} $results */
		$results     = \wc_get_products( $args );
		$total       = $results->total;
		$total_pages = $results->max_num_pages;

		$products = $results->products;

		$formatted_products = [];
		foreach ($products as $product) {
			$formatted_products[] = Utils::format_product_details( $product, $with_description, $meta_keys );
		}
		$formatted_products = array_filter( $formatted_products );

		$response = new \WP_REST_Response( $formatted_products );

		// set pagination in header
		$response->header( 'X-WP-Total', (string) $total );
		$response->header( 'X-WP-TotalPages', (string) $total_pages );
		$response->header( 'X-WP-CurrentPage', (string) $args['paged'] );
		$response->header( 'X-WP-PageSize', (string) $args['posts_per_page'] );

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
	public function get_products_with_id_callback( $request ) { // phpcs:ignore

		try {
			$id = $request['id'] ?? null;
			if (!is_numeric($id)) {
				throw new \Exception('id 格式不符合');
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
			] = PostUtils::handle_args($params);

			$product_array = Utils::format_product_details( (int) $id, $with_description, $meta_keys );

			$response = new \WP_REST_Response( $product_array );

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
	 * @param bool             $require_id 是否需要 id
	 * @throws \Exception 當找不到商品時拋出異常。.
	 * @return array{product: \WC_Product|null, data: array<string, mixed>, meta_data: array<string, mixed>} 包含產品對象、資料和元數據的陣列。
	 * @phpstan-ignore-next-line
	 */
	private function separator( $request, $require_id = true ): array {
		$product = null; // 初始值，下面會判斷是否需要 id 塞入 product
		if ($require_id) {
			$id = $request['id'] ?? null;
			if (!is_numeric($id)) {
				throw new \Exception(
				sprintf(
				__('product id format not match #%s', 'powerhouse'),
				$id
				)
				);
			}

			$product = \wc_get_product( (int) $id );
			if (!$product) {
				throw new \Exception(
				sprintf(
				__('product not found #%s', 'powerhouse'),
				$id
				)
				);
			}
		}

		$body_params = $request->get_body_params();
		$file_params = $request->get_file_params();

		// 將前端傳過來的欄位轉換成 wp_update_post 能吃的參數
		// $body_params = Utils::converter( $body_params );

		$skip_keys = [
			'description',
			'slug',
		];
		/** @var array<string, mixed> $body_params 過濾字串，防止 XSS 攻擊 */
		$body_params = WP::sanitize_text_field_deep($body_params, true, $skip_keys);

		// 將 '[]' 轉為 [], 'true' 轉為 true, 'false' 轉為 false
		$body_params = General::parse( $body_params );

		$separated_data = WP::separator( $body_params, 'product', $file_params['files'] ?? [] );

		$separated_data['product'] = $product;

		return $separated_data;
	}

	/**
	 * Post post callback
	 * 創建商品
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 * @throws \Exception 當新增商品失敗時拋出異常
	 * @phpstan-ignore-next-line
	 */
	public function post_products_callback( $request ): \WP_REST_Response|\WP_Error {

		try {
			[
				'data'      => $data,
				'meta_data' => $meta_data,
			] = $this->separator( $request, false );

			$qty = (int) ( $meta_data['qty'] ?? 1 );
			unset($meta_data['qty']);

			$success_ids = [];

			for ($i = 0; $i < $qty; $i++) {
				$product_id    = Utils::create_product( $data, $meta_data );
				$success_ids[] = $product_id;
			}

			return new \WP_REST_Response(
				[
					'code'    => 'create_success',
					'message' => __('create products success', 'powerhouse'),
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
	 * Patch post callback
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 * @throws \Exception 當更新文章失敗時拋出異常
	 * @phpstan-ignore-next-line
	 */
	public function post_products_with_id_callback( $request ): \WP_REST_Response|\WP_Error {
		try {

			[
				'product' => $product,
				'data'      => $data,
				'meta_data' => $meta_data,
			] = $this->separator( $request );

			/** @var \WC_Product $product */
			Utils::update_product( $product, $data, $meta_data );

			return new \WP_REST_Response(
			[
				'code'    => 'update_success',
				'message' => __('update product success', 'powerhouse'),
				'data'    => [
					'id' => $product->get_id(),
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
	public function delete_products_callback( $request ): \WP_REST_Response|\WP_Error {

		$body_params = $request->get_json_params();

		/** @var array<string, mixed> $body_params */
		$body_params = WP::sanitize_text_field_deep( $body_params, false );

		$ids = $body_params['ids'] ?? [];
		/** @var array<string> $ids */
		$ids          = is_array( $ids ) ? $ids : [];
		$force_delete = $body_params['force_delete'] ?? false;

		try {
			foreach ($ids as $id) {
				$product = \wc_get_product( (int) $id );

				if (!$product) {
					throw new \Exception(
						sprintf(
						__('product not found #%s', 'powerhouse'),
						$id
					)
					);
				}

				$result = $product->delete( (bool) $force_delete );
				if (!$result) {
					throw new \Exception(
						sprintf(
						__('delete product failed #%s', 'powerhouse'),
						$id
					)
					);
				}
			}

			return new \WP_REST_Response(
				[
					'code'    => 'delete_success',
					'message' => __('delete product success', 'powerhouse'),
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
	public function delete_products_with_id_callback( $request ): \WP_REST_Response {
		try {
			$id = $request['id'] ?? null;
			if (!is_numeric($id)) {
				throw new \Exception(
					sprintf(
					__('product id format not match #%s', 'powerhouse'),
					$id
				)
				);
			}

			$product = \wc_get_product( (int) $id );

			if (!$product) {
				throw new \Exception(
					sprintf(
					__('product not found #%s', 'powerhouse'),
					$id
				)
				);
			}

			$body_params  = $request->get_json_params();
			$force_delete = $body_params['force_delete'] ?? false;
			$result       = $product->delete( (bool) $force_delete );
			if (!$result) {
				throw new \Exception(
					sprintf(
					__('delete product failed #%s', 'powerhouse'),
					$id
				)
				);
			}

			return new \WP_REST_Response(
			[
				'code'    => 'delete_success',
				'message' => __('delete product success', 'powerhouse'),
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


	/**
	 * Get options callback
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return array{
	 *  product_cats: array{id: string, name: string, slug: string}[],
	 *  product_tags: array{id: string, name: string, slug: string}[],
	 *  top_sales_products: array{id: string, name: string, slug: string}[],
	 *  max_price: float,
	 *  min_price: float,
	 * ...
	 * }
	 * @phpstan-ignore-next-line
	 */
	public function get_products_options_callback( $request ) { // phpcs:ignore
		$formatted_cats = PostUtils::format_terms(
			[
				'taxonomy' => 'product_cat',
			]
			);
		$formatted_tags = PostUtils::format_terms(
			[
				'taxonomy' => 'product_tag',
			]
			);

		$top_sales_products = WC::get_top_sales_products( 5 );

		[
			'max_price' => $max_price,
			'min_price' => $min_price,
		] = Utils::get_max_min_prices();

		// @phpstan-ignore-next-line
		return \apply_filters(
			'powerhouse/product/get_options',
			[
				'product_cats'       => $formatted_cats,
				'product_tags'       => $formatted_tags,
				'top_sales_products' => $top_sales_products,
				'max_price'          => $max_price,
				'min_price'          => $min_price,
			],
			$request
			);
	}




	/**
	 * 綁定課程到商品上
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function post_products_bind_items_callback( $request ) {
		try {
			$body_params = $request->get_body_params() ?? [];
			WP::include_required_params( $body_params, [ 'product_ids', 'item_ids', 'limit_type' ] );

			$body_params = WP::sanitize_text_field_deep( $body_params );

			$product_ids = $body_params['product_ids'];
			$item_ids    = $body_params['item_ids'];
			$limit       = new Limit( $body_params['limit_type'], (int) $body_params['limit_value'], $body_params['limit_unit'] );

			$success_ids = [];
			$failed_ids  = [];
			foreach ($product_ids as $product_id) {
				$result = WcProduct::add_meta_array( (int) $product_id, 'bind_course_ids', $course_ids );
				if (\is_wp_error($result)) {
					$failed_ids[] = $product_id;
					continue;
				}

				$bind_items_data_instance = BindCoursesData::instance( (int) $product_id );

				foreach ($course_ids as $course_id) {
					$bind_items_data_instance->add_course_data(
					(int) $course_id,
					$limit
					);
				}

				$bind_items_data_instance->save();

				$success_ids[] = $product_id;
			}

			return new \WP_REST_Response(
			[
				'code'    => 'success',
				'message' => '綁定成功',
				'data'    => [
					'success_ids' => $success_ids,
					'failed_ids'  => $failed_ids,
					'course_ids'  => $course_ids,
				],
			],
				200
			);
		} catch (\Throwable $th) {
			return new \WP_REST_Response(
				[
					'code'    => 'error',
					'message' => $th->getMessage(),
				],
				400
				);

		}
	}


	/**
	 * 更新已綁定課程觀看權限到商品上
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function post_products_update_bound_items_callback( $request ) {
		$body_params = $request->get_body_params() ?? [];

		$include_required_params = WP::include_required_params( $body_params, [ 'product_ids', 'course_ids', 'limit_type' ] );

		if ($include_required_params !== true) {
			return $include_required_params;
		}

		$body_params = WP::sanitize_text_field_deep( $body_params );

		$product_ids = $body_params['product_ids'];
		$course_ids  = $body_params['course_ids'];

		$success_ids = [];
		$failed_ids  = [];
		foreach ($product_ids as $product_id) {

			$bind_items_data_instance = BindCoursesData::instance( (int) $product_id);
			foreach ($course_ids as $course_id) {
				$limit = new Limit( $body_params['limit_type'], (int) $body_params['limit_value'], $body_params['limit_unit'] );
				$bind_items_data_instance->update_course_data( (int) $course_id, $limit );
			}
			$bind_items_data_instance->save();
			$success_ids[] = $product_id;
		}

		return new \WP_REST_Response(
			[
				'code'    => 'success',
				'message' => '修改成功',
				'data'    => [
					'success_ids' => $success_ids,
					'failed_ids'  => $failed_ids,
					'course_ids'  => $course_ids,
				],
			],
			200
		);
	}


	/**
	 * 解除綁定課程到商品上
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function post_products_unbind_items_callback( $request ) {
		$body_params = $request->get_body_params() ?? [];

		$include_required_params = WP::include_required_params( $body_params, [ 'product_ids', 'course_ids' ] );

		if ($include_required_params !== true) {
			return $include_required_params;
		}

		$body_params = WP::sanitize_text_field_deep( $body_params );

		$product_ids = $body_params['product_ids'];
		$course_ids  = $body_params['course_ids'];

		$success_ids = [];
		$failed_ids  = [];
		foreach ($product_ids as $product_id) {
			$original_course_ids = \get_post_meta( $product_id, 'bind_course_ids' ) ?: [];
			$new_course_ids      = \array_filter( $original_course_ids, fn( $original_course_id ) => ! \in_array( $original_course_id, $course_ids ) );

			$result = WcProduct::update_meta_array( (int) $product_id, 'bind_course_ids', $new_course_ids );
			if (\is_wp_error($result)) {
				$failed_ids[] = $product_id;
				continue;
			}

			$bind_items_data_instance = BindCoursesData::instance( (int) $product_id );
			foreach ($course_ids as $course_id) {
				$bind_items_data_instance->remove_course_data( (int) $course_id );
			}
			$bind_items_data_instance->save();
			$success_ids[] = $product_id;
		}

		return new \WP_REST_Response(
			[
				'code'    => 'success',
				'message' => '解除綁定成功',
				'data'    => [
					'success_ids' => $success_ids,
					'failed_ids'  => $failed_ids,
					'course_ids'  => $course_ids,
				],
			],
			200
		);
	}
}
