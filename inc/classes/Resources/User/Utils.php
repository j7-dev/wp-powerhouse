<?php
/**
 * User Utils
 */

declare(strict_types=1);

namespace J7\Powerhouse\Resources\User;

use J7\WpUtils\Classes\WP;

/**
 * Class Utils
 */
abstract class Utils {

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
	 * @param \WP_User $user  User.
	 * @return array{id: string, user_login: string, user_email: string, display_name: string, user_registered: string, user_registered_human: string|null, user_avatar_url: mixed, description: string, ...}|array{}
	 */
	public static function format_user_details( \WP_User $user ): array {

		if ( ! ( $user instanceof \WP_User ) ) {
			return [];
		}
		$user_id              = $user->ID;
		$user_registered      = (string) $user->get( 'user_registered' );
		$user_registered_time = \strtotime($user_registered);
		$user_avatar_url      = \get_user_meta($user_id, 'user_avatar_url', true);
		$user_avatar_url      = !!$user_avatar_url ? $user_avatar_url : \get_avatar_url( $user_id );

		$meta_keys_array = self::get_meta_keys_array( $user );

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
		];

		$formatted_array = array_merge(
			$base_array,
			$meta_keys_array
		);

		// TODO 未來可能會有階層  上下限關係的 user!?

		/** @var array{id: string, user_login: string, user_email: string, display_name: string, user_registered: string, user_registered_human: string|null, user_avatar_url: mixed, description: string, ...} $formatted_array */
		return $formatted_array;
	}

	/**
	 * 取得 meta keys array
	 *
	 * @param \WP_User      $user 用戶.
	 * @param array<string> $meta_keys 要暴露出來的 meta keys.
	 * @return array<string, mixed>
	 */
	public static function get_meta_keys_array( \WP_User $user, ?array $meta_keys = [] ): array {
		if (!$meta_keys) {
			return [];
		}

		$meta_keys_array = [];
		foreach ($meta_keys as $meta_key) {
			$meta_keys_array[ $meta_key ] = \get_user_meta( $user->ID, $meta_key, true );
		}

		// 可以改寫 meta_keys
		// @phpstan-ignore-next-line
		return \apply_filters( 'powerhouse/user/get_meta_keys_array', $meta_keys_array, $user );
	}
}
