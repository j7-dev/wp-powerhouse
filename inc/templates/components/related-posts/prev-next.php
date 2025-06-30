<?php
/**
 * 顯示樹狀結構的上一篇、下一篇文章 (不是扁平的上一篇下一篇)
 */

use J7\Powerhouse\Domains\Post\Utils\CRUD as PostCRUD;

global $post;


$top_parent_id = PostCRUD::get_top_post_id($post->ID);

$all_children_ids = PostCRUD::get_flatten_post_ids( $top_parent_id);

// find index of current post id
/** @var int|false $current_post_index */
$current_post_index = array_search($post->ID, $all_children_ids, true);

if (false === $current_post_index) {
	// 此文章 id 不在列表中
	return;
}

$class_name   = 'group w-full rounded-box border border-solid border-base-content/30 p-2 md:p-4 flex items-center gap-x-2 md:gap-x-4 relative';
$prev_post_id = $all_children_ids[ $current_post_index - 1 ] ?? null;
/** @var WP_Post|null $prev_post */
$prev_post    = $prev_post_id ? get_post($prev_post_id) : null;
$next_post_id = $all_children_ids[ $current_post_index + 1 ] ?? null;
/** @var WP_Post|null $next_post */
$next_post = $next_post_id ? get_post($next_post_id) : null;


echo '<div class="flex gap-x-2 md:gap-x-4">';

if ($prev_post) {
	printf(
	/*html*/'
	<a href="%1$s" class="pc-prev-post %2$s" style="text-decoration: none;">
		<svg class="size-4 md:size-6 stroke-base-content group-hover:stroke-primary" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g stroke-width="0"></g><g stroke-linecap="round" stroke-linejoin="round"></g><g> <path d="M4 12H20M4 12L8 8M4 12L8 16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
		<div class="flex-1 pt-4 md:pt-6">
			<p class="m-0 text-sm md:text-base text-base-content group-hover:text-primary">%3$s</p>
		</div>
		<p class="m-0 text-xs md:text-sm text-base-content/50 absolute top-2 md:top-4 left-8 md:left-14">上一個</p>
	</a>
	',
	get_the_permalink($prev_post->ID),
	$class_name,
	$prev_post->post_title,
	);
}

if ($next_post) {
	printf(
	/*html*/'
	<a href="%1$s" class="pc-next-post %2$s" style="text-decoration: none;">
		<div class="text-right flex-1 pt-4 md:pt-6">
			<p class="m-0 text-sm md:text-base text-base-content group-hover:text-primary">%3$s</p>
		</div>
		<svg class="size-4 md:size-6 stroke-base-content group-hover:stroke-primary" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g stroke-width="0"></g><g stroke-linecap="round" stroke-linejoin="round"></g><g> <path d="M4 12H20M20 12L16 8M20 12L16 16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
		<p class="m-0 text-xs md:text-sm text-base-content/50 absolute top-2 md:top-4 right-8 md:right-14">下一個</p>
	</a>
	',
	get_the_permalink($next_post->ID),
	$class_name,
	$next_post->post_title,
	);
}


echo '</div>';
