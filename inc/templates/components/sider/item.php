<?php
/**
 * Sider 側欄 item
 */

$default_args = [
	'slug'  => '',
	'url'   => '', // 連結
	'label' => '', // 標題
	'icon'  => '', // 圖示
];

/**
 * @var array{slug: string, url: string, label: string, icon: string} $args
 * @phpstan-ignore-next-line
 */
$args = wp_parse_args( $args, $default_args );

[
	'slug'  => $slug,
	'url'   => $url,
	'label' => $label,
	'icon'  => $icon,
] = $args;

$selected = @$_GET['module'] === $slug; // phpcs:ignore

printf(
/*html*/'
	<li class="pl-6 pr-4 overflow-hidden text-ellipsis whitespace-nowrap cursor-pointer relative flex items-center h-10 text-sm transition duration-300 ease-in-out rounded-lg %4$s %5$s" role="menuitem" style="width: calc(100%% - 0.5rem);">
		<span role="img" aria-label="table" class="flex-none h-full inline-flex items-center text-inherit min-w-[0.875rem] normal-case text-center" style="
		font-style: normal;
		line-height: 0;
		vertical-align: -0.125em;
    text-rendering: optimizeLegibility;
    -webkit-font-smoothing: antialiased;">
			%1$s
		</span>
		<span class="flex-auto h-full m-w-0 overflow-hidden text-ellipsis whitespace-nowrap inline-flex items-center ms-[0.625rem]">
			<a href="%2$s" class="flex-1 text-inherit bg-transparent outline-none cursor-pointer touch-manipulation" style="text-decoration: none;line-height: 39px;">
				%3$s
			</a>
			<div class="ant-menu-tree-arrow"></div>
		</span>
	</li>
',
	$icon,
	$url,
	$label,
	'wp-admin' === $slug ? 'mx-1 mt-1 mb-8' : 'm-1',
	$selected ? 'bg-[#e6f4ff] text-[#1677ff] [&_svg]:fill-[#1677ff]' : 'bg-white hover:bg-[#f5f5f5] text-[rgba(0,0,0,0.88)]',
);
