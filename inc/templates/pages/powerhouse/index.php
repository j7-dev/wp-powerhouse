<?php
/**
 * 後台入口
 */

use J7\Powerhouse\Plugin;
use J7\Powerhouse\Utils\Base;

$menu_items = [
	[
		'slug'    => 'courses',
		'label'   => '課程列表',
		'url'     => Base::get_module_url('courses'),
		'icon'    => /*html*/'<svg viewBox="64 64 896 896" focusable="false" data-icon="table" width="1em" height="1em" fill="currentColor" aria-hidden="true"><path d="M928 160H96c-17.7 0-32 14.3-32 32v640c0 17.7 14.3 32 32 32h832c17.7 0 32-14.3 32-32V192c0-17.7-14.3-32-32-32zm-40 208H676V232h212v136zm0 224H676V432h212v160zM412 432h200v160H412V432zm200-64H412V232h200v136zm-476 64h212v160H136V432zm0-200h212v136H136V232zm0 424h212v136H136V656zm276 0h200v136H412V656h212v136z"></path></svg>',
		'content' => /*html*/'課程列表',
	],
	[
		'slug'    => 'users',
		'url'     => Base::get_module_url('users'),
		'label'   => '講師管理',
		'icon'    => /*html*/'<svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 256 256" class="ant-menu-item-icon" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><path d="M226.53,56.41l-96-32a8,8,0,0,0-5.06,0l-96,32A8,8,0,0,0,24,64v80a8,8,0,0,0,16,0V75.1L73.59,86.29a64,64,0,0,0,20.65,88.05c-18,7.06-33.56,19.83-44.94,37.29a8,8,0,1,0,13.4,8.74C77.77,197.25,101.57,184,128,184s50.23,13.25,65.3,36.37a8,8,0,0,0,13.4-8.74c-11.38-17.46-27-30.23-44.94-37.29a64,64,0,0,0,20.65-88l44.12-14.7a8,8,0,0,0,0-15.18ZM176,120A48,48,0,1,1,89.35,91.55l36.12,12a8,8,0,0,0,5.06,0l36.12-12A47.89,47.89,0,0,1,176,120ZM128,87.57,57.3,64,128,40.43,198.7,64Z"></path></svg>',
		'content' => /*html*/'講師管理',
	],
];
/** @var array<array{slug: string, label: string, icon: string, content: string}> $items */
$menu_items = apply_filters('powerhouse_menu_items', $menu_items);

echo /*html*/'<main class="pl-52 w-screen h-screen">';
Plugin::get('sider', $menu_items, true, true);
Plugin::get('container', $menu_items, true, true);
echo /*html*/'</main>';
