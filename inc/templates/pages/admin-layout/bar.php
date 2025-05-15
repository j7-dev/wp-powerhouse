<?php

use J7\Powerhouse\Utils\Base;

$plugin_links = Base::get_plugin_links();
?>

<div class="bg-[#444444] tw-fixed top-0 left-0 w-full h-8 z-20 tw-hidden md:flex justify-between items-center px-5">
	<div class="flex gap-2 items-center">
		<a href="<?php echo admin_url(); ?>" class="flex gap-2 items-center no-underline">
			<svg class="size-6 fill-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13.9195 8.48006H8.76945L9.09945 8.15006C9.38945 7.86006 9.38945 7.38006 9.09945 7.09006C8.80945 6.80006 8.32945 6.80006 8.03945 7.09006L6.46945 8.66006C6.17945 8.95006 6.17945 9.43006 6.46945 9.72006L8.03945 11.2901C8.18945 11.4401 8.37945 11.5101 8.56945 11.5101C8.75945 11.5101 8.94945 11.4401 9.09945 11.2901C9.38945 11.0001 9.38945 10.5201 9.09945 10.2301L8.83945 9.97006H13.9195C15.1995 9.97006 16.2495 11.0101 16.2495 12.3001C16.2495 13.5901 15.2094 14.6301 13.9195 14.6301H8.99945C8.58945 14.6301 8.24945 14.9701 8.24945 15.3801C8.24945 15.7901 8.58945 16.1301 8.99945 16.1301H13.9195C16.0295 16.1301 17.7495 14.4101 17.7495 12.3001C17.7495 10.1901 16.0295 8.48006 13.9195 8.48006Z"></path></svg>
			<span class="text-white text-sm">網站後台</span>
		</a>
		<span class="text-white text-sm">|</span>
		<a href="<?php echo site_url(); ?>" class="flex gap-2 items-center no-underline">
			<span class="text-white text-sm">網站前台</span>
		</a>
	</div>
	<div class="flex gap-x-2 items-center">
		<div class="flex items-center gap-x-5">
			<?php
			foreach ( $plugin_links as $plugin_link ) {
				if ($plugin_link['disabled']) {
					continue;
				}
				if ('Powerhouse' === $plugin_link['label']) {
					$plugin_link['label'] = '主控台';
				}
				printf(
				/*html*/'<a href="%1$s" class="text-xs text-white %3$s">%2$s</a>',
				$plugin_link['url'],
				$plugin_link['label'],
				$plugin_link['current'] ? 'pc-btn pc-btn-xs bg-[#1677ff] !rounded-md no-underline pc-btn-outline' : '',
				);
			}
			?>
		</div>

		<!-- <span class="text-xs text-white font-bold">$ 100,000</span>
		<img class="size-5 rounded-full" src="https://img.daisyui.com/images/stock/photo-1534528741775-53994a69daeb.webp" /> -->
	</div>
</div>
