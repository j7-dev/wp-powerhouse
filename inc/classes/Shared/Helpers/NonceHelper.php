<?php

declare(strict_types=1);

namespace J7\Powerhouse\Shared\Helpers;

/**
 * Nonce Helper
 * 創建 & 驗證 nonce
 */
final class NonceHelper {

	/** @var string */
	private string $prefix = 'ph_nonce:';


	/**
	 * 建立 Nonce 服務
	 *
	 * @param string $key 創建 nonce 的 key，通常會是 Email
	 * @param int    $ttl 時間限制(秒)
	 */
	public function __construct(
		private readonly string $key,
		private readonly int $ttl = 600
	) {
		if ($key === '') {
			throw new \InvalidArgumentException('Nonce key 不可為空');
		}
		if ($ttl <= 0) {
			throw new \InvalidArgumentException('Nonce TTL 需為正整數');
		}
	}

	/** @return string 創建 nonce */
	public function create(): string {
		// 建立高熵 nonce（URL-safe）
		$bytes = \random_bytes(32);
		$nonce = \rtrim(\strtr(\base64_encode($bytes), '+/', '-_'), '=');

		$t_key = $this->transient_key( $nonce);

		$ok = \set_transient( $t_key, 1, $this->ttl);
		if ($ok !== true) {
			throw new \RuntimeException('建立 nonce 失敗');
		}

		return $nonce;
	}

	/**
	 * 驗證 nonce 是否有效（一次性，驗證通過即失效）
	 *
	 * @param string $nonce 待驗證的 nonce 字串
	 * @return array{0:bool, 1:bool} 是否通過 & 過期 [$is_valid, $is_expired]
	 */
	public function verify( string $nonce ): array {
		if ($nonce === '') {
			throw new \InvalidArgumentException('nonce 不可為空');
		}

		$t_key      = $this->transient_key( $nonce);
		$val        = \get_transient( $t_key);
		$is_valid   = $val === 1;
		$is_expired = $val === false;

		// 一次性使用：驗證通過後立即刪除
		\delete_transient( $t_key);
		return [ $is_valid, $is_expired ];
	}

	/**
	 * 依據 key 與 nonce 組合唯一 transient key
	 */
	private function transient_key( string $nonce ): string {
		// 限制長度避免超過 transient key 的限制
		$keyHash = \substr(\hash('sha256', $this->key), 0, 16);
		return $this->prefix . $keyHash . ':' . $nonce;
	}
}
