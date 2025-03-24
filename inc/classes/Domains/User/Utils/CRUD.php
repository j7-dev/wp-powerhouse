<?php
/**
 * User Utils
 */

declare(strict_types=1);

namespace J7\Powerhouse\Domains\User\Utils;

use J7\WpUtils\Classes\WP;
use Automattic\WooCommerce\Admin\API\Reports\Customers\Query as CustomersQuery;
use J7\Powerhouse\Domains\Order\Utils\Info;


/**
 * Class CRUD
 */
abstract class CRUD {

	/**
	 * Create a new user
	 *
	 * @see https://developer.wordpress.org/reference/functions/wp_insert_user/
	 *
	 * 簡單的新增，沒有太多參數，所以不使用 Converter
	 *
	 * @param array<string, mixed> $args Arguments.
	 *
	 * @return int|\WP_Error
	 */
	public static function create_user( array $args = [] ): int|\WP_Error {

		[
			'data'      => $data,
			'meta_data' => $meta_data,
		] = WP::separator( $args );

		$data['meta_input'] = $meta_data;

		/** @var array{ID?: int, user_pass?: string, user_login?: string, user_nicename?: string, user_url?: string, user_email?: string, display_name?: string, nickname?: string, ...}|object $data */
		return \wp_insert_user($data);
	}


	/**
	 * Update a user
	 *
	 * @see https://developer.wordpress.org/reference/functions/wp_insert_user/
	 *
	 * 簡單的新增，沒有太多參數，所以不使用 Converter
	 *
	 * @param array<string, mixed> $args Arguments.
	 *
	 * @return int|\WP_Error
	 */
	public static function update_user( array $args = [] ): int|\WP_Error {

		[
			'data'      => $data,
			'meta_data' => $meta_data,
		] = WP::separator( $args );

		$data['meta_input'] = $meta_data;

		/** @var array{ID?: int, user_pass?: string, user_login?: string, user_nicename?: string, user_url?: string, user_email?: string, display_name?: string, nickname?: string, ...}|object $data */
		return \wp_update_user($data);
	}


	/**
	 * Format user details
	 *
	 * @param int           $user_id  User ID.
	 * @param array<string> $meta_keys  要暴露的前端 meta key
	 * @return array{
	 * id: string,
	 *   user_login: string,
	 *   user_email: string,
	 *   display_name: string,
	 *   user_registered: string,
	 *   user_registered_human: string|null,
	 *   user_avatar_url: mixed,
	 *   description: string,
	 *   roles: array<string>,
	 *   billing_phone: string,
	 *   pc_birthday: int|null,
	 *   edit_url: string,
	 *   date_last_active: string|null,
	 *   date_last_order: string|null,
	 *   orders_count: int|null,
	 *   total_spend: string|null,
	 *   avg_order_value: string|null
	 * }|null
	 */
	public static function format_user_record( int $user_id, array $meta_keys = [] ): array|null {
		$user = \get_user_by( 'ID', $user_id );

		if ( ! $user ) {
			return [
				'id'                    => '',
				'user_login'            => '',
				'user_email'            => '',
				'display_name'          => '訪客',
				'user_registered'       => '',
				'user_registered_human' => '',
				'user_avatar_url'       => 'https://www.google.com/url?sa=i&url=https%3A%2F%2Fwww.vecteezy.com%2Fvector-art%2F26631837-incognito-icon-vector-symbol-design-illustration&psig=AOvVaw0KlMIe2lsttP8SC-47PjOn&ust=1741950083858000&source=images&cd=vfe&opi=89978449&ved=0CBQQjRxqFwoTCPDiwPbzhowDFQAAAAAdAAAAABAb',
				'description'           => '',
				'roles'                 => [],
				'billing_phone'         => '',
				'edit_url'              => '',
			];
		}

		$user_registered      = (string) $user->get( 'user_registered' );
		$user_registered_time = \strtotime($user_registered);
		$user_avatar_url      = \get_user_meta($user_id, 'user_avatar_url', true);
		$user_avatar_url      = $user_avatar_url ? $user_avatar_url : \get_avatar_url( $user_id );

		$meta_keys_array = self::get_meta_keys_array( $user, $meta_keys );

		$base_array = [
			'id'                    => (string) $user_id,
			'user_login'            => $user->user_login,
			'user_email'            => $user->user_email,
			'display_name'          => $user->display_name,
			'user_registered'       => $user_registered,
			'user_registered_human' => $user_registered_time ? \human_time_diff( $user_registered_time ) : null,
			'user_avatar_url'       => $user_avatar_url,
			'description'           => $user->description,
			'roles'                 => $user->roles,
			'billing_phone'         => \get_user_meta($user_id, 'billing_phone', true),
			'pc_birthday'           => ( (int) \get_user_meta($user_id, 'pc_birthday', true) ) ?: null,
			'edit_url'              => \get_edit_user_link( $user_id ),
		];

		// 取得 customer 資料
		$customers_query = new CustomersQuery(
			[
				'customers'    => [ $user_id ],
				// If unset, these params have default values that affect the results.
				'order_after'  => null,
				'order_before' => null,
			]
			);

		$customer_data    = $customers_query->get_data();
		$customer_history = $customer_data->data[0] ?? null;

		$base_array['date_last_active'] = $customer_history['date_last_active'] ?? null;
		$base_array['date_last_order']  = $customer_history['date_last_order'] ?? null;
		$base_array['orders_count']     = $customer_history['orders_count'] ?? null;
		$base_array['total_spend']      = $customer_history['total_spend'] ?? null;
		$base_array['avg_order_value']  = $customer_history['avg_order_value'] ?? null;

		$formatted_array = array_merge(
			$base_array,
			$meta_keys_array
		);

		// ENHANCE 未來可能會有階層  上下線關係的 user!?

		/** @var array{
		 *   id: string,
		 *   user_login: string,
		 *   user_email: string,
		 *   display_name: string,
		 *   user_registered: string,
		 *   user_registered_human: string|null,
		 *   user_avatar_url: mixed,
		 *   description: string,
		 *   roles: array<string>,
		 *   billing_phone: string,
		 *   pc_birthday: int|null,
		 *   edit_url: string,
		 *   date_last_active: string|null,
		 *   date_last_order: string|null,
		 *   orders_count: int|null,
		 *   total_spend: string|null,
		 *   avg_order_value: string|null
		 * } $formatted_array */
		return $formatted_array;
	}

