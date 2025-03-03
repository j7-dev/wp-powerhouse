<?php
/**
 * 麵包屑
 */

use J7\Powerhouse\Plugin;

global $post;

/** @var array{post: WP_Post|null, class: string|null} $args */
@[
	'post'    => $the_post,
	'class'   => $class,
] = $args;

$the_post = $the_post ?? $post;
$class    = $class ?? 'text-sm mb-8';

if (!( $the_post instanceof \WP_Post )) {
	echo '$the_post 不是 WP_Post 實例';
	return;
}

if (!$the_post->post_parent) {
	// 如果沒有父章節，自己就是頂層
	Plugin::load_template('breadcrumb/top');
	return;
}

/** @var array<int> $breadcrumb_post_ids 取得麵包屑的 post id，由上到下 */
$breadcrumb_post_ids = array_reverse(\get_ancestors($the_post->ID, $the_post->post_type, 'post_type'));
$breadcrumb_post_ids = [
	...$breadcrumb_post_ids,
	$the_post->ID,
];

printf(
/*html*/'
<div class="pc-breadcrumbs %1$s">
	<ul class="pl-0 gap-y-1 flex-wrap">
',
$class
);


foreach ($breadcrumb_post_ids as $key => $breadcrumb_post_id) {
	/** @var \WP_Post|null $breadcrumb_post */
	$breadcrumb_post = get_post($breadcrumb_post_id);
	if (!$breadcrumb_post) {
		continue;
	}

	printf(
	/*html*/'<li><a class="text-base-content/75 hover:text-primary flex" style="text-decoration: none;" href="%1$s">%2$s<span class="flex-1 whitespace-normal">%3$s</span></a></li>
	',
	get_the_permalink($breadcrumb_post_id),
	$key === 0 ? /*html*/'
	<svg
	xmlns="http://www.w3.org/2000/svg"
	fill="none"
	viewBox="0 0 24 24"
	class="h-4 w-4 stroke-current mx-1">
		<path
		stroke-linecap="round"
		stroke-linejoin="round"
		stroke-width="2"
		d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
	</svg>' : '',
	$breadcrumb_post->post_title
	);
}

echo /*html*/ '
	</ul>
</div>';
