<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Upload\Core;

use J7\WpUtils\Classes\ApiBase;

/**
 * Class V2Api
 * TODO:
 * 限制大小 / MIME
 */
final class V2Api extends ApiBase {
	use \J7\WpUtils\Traits\SingletonTrait;

	/** @var string Namespace*/
	protected $namespace = 'v2/powerhouse';

	/**
	 * APIs
	 *
	 * @var array{endpoint:string,method:string,permission_callback: ?callable }[]
	 */
	protected $apis = [
		[
			'endpoint'            => 'upload',
			'method'              => 'post',
			'permission_callback' => null,
		],
	];

	/**
	 * 允許的 MIME 類型
	 * [] = 不限制
	 *
	 * @var array<string>
	 */
	private $allowed_mime_types = [];

	/** Constructor */
	public function __construct() {
		parent::__construct();
		$this->allowed_mime_types = \apply_filters( 'powerhouse/upload/allowed_mime_types', $this->allowed_mime_types ); // @phpstan-ignore-line
	}

	/**
	 * Post upload callback
	 * 上傳檔案
	 * post 走 form-data
	 *  - files: binary[]
	 * -  upload_only: '0' or '1 // 是否只上傳，不新增到媒體庫
	 *
	 * @param  \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 * @throws \Exception 不允許的 MIME 類型 | 找不到檔案
	 * @phpstan-ignore-next-line
	 */
	public function post_upload_callback( $request ): \WP_REST_Response {
		// get data from form-data
		$file_params = $request->get_file_params();
		$body_params = $request->get_body_params();
		$upload_only = $body_params['upload_only'] ?? '0';
		if ( (bool) $file_params['files']['tmp_name'] ) {

			if ( ! function_exists( 'media_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/image.php';
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/media.php';
			}

			if (\is_array($file_params['files']['tmp_name'])) {
				return $this->handle_multiple_upload( $file_params['files'], $upload_only);
			}
			return $this->handle_single_upload( $file_params['files'], $upload_only);

		}

		throw new \Exception(__('upload file not found', 'powerhouse'));
	}

	/**
	 * Handle Single upload
	 *
	 * @param array{name:string,type:string,tmp_name:string,error:int,size:int} $file 檔案資訊
	 * @param string|null                                                       $upload_only 是否只上傳，不新增到媒體庫 '0' or '1'
	 *
	 * @return \WP_REST_Response
	 * @throws \Exception 不允許的 MIME 類型
	 */
	private function handle_single_upload( array $file, ?string $upload_only = '0' ): \WP_REST_Response {

		$_FILES['0'] = $file;

		if ( (bool) $this->allowed_mime_types && !in_array($file['type'], $this->allowed_mime_types)) {
			throw new \Exception(
				sprintf(
				__('not allowed mime type, only allow: %s', 'powerhouse'),
				implode(', ', $this->allowed_mime_types)
			)
				);
		}

		// 根據 MIME 類型的開頭判斷文件類型
		$file_type = match (true) {
			str_starts_with($file['type'], 'image/') => 'image',
			str_starts_with($file['type'], 'video/') => 'video',
			str_starts_with($file['type'], 'audio/') => 'audio',
			default => 'other',
		};

		if ('image' === $file_type) {
			return $this->handle_single_upload_image($file, $upload_only);
		}

		return $this->handle_single_upload_other($file, $upload_only);
	}


	/**
	 * Handle Single upload IMAGE
	 *
	 * @param array{name:string,type:string,tmp_name:string,error:int,size:int} $file 檔案資訊
	 * @param string|null                                                       $upload_only 是否只上傳，不新增到媒體庫 '0' or '1'
	 *
	 * @return \WP_REST_Response
	 */
	private function handle_single_upload_image( array $file, ?string $upload_only = '0' ): \WP_REST_Response {

		$_FILES['0'] = $file;

		// 獲取圖片尺寸
		$image_info = getimagesize($file['tmp_name']);

		if ($image_info === false) {
			return new \WP_REST_Response(
				[
					'code'    => 'upload_error',
					'message' => __('get image size failed', 'powerhouse'),
					'data'    => $file,
				],
				400
			);
		}
		$width  = $image_info[0];
		$height = $image_info[1];

		if ( $upload_only ) {
			// 直接上傳到 wp-content/uploads 不會新增到媒體庫
			$upload_overrides = [ 'test_form' => false ];
			$upload_result    = \wp_handle_upload( $file, $upload_overrides );

			unset( $upload_result['file'] );
			$upload_result['id']     = null;
			$upload_result['type']   = $file['type']; // @phpstan-ignore-line
			$upload_result['name']   = $file['name']; // @phpstan-ignore-line
			$upload_result['size']   = $file['size']; // @phpstan-ignore-line
			$upload_result['width']  = $width;
			$upload_result['height'] = $height;
			if ( isset( $upload_result['error'] ) ) {
				return new \WP_REST_Response(
					[
						'code'    => 'upload_error',
						'message' => $upload_result['error'],
						'data'    => $upload_result,
					],
					400
				);
			}
		} else {
			// 將檔案上傳到媒體庫
			$attachment_id = \media_handle_upload(
				file_id: '0',
				post_id: 0
			);

			if ( \is_wp_error( $attachment_id ) ) {
				// 處理錯誤
				return new \WP_REST_Response(
					[
						'code'    => 'upload_error',
						'message' => $attachment_id->get_error_message(),
						'data'    => $file,
					],
					400
				);
			}

			$upload_result = [
				'id'     => (string) $attachment_id,
				'url'    => \wp_get_attachment_url( $attachment_id ),
				'type'   => $file['type'],
				'name'   => $file['name'],
				'size'   => $file['size'],
				'width'  => $width,
				'height' => $height,
			];
		}

		// 返回上傳成功的訊息
		return new \WP_REST_Response(
			[
				'code'    => 'upload_success',
				'message' => __('upload file success', 'powerhouse'),
				'data'    => $upload_result,
			],
		);
	}

	/**
	 * Handle Single upload OTHER
	 *
	 * @param array{name:string,type:string,tmp_name:string,error:int,size:int} $file 檔案資訊
	 * @param string|null                                                       $upload_only 是否只上傳，不新增到媒體庫 '0' or '1'
	 *
	 * @return \WP_REST_Response
	 */
	private function handle_single_upload_other( array $file, ?string $upload_only = '0' ): \WP_REST_Response {

		$_FILES['0'] = $file;

		if ( $upload_only ) {
			// 直接上傳到 wp-content/uploads 不會新增到媒體庫
			$upload_overrides = [ 'test_form' => false ];
			$upload_result    = \wp_handle_upload( $file, $upload_overrides );

			unset( $upload_result['file'] );
			$upload_result['id'] = null;
			/** @var array{name: string, type: string, tmp_name: string, size: int, error: int} $file */
			$upload_result['type'] = $file['type'];
			$upload_result['name'] = $file['name'];
			$upload_result['size'] = $file['size'];
			if ( isset( $upload_result['error'] ) ) {
				return new \WP_REST_Response(
					[
						'code'    => 'upload_error',
						'message' => $upload_result['error'],
						'data'    => $upload_result,
					],
					400
				);
			}
		} else {
			// 將檔案上傳到媒體庫
			$attachment_id = \media_handle_upload(
				file_id: '0',
				post_id: 0
			);

			if ( \is_wp_error( $attachment_id ) ) {
				// 處理錯誤
				return new \WP_REST_Response(
					[
						'code'    => 'upload_error',
						'message' => $attachment_id->get_error_message(),
						'data'    => $file,
					],
					400
				);
			}

			$upload_result = [
				'id'   => (string) $attachment_id,
				'url'  => \wp_get_attachment_url( $attachment_id ),
				'type' => $file['type'],
				'name' => $file['name'],
				'size' => $file['size'],
			];
		}

		// 返回上傳成功的訊息
		return new \WP_REST_Response(
			[
				'code'    => 'upload_success',
				'message' => __('upload file success', 'powerhouse'),
				'data'    => $upload_result,
			],
		);
	}

	/**
	 * Handle multiple upload
	 *
	 * @param array{name:string[],type:string[],tmp_name:string[],error:int[],size:int[]} $files 檔案資訊
	 * @param string|null                                                                 $upload_only 是否只上傳，不新增到媒體庫 '0' or '1'
	 *
	 * @return \WP_REST_Response
	 * @throws \Exception 不允許的 MIME 類型
	 */
	private function handle_multiple_upload( array $files, ?string $upload_only = '0' ): \WP_REST_Response {
		$upload_results   = [];
		$upload_overrides = [ 'test_form' => false ];
		$_FILES           = [];

		// 遍歷每個上傳的檔案
		foreach ( $files['tmp_name'] as $key => $tmp_name ) {
			if ( ! empty( $tmp_name ) ) {
				$file = [
					'name'     => $files['name'][ $key ],
					'type'     => $files['type'][ $key ],
					'tmp_name' => $tmp_name,
					'error'    => $files['error'][ $key ],
					'size'     =>$files['size'][ $key ],
				];

				if ( (bool) $this->allowed_mime_types && !in_array($files['type'][ $key ], $this->allowed_mime_types)) {
					throw new \Exception(
						sprintf(
						__('not allowed mime type, only allow: %s', 'powerhouse'),
						implode(', ', $this->allowed_mime_types)
					)
					);
				}

				// 獲取圖片尺寸
				$image_info = getimagesize($file['tmp_name']);

				if ($image_info === false) {
					return new \WP_REST_Response(
						[
							'code'    => 'upload_error',
							'message' => __('get image size failed', 'powerhouse'),
							'data'    => $file,
						],
						400
					);
				}
				$width  = $image_info[0];
				$height = $image_info[1];

				$_FILES[ $key ] = $file;

				if ( $upload_only ) {
					// 直接上傳到 wp-content/uploads 不會新增到媒體庫
					$upload_result = \wp_handle_upload( $file, $upload_overrides );
					unset( $upload_result['file'] );
					$upload_result['id'] = null;
					/** @var array{name: string, type: string, tmp_name: string, size: int, error: int} $file */
					$upload_result['type']   = $file['type'];
					$upload_result['name']   = $file['name'];
					$upload_result['size']   = $file['size'];
					$upload_result['width']  = $width;
					$upload_result['height'] = $height;
					if ( isset( $upload_result['error'] ) ) {
						return new \WP_REST_Response(
							[
								'code'    => 'upload_error',
								'message' => $upload_result['error'],
								'data'    => $upload_result,
							],
							400
						);
					}
				} else {
					// 將檔案上傳到媒體庫
					$attachment_id = \media_handle_upload(
						file_id: $key,
						post_id: 0
					);

					if ( \is_wp_error( $attachment_id ) ) {
						// 處理錯誤
						return new \WP_REST_Response(
							[
								'code'    => 'upload_error',
								'message' => $attachment_id->get_error_message(),
								'data'    => $file,
							],
							400
						);
					}

					$upload_result = [
						'id'     => (string) $attachment_id,
						'url'    => \wp_get_attachment_url( $attachment_id ),
						'type'   => $file['type'],
						'name'   => $file['name'],
						'size'   => $file['size'],
						'width'  => $width,
						'height' => $height,
					];
				}

				$upload_results[] = $upload_result;
			}
		}

		// 返回上傳成功的訊息
		return new \WP_REST_Response(
			[
				'code'    => 'upload_success',
				'message' => __('upload file success', 'powerhouse'),
				'data'    => $upload_results,
			],
		);
	}
}
