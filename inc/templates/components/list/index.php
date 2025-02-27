<?php
/**
 * 單一文章列表版型
 * 顯示
 * - 麵包屑
 * - 文章標題
 * - 內文前100字
 *
 * 可用於文章搜尋結果
 */

use J7\Powerhouse\Plugin;

global $post;

/** @var array{post: WP_Post|null} $args */
@[
	'post'    => $the_post,
] = $args;

$the_post = $the_post ?? $post;

if (!( $the_post instanceof \WP_Post )) {
	echo '$the_post 不是 WP_Post 實例';
	return;
}

$breadcrumb = Plugin::load_template(
	'breadcrumb',
	[
		'post'  => $the_post,
		'class' => 'text-sm mb-0',
	],
	false
);

printf(
/*html*/'
<div>
	<a href="%1$s" class="text-base-content hover:text-primary">
		<h2 class="text-lg font-bold mb-0">%2$s</h2>
	</a>
	%3$s
	<p class="text-sm md:text-base text-base-content/75">%4$s</p>
	<div class="pc-divider"></div>
</div>
',
get_the_permalink($the_post->ID),
$the_post->post_title,
$breadcrumb,
substr(\wp_strip_all_tags($the_post->post_content), 0, 100)
);
