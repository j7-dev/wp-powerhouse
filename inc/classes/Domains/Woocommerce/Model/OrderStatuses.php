<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Woocommerce\Model;

use J7\WpUtils\Classes\DTO;

/**
 * OrderStatuses DTO
 * */
class OrderStatuses extends DTO {
	/** @var array<array{value: string, label: string, color: string}> $order_statuses */
	public array $order_statuses;

	/** @var array<array{value: string, label: string, color: string}> $order_statuses_mapper */
	protected static array $order_statuses_mapper = [
		'processing' => [
			'value' => 'processing',
			'label' => '處理中',
			'color' => '#108ee9',
		],
		'pending' => [
			'value' => 'pending',
			'label' => '等待付款中',
			'color' => 'volcano',
		],
		'wmp-in-transit' => [
			'value' => 'wmp-in-transit',
			'label' => '配送中',
			'color' => '#2db7f5',
		],
		'wmp-shipped' => [
			'value' => 'wmp-shipped',
			'label' => '已出貨',
			'color' => 'green',
		],
		'on-hold' => [
			'value' => 'on-hold',
			'label' => '保留',
			'color' => 'gold',
		],
		'completed' => [
			'value' => 'completed',
			'label' => '已完成',
			'color' => '#87d068',
		],
		'cancelled' => [
			'value' => 'cancelled',
			'label' => '已取消',
			'color' => 'orange',
		],
		'refunded' => [
			'value' => 'refunded',
			'label' => '已退款',
			'color' => 'volcano',
		],
		'failed' => [
			'value' => 'failed',
			'label' => '失敗訂單',
			'color' => 'magenta',
		],
		'checkout-draft' => [
			'value' => 'checkout-draft',
			'label' => '未完成結帳',
			'color' => 'gold',
		],
		'ry-at-cvs' => [
			'value' => 'ry-at-cvs',
			'label' => 'RY 等待撿貨中',
			'color' => 'cyan',
		],
		'ry-out-cvs' => [
			'value' => 'ry-out-cvs',
			'label' => 'RY 訂單過期',
			'color' => 'purple',
		],
	];

	/** 取得 OrderStatuses @return self */
	public static function instance(): self {
		$order_status_array = \wc_get_order_statuses(); // key => name 的 array
		$order_statuses     = [];
		foreach ( $order_status_array as $order_status => $order_status_name ) {
			// 移除 wc- 開頭
			if ( \str_starts_with( $order_status, 'wc-' ) ) {
				$order_status = str_replace( 'wc-', '', $order_status );
			}
			if ( isset( self::$order_statuses_mapper[ $order_status ] ) ) {
				$order_statuses[] = self::$order_statuses_mapper[ $order_status ];
				continue;
			}
			$order_statuses[] = [
				'value' => $order_status,
				'label' => $order_status_name,
				'color' => 'default',
			];
		}

		return new self(
			[
				'order_statuses' => $order_statuses,
			]
			);
	}
}
