<?php
/**
 * 舊版 class
 */

declare (strict_types = 1);

namespace J7\Powerhouse;

require_once __DIR__ . '/Utils.php';


if ( class_exists( 'J7\Powerhouse\LC' ) ) {
	return;
}
/**
 * Class LC
 */
final class LC extends Domains\LC\Utils {

}
