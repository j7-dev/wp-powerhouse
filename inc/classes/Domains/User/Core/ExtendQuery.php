<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\User\Core;

use J7\WpUtils\Classes\General;
use J7\Powerhouse\Domains\Post\Service\MetaQueryBuilder;

/**
 * 拓展 User Query
 * 拓展特殊的查詢
 */
final class ExtendQuery {
	use \J7\WpUtils\Traits\SingletonTrait;

	/** Constructor */
	public function __construct() {
		\add_filter('powerhouse/user/prepare_query_args/meta_query_builder', [ $this, 'extend_query_args' ], 10);
	}

	/**
	 * 拓展 User Query
	 *
	 * @param MetaQueryBuilder $builder 查詢參數
	 * @return MetaQueryBuilder
	 */
	public function extend_query_args( MetaQueryBuilder $builder ): MetaQueryBuilder {
		$builder->find( 'billing_phone' )?->set( [ 'compare' => 'LIKE' ] );
		$builder->find( 'user_birthday' )?->format_value( '-{value}-' )?->set( [ 'compare' => 'LIKE' ] );
		return $builder;
	}
}
