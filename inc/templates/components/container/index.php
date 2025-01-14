<?php
/**
 * Container 容器
 */

use J7\Powerhouse\Plugin;
use J7\WpUtils\Classes\General;


/** @var array<array{slug: string, label: string, icon: string, content: string}> $args */
$menu_items = $args;

$module_name    = @$_GET['module'] ?? ''; // phpcs:ignore
/** @var array{slug: string, label: string, icon: string, content: string} $menu_item */
$menu_item = General::array_find($menu_items, fn( $menu_item ) => $menu_item['slug'] === $module_name);

echo /*html*/'<div id="powerhouse-container">';
echo $menu_item['content'] ?? ''; // @phpstan-ignore-line
echo /*html*/'</div>';
