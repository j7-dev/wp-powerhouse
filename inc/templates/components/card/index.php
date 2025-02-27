<?php
/**
 * 顯示子文章的卡片
 */

/** @var array{post: WP_Post|null, post_type: string} $args */
@[
	'post'      => $the_post,
	'post_type' => $the_post_type,
] = $args;

if (!$the_post) {
	echo '找不到 $post';
	return;
}

$children_posts = get_posts(
	[
		'post_type'      => $the_post_type ? $the_post_type : $the_post->post_type,
		'post_parent'    => $the_post->ID,
		'posts_per_page' => -1,
		'orderby'        => [
			'menu_order' => 'ASC',
			'ID'         => 'ASC',
			'date'       => 'ASC',
		],
	]
	);

printf(
/*html*/'
<div class="pc-card bg-base-100 w-full shadow-xl relative">
	<div class="pc-card-body p-6 md:p-8">
		<h2 class="pc-card-title font-black text-base-content text-base md:text-xl">%1$s</h2>
		<ul class="pl-6 mb-8">
',
$the_post->post_title,
);

foreach ($children_posts as $child_post) {
	printf(
	/*html*/'
			<a class="text-base-content/75 hover:text-primary text-sm md:text-base" href="%1$s" style="text-decoration: none;">
				<li>%2$s</li>
			</a>
',
	get_permalink($child_post),
	$child_post->post_title,
	);
}

printf(
/*html*/'
		</ul>
		<div class="pc-card-actions justify-start absolute bottom-4 md:bottom-6">
			<a class="text-primary/75 hover:text-primary flex items-center gap-1 text-sm md:text-base" style="text-decoration: none;" href="%1$s">查看更多
				<svg class="w-[1.25em] h-[1.25em] stroke-current" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g stroke-width="0"></g><g stroke-linecap="round" stroke-linejoin="round"></g><g> <path d="M5 12H19M19 12L13 6M19 12L13 18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
			</a>
		</div>
	</div>
</div>
',
get_permalink($the_post),
);
