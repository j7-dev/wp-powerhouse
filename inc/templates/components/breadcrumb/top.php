<?php
/**
 * 如果是最上層的文章專用的麵包屑
 * 因為只需要一層，簡單處理就好
 */

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

printf(
/*html*/'
<div class="pc-breadcrumbs %1$s">
	<ul class="pl-0 gap-y-1 flex-wrap">
		<li>
			<a class="text-base-content/75 hover:text-primary flex" style="text-decoration: none;" href="%2$s">
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="h-4 w-4 stroke-current mr-1">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
				</svg>%3$s
			</a>
		</li>
	</ul>
</div>
',
$class,
get_permalink($the_post->ID),
$the_post->post_title,
);
