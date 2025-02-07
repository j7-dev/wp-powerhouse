<?php
/**
 * Product Save
 * 商品儲存相關 Helper
 */

declare(strict_types=1);

namespace J7\Powerhouse\Resources\Product\Helper;

/**
 * Class Save
 */
class Save {

	/**
	 * 處理儲存產品資料
	 *
	 * @param \WC_Product          $product 產品
	 * @param array<string, mixed> $data 資料
	 *
	 * @return void
	 */
	public static function data( \WC_Product $product, array $data = [] ): void {

		\do_action('powerhouse/product/before_save_data', $product, $data);

		foreach ( $data as $key => $value ) {
			$method_name = "set_{$key}";
			if (method_exists($product, $method_name)) {
				$product->$method_name( $value );
			}
		}

		$product->save();

		\do_action('powerhouse/product/after_save_data', $product, $data);
	}

	/**
	 * 處理儲存產品 meta data
	 *
	 * TODO 前端送 type 進來後，怎麼變更商品類型?
	 *
	 * @param \WC_Product          $product 產品
	 * @param array<string, mixed> $meta_data 資料
	 *
	 * @return void
	 * @throws \Exception Exception 當 WC_Subscription 不存在時
	 */
	public static function meta_data( \WC_Product $product, array $meta_data = [] ): void {
		// type 會被儲存為商品的類型，不需要再額外存進 meta data
		$is_subscription = strpos( (string) ( $meta_data['type'] ?? '' ), 'subscription') !== false; // 判斷是否為訂閱商品
		unset($meta_data['type']);

		if ($is_subscription && !class_exists('WC_Subscription')) {
			throw new \Exception(__('WC_Subscription class does not exist, please make sure WooCommerce Subscription is installed', 'powerhouse'));
		}

		unset( $meta_data['images'] ); // 圖片只做顯示用，不用存
		unset( $meta_data['files'] ); // files 會上傳，不用存

		// 如果是非訂閱商品，則刪除訂閱商品的相關資料
		if (!$is_subscription) {
			$fields_to_delete = Subscription::get_fields();
			foreach ($fields_to_delete as $field) {
				$product->delete_meta_data($field);
			}
		}

		\do_action('powerhouse/product/before_save_meta_data', $product, $meta_data);

		// 最後再來處理剩餘的 meta_data
		foreach ( $meta_data as $key => $value ) {
			$product->update_meta_data( $key, $value ); // @phpstan-ignore-line
		}

		$product->save_meta_data();

		\do_action('powerhouse/product/after_save_meta_data', $product, $meta_data);

		$id = $product->get_id();

		\wc_delete_product_transients($id);
	}
}
