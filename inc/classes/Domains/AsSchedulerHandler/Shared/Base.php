<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\AsSchedulerHandler\Shared;

/**
 * Class Base 基類
 *
 * 重要觀念，因為使用 `as_next_scheduled_action` 查找下一個排程時
 * 會比對 $hook, $args, $group
 * 其中 $args 會被序列化成 json string，所有有順序姓
 * 先定義好方法就可以避免查詢時，因為順序不同，導致查詢不到
 * 傳入資源
 * 1. 繼承
 * 2. 實作 get_args()
 * 3. new Base( $item )
 * 4. 使用 get_next_action_id() 取得 action_id
 * 5. 使用 unschedule() 取消排程
 *
 * after_schedule_single 等方法，可以在排程後執行，例如寫入 log
 */
abstract class Base {

	/** @var string 排程的 hook {plugin_name}/{version}/{domains}/{action} */
	protected static string $hook;

	/** @var array 排程的參數 */
	protected array $args;

	/** Constructor，每次傳入的資源實例可能不同 */
	public function __construct(
		/** @var mixed 資源實例 */
		protected $item,
	) {
		$this->args = $this->get_args();
	}

	/** 取得排程的參數，執行時會傳入 action_callback @return array<string, string> */
	abstract protected function get_args(): array;

	/**
	 * 取得排程的 callback
	 *
	 * @param array<string, string> $args 排程的參數
	 * @return void
	 */
	abstract public static function action_callback( $args ): void;

	/**
	 * 取得下一個排程的 action_id
	 *
	 * @param string $group 排程的群組
	 * @return int|null 下一個排程的 action_id
	 */
	public function get_next_action_id( $group = '' ): int|null {
		$action_id = \as_next_scheduled_action( static::$hook, [ $this->args ], $group );
		return $action_id ?: null;
	}

	/**
	 * 檢查是否已經有排程
	 *
	 * @param string $group 排程的群組
	 * @return bool 是否已經有排程
	 */
	public function has_scheduled( $group = '' ): bool {
		return \as_has_scheduled_action( static::$hook, [ $this->args ], $group );
	}


	/**
	 * 單次排程
	 *
	 * @param int    $timestamp 排程的時間
	 * @param string $group     排程的群組
	 * @param bool   $unique    是否唯一
	 * @param int    $priority  排程的優先級
	 *
	 * @return int|null 排程的 action_id
	 */
	public function schedule_single( int $timestamp, string $group = '', bool $unique = false, int $priority = 10 ): int|null {

		$action_id = \as_schedule_single_action( $timestamp, static::$hook, [ $this->args ], $group, $unique, $priority ) ?: null;

		$method_name = 'after_' . __FUNCTION__;
		if ( method_exists( static::class, $method_name ) ) {
			$this->{$method_name}( $action_id, $timestamp, $group );
		}

		return $action_id;
	}

	/**
	 * 定期排程
	 *
	 * @param int    $timestamp 排程的時間
	 * @param int    $interval  排程的間隔
	 * @param string $group     排程的群組
	 * @param string $unique    排程的唯一值
	 * @param int    $priority  排程的優先級
	 */
	public function schedule_recurring( int $timestamp, int $interval, string $group = '', string $unique = '', int $priority = 10 ): int|null {
		$action_id = \as_schedule_recurring_action( $timestamp, $interval, static::$hook, [ $this->args ], $group, $unique, $priority ) ?: null;

		$method_name = 'after_' . __FUNCTION__;
		if ( method_exists( static::class, $method_name ) ) {
			$this->{$method_name}( $action_id, $timestamp, $interval, $group );
		}

		return $action_id;
	}

	/**
	 * 非同步排程
	 *
	 * @param int    $timestamp 排程的時間
	 * @param string $group     排程的群組
	 * @param string $unique    排程的唯一值
	 * @param int    $priority  排程的優先級
	 */
	public function schedule_async( int $timestamp, string $group = '', string $unique = '', int $priority = 10 ): int|null {
		$action_id = \as_schedule_single_action( $timestamp, static::$hook, [ $this->args ], $group, $unique, $priority ) ?: null;

		$method_name = 'after_' . __FUNCTION__;
		if ( method_exists( static::class, $method_name ) ) {
			$this->{$method_name}( $action_id, $timestamp, $group );
		}

		return $action_id;
	}

	/**
	 * 使用 cron 排程
	 *
	 * @param string $timestamp 排程的時間
	 * @param string $group     排程的群組
	 * @param string $unique    排程的唯一值
	 * @param int    $priority  排程的優先級
	 */
	public function schedule_cron( string $timestamp, string $group = '', string $unique = '', int $priority = 10 ): int|null {
		$action_id = \as_schedule_cron_action( $timestamp, static::$hook, [ $this->args ], $group, $unique, $priority ) ?: null;

		$method_name = 'after_' . __FUNCTION__;
		if ( method_exists( static::class, $method_name ) ) {
			$this->{$method_name}( $action_id, $timestamp, $group );
		}

		return $action_id;
	}

	/**
	 * 取消排程
	 *
	 * @param string $group 排程的群組
	 * @return int|null 取消的排程 action_id
	 */
	public function unschedule( $group = '' ): int|null {
		$action_id = $this->get_next_action_id($group);
		if ( ! $action_id ) {
			return null;
		}
		\ActionScheduler_Store::instance()->delete_action( (string) $action_id);

		$method_name = 'after_' . __FUNCTION__;
		if ( method_exists( static::class, $method_name ) ) {
			$this->{$method_name}($action_id, $group);
		}

		return $action_id;
	}

	/**
	 * 取消所有排程
	 *
	 * @param string $group 排程的群組
	 * @return void
	 */
	public function unschedule_all( $group = '' ): void {
		\as_unschedule_all_actions( static::$hook, [ $this->args ], $group );

		$method_name = 'after_' . __FUNCTION__;
		if ( method_exists( static::class, $method_name ) ) {
			$this->{$method_name}($group);
		}
	}

	/**
	 * 註冊排程
	 * 建議在 plugins_loaded 時註冊 或 在 init 時註冊
	 *
	 * @return void
	 */
	public static function register(): void {
		\add_action( static::$hook, [ static::class, 'action_callback' ] );
	}
}
