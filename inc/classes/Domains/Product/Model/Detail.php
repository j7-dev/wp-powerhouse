<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Product\Model;

/** 商品詳細資料 DTO */
final class Detail extends DTO {

	/** @var string $description 商品描述 */
	public string $description;

	/** @var string $short_description 商品簡短描述 */
	public string $short_description;

	/** @var string $page_template 商品頁模板 '' 代表 default */
	public string $page_template = 'default';

	/** @var array<array{label: string, value: string}> $page_template_options 商品頁模板 */
	public array $page_template_options;

	/** @var string $_variation_description 變體的描述 */
	public string $_variation_description = '';

	/**
	 * 取得實例
	 *
	 * @param \WC_Product $product 商品
	 */
	public static function instance( $product ): self {
		$product_id            = $product->get_id();
		$page_template_options = [
			[
				'label' => '預設模板',
				'value' => '',
			],
		];

		if ( !function_exists( '\get_page_templates' ) ) {
			require_once ABSPATH . 'wp-admin/includes/theme.php';
		}

		/** @var array<string, string> $page_templates Label => Value */
		$page_templates = \get_page_templates($product_id, 'product');

		foreach ( $page_templates as $label => $value ) {
			$page_template_options[] = [
				'label' => $label,
				'value' => $value,
			];
		}

		$args = [
			'description'            => $product->get_description(),
			'short_description'      => $product->get_short_description(),
			'page_template'          => \get_page_template_slug($product_id) ?: '',
			'page_template_options'  => $page_template_options,
			'_variation_description' => $product->get_meta('_variation_description'),
		];

		$instance = new self($args);
		return $instance;
	}
}
