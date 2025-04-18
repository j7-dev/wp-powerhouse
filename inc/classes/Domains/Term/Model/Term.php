<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Term\Model;

use J7\WpUtils\Classes\DTO;

/** 商品 Term DTO */
final class Term extends DTO {

	/** @var string $id */
	public string $id;

	/** @var string $name 名稱 */
	public string $name = '';

	/** @var string $slug */
	public string $slug = '';

	/** @var string $term_taxonomy_id */
	public string $term_taxonomy_id = '';

	/** @var string $taxonomy ex 分類/標籤等等... */
	public string $taxonomy = '';

	/** @var string $description 描述 */
	public string $description = '';

	/** @var string $parent 父 ID */
	public string $parent = '';

	/** @var int $count term 數量 */
	public int $count = 0;

	/** @var int $order term 排序 */
	public int $order = 0;

	/** @var Term[]|null  $children 子孫 */
	public array|null $children = null;

	/**
	 * 取得實例
	 *
	 * @param \WP_Term $term 商品
	 */
	public static function instance( \WP_Term $term ): self {

		$children = \get_terms(
			[
				'taxonomy'   => $term->taxonomy,
				'parent'     => $term->term_id,
				'hide_empty' => false,
				'orderby'    => 'order',
				'order'      => 'ASC',
			]
			);

		$args = [
			'id'               => (string) $term->term_id,
			'name'             => $term->name,
			'slug'             => $term->slug,
			'term_taxonomy_id' => (string) $term->term_taxonomy_id,
			'taxonomy'         => $term->taxonomy,
			'description'      => $term->description,
			'parent'           => $term->parent ? (string) $term->parent : '',
			'count'            => (int) $term->count,
			'order'            => (int) \get_term_meta( $term->term_id, 'order', true ),
			'children'         => array_map(
				fn ( \WP_Term $child ) => self::instance( $child ),
				$children
			),
		];

		$strict = \wp_get_environment_type() === 'local';

		$instance = new self($args, $strict);
		return $instance;
	}

	/**
	 * 轉換為陣列
	 *
	 * @return array
	 */
	public function to_array(): array {
		$array = parent::to_array();

		if (!$array['children']) {
			$array['children'] = null;
			return $array;
		}

		$array['children'] = array_map(
			fn ( Term $child ) => $child->to_array(),
			$array['children']
		);

		return $array;
	}
}
