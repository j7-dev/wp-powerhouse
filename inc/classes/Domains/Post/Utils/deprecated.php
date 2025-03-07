<?php
/**
 * 舊版 class
 */

declare (strict_types = 1);

namespace J7\Powerhouse\Domains\Post;

require_once __DIR__ . '/CRUD.php';


if ( class_exists( 'J7\Powerhouse\Domains\Post\Utils' ) ) {
	return;
}
/**
 * Class LC
 */
final class Utils extends Utils\CRUD {

}
