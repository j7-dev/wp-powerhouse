<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Woocommerce\Model;

use J7\WpUtils\Classes\DTO;

/**
 * PostStatuses DTO
 * */
class PostStatuses extends DTO {
	/** @var array<array{value: string, label: string, color: string}> $post_statuses */
	public array $post_statuses;

	/** @var array<array{value: string, label: string, color: string}> $post_statuses_mapper */
	protected static array $post_statuses_mapper = [
		'publish' => [
			'value' => 'publish',
			'label' => '已發佈',
			'color' => 'blue',
		],
		'pending' => [
			'value' => 'pending',
			'label' => '送交審閱',
			'color' => 'volcano',
		],
		'draft' => [
			'value' => 'draft',
			'label' => '草稿',
			'color' => 'orange',
		],
		'private' => [
			'value' => 'private',
			'label' => '私密',
			'color' => 'purple',
		],
		'trash' => [
			'value' => 'trash',
			'label' => '回收桶',
			'color' => 'red',
		],
	];

	/** 取得 PostStatuses @return self */
	public static function instance(): self {
		$post_status_array = get_post_statuses(); // key => name 的 array
		$post_statuses     = [];
		foreach ( $post_status_array as $post_status => $post_status_name ) {
			if ( isset( self::$post_statuses_mapper[ $post_status ] ) ) {
				$post_statuses[] = self::$post_statuses_mapper[ $post_status ];
				continue;
			}
			$post_statuses[] = [
				'value' => $post_status,
				'label' => $post_status_name,
				'color' => 'default',
			];
		}

		return new self(
			[
				'post_statuses' => $post_statuses,
			]
		);
	}
}
