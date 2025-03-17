<?php

declare (strict_types = 1);

namespace J7\Powerhouse\Domains\Post;

require_once __DIR__ . '/CRUD.php';


if ( class_exists( 'J7\Powerhouse\Domains\Post\Utils' ) ) {
	return;
}
/**
 * 舊版 class
 */
final class Utils extends Utils\CRUD {

}