	/**
	 * Format user details
	 *
	 * @param int           $user_id  User ID.
	 * @param array<string> $meta_keys  要暴露的前端 meta key
	 * @return array{id: string, user_login: string, user_email: string, display_name: string, user_registered: string, user_registered_human: string|null, user_avatar_url: mixed, description: string, ...}|null
	 */
	public static function format_user_details( int $user_id, array $meta_keys = [] ): array|null {
		$new_meta_keys = [
			...$meta_keys,
			'first_name',
			'last_name',
			'description',
		];

		$user_record = self::format_user_record( $user_id, $new_meta_keys );

		$extra_array = Info::to_user_array( $user_id );

		return array_merge( $user_record, $extra_array );
	}

	/**
	 * 取得 meta keys array
	 *
	 * @param \WP_User      $user 用戶.
	 * @param array<string> $meta_keys 要暴露出來的 meta keys.
	 * @return array<string, mixed>
	 */
	public static function get_meta_keys_array( \WP_User $user, array $meta_keys = [] ): array {
		$meta_keys_array = [];
		foreach ($meta_keys as $meta_key) {
			$meta_keys_array[ $meta_key ] = \get_user_meta( $user->ID, $meta_key, true );
		}

		// 可以改寫 meta_keys
		// @phpstan-ignore-next-line
		return \apply_filters( 'powerhouse/user/get_meta_keys_array', $meta_keys_array, $user );
	}


	/**
	 * 將的參數拆成 data 與 meta_data
	 * data 可以直接給 WP_User_Query 查詢，其他的參數是 meta_data 查詢
	 *
	 * @see https://developer.wordpress.org/reference/classes/wp_user_query/
	 *
	 * @param array<string, mixed> $args Arguments.
	 * @return array{data: array<string, mixed>, meta_data: array<string, mixed>}
	 */
	public static function query_separator( array $args ): array {
		$args['number']      = $args['posts_per_page'] ?? 20; // @phpstan-ignore-line
		$args['count_total'] = true; // @phpstan-ignore-line
		unset( $args['posts_per_page'] );

		// 將資料拆成 data 與 meta_data
		$data      = [];
		$meta_data = [];

		$data_fields = [
			'role',
			'role__in',
			'role__not_in',
			'include',
			'exclude',
			'blog_id',
			'search',
			'search_columns',
			'number',
			'offset',
			'paged',
			'orderby',
			'order',
			'date_query',
			'who',
			'count_total', // 是否計算總數
			'has_published_posts',
			'fields', // return 的 fields
		];
		foreach ( $args as $key => $value ) {
			if ('search' === $key) {
				$data[ $key ] = "*{$value}*";
				continue;
			}

			if ( \in_array( $key, $data_fields, true ) ) {
				$data[ $key ] = $value;
			} else {
				$meta_data[ $key ] = $value;
			}
		}

		return [
			'data'      => $data,
			'meta_data' => $meta_data,
		];
	}


	/**
	 * 準備查詢參數
	 *
	 * @param array<string, mixed> $args Arguments.
	 * @return array<string, mixed>
	 */
	public static function prepare_query_args( array $args ): array {

		[
			'data'      => $data,
			'meta_data' => $meta_data,
		] = self::query_separator($args);

		if ($meta_data) {
			$data['meta_query'] = [
				'relation' => 'AND',
			];
		}

		foreach ( $meta_data as $key => $value ) {
			if ('billing_phone' === $key) {
				$data['meta_query'][] = [
					'key'     => $key,
					'value'   => $value,
					'compare' => 'LIKE',
				];

				continue;
			}

			if ('pc_birthday' === $key && is_array($value)) {
				$data['meta_query'][] = [
					'key'     => $key,
					'value'   => $value[0] ?? 0,
					'compare' => '>=',
					'type'    => 'NUMERIC',
				];
				$data['meta_query'][] = [
					'key'     => $key,
					'value'   => $value[1] ?? time(),
					'compare' => '<=',
					'type'    => 'NUMERIC',
				];

				continue;
			}
			$data['meta_query'][] = [
				'key'   => $key,
				'value' => $value,
			];
		}

		$default_args = [
			'orderby' => 'ID',
			'order'   => 'DESC',
		];

		return \wp_parse_args( $data, $default_args );
	}
}
