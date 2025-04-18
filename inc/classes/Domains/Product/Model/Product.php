<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Product\Model;

/**
 * 商品 Product DTO
 * 各個個子 DTO 組合而成商品 DTO
 */
final class Product extends DTO {

	/** @var Basic $basic 商品基本資料 */
	protected Basic $basic;

	/** @var Price $price 商品價格 */
	protected Price $price;

	/** @var Taxonomy $taxonomy 商品分類、標籤、品牌 */
	protected Taxonomy $taxonomy;

	/** @var Stock $stock 商品庫存 */
	protected Stock $stock;

	/** @var Sales $sales 促銷、交叉銷售相關 */
	protected Sales $sales;

	/** @var Size $size 商品尺寸 */
	protected Size $size;

	/** @var Attribute $attribute 商品屬性 */
	protected Attribute $attribute;

	/** @var Subscription $subscription 訂閱相關 */
	protected Subscription $subscription;

	/** @var Variation $variation 可變商品的變體 */
	protected Variation $variation;

	/** @var array<string> $meta_keys 要包含的 meta 欄位 */
	protected array $meta_keys = [];

	/**
	 * 取得實例
	 *
	 * @param \WC_Product $product 商品
	 * @param array       $meta_keys 要包含的 meta 欄位
	 */
	public static function instance(
		\WC_Product $product,
		array $meta_keys = []
	): self {

		$args = [
			'basic'        => Basic::instance($product),
			'detail'       => Detail::instance($product),
			'price'        => Price::instance($product),
			'stock'        => Stock::instance($product),
			'sales'        => Sales::instance($product),
			'size'         => Size::instance($product),
			'subscription' => Subscription::instance($product),
			'taxonomy'     => Taxonomy::instance($product),
			'attribute'    => Attribute::instance($product),
			'variation'    => Variation::instance($product, $meta_keys),
			'product'      => $product,
			'meta_keys'    => $meta_keys,
		];

		$strict = \wp_get_environment_type() === 'local';

		$instance = new self($args, $strict);
		return $instance;
	}

	/**
	 * 轉換為陣列
	 *
	 * @param array<string>|null $partials 要包含的 partial，可以輸入 'basic', 'detail', 'price', 'stock', 'sales', 'size', 'subscription', 'taxonomy', 'attribute', 'variation'
	 * @return array
	 */
	public function to_array( $partials = null ): array {
		$partials = $partials ? $partials : [
			'basic',
			// 'detail',
			'price',
			'stock',
			'sales',
			'size',
			// 'subscription',
			'taxonomy',
			'attribute',
			'variation',
		];
		$array    = [];

		foreach ($partials as $partial) {
			if ('variation' === $partial) {
				$array = array_merge($array, $this->variation->to_array($partials));
				continue;
			}

			$array = array_merge($array, $this->{$partial}->to_array());
		}

		return array_merge(
			$array,
			$this->get_meta_keys_array(),
		);
	}

	/**
	 * 取得 meta keys array
	 *
	 * @return array<string, mixed>
	 */
	protected function get_meta_keys_array(): array {
		$meta_keys_array = [];
		foreach ($this->meta_keys as $meta_key) {
			$meta_keys_array[ $meta_key ] = $this->product->get_meta( $meta_key );
		}

		// 可以改寫 meta_keys
		// @phpstan-ignore-next-line
		return \apply_filters( 'powerhouse/product/get_meta_keys_array', $meta_keys_array, $this->product );
	}
}
