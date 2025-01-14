<?php
/**
 * Sider 側欄
 */

use J7\Powerhouse\Plugin;


/** @var array<array{slug: string, label: string, icon: string}> $args */
$menu_items = $args;

echo /*html*/'<div id="powerhouse-sider" class="tw-fixed left-0 top-0 w-52 bg-white h-screen">';
echo /*html*/'<ul class="pt-2 px-0 border-none overflow-auto h-[calc(100%-72px)] list-none transition duration-300 ease-in-out outline-none" role="menu">';

Plugin::get(
	'sider/item',
	[
		'slug'  => 'wp-admin',
		'url'   => \admin_url(),
		'label' => '回網站後台',
		'icon'  => /*html*/'<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M4 10L3.29289 10.7071L2.58579 10L3.29289 9.29289L4 10ZM21 18C21 18.5523 20.5523 19 20 19C19.4477 19 19 18.5523 19 18L21 18ZM8.29289 15.7071L3.29289 10.7071L4.70711 9.29289L9.70711 14.2929L8.29289 15.7071ZM3.29289 9.29289L8.29289 4.29289L9.70711 5.70711L4.70711 10.7071L3.29289 9.29289ZM4 9L14 9L14 11L4 11L4 9ZM21 16L21 18L19 18L19 16L21 16ZM14 9C17.866 9 21 12.134 21 16L19 16C19 13.2386 16.7614 11 14 11L14 9Z" fill="#33363F"></path> </g></svg>',
	]
	);

foreach ( $menu_items as $menu_item ) {
	Plugin::get('sider/item', $menu_item);
}

echo /*html*/'</ul>';
echo /*html*/'</div>';
