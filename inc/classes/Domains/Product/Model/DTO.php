<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Product\Model;

use J7\WpUtils\Classes\DTO as DTOBase;

/**
 * 自訂 DTO
 * 會將商品實例存入到 $product 中
 */
abstract class DTO extends DTOBase {
	/** @var \WC_Product $product 商品實例 */
	protected \WC_Product $product;
}
