<?php

declare(strict_types=1);

namespace J7\Powerhouse\Domains\Product\Model;

use J7\WpUtils\Classes\DTO;

/** Antd 的 option DTO */
class AntdOption extends DTO {
	/** @var string $value */
	public string $value;
	/** @var string $label */
	public string $label;
	/** @var string $color */
	public string $color;
}
