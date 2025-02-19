<?php

use J7\Powerhouse\Utils\Base;

$plugin_links = Base::get_plugin_links();
?>

<div class="bg-[#333333] tw-fixed top-0 left-0 w-full h-8 z-10 tw-hidden md:flex justify-between items-center px-4">
	<div class="flex gap-2 items-center">
		<a href="<?php echo admin_url(); ?>" class="flex gap-2 items-center no-underline">
			<svg class="size-6 fill-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13.9195 8.48006H8.76945L9.09945 8.15006C9.38945 7.86006 9.38945 7.38006 9.09945 7.09006C8.80945 6.80006 8.32945 6.80006 8.03945 7.09006L6.46945 8.66006C6.17945 8.95006 6.17945 9.43006 6.46945 9.72006L8.03945 11.2901C8.18945 11.4401 8.37945 11.5101 8.56945 11.5101C8.75945 11.5101 8.94945 11.4401 9.09945 11.2901C9.38945 11.0001 9.38945 10.5201 9.09945 10.2301L8.83945 9.97006H13.9195C15.1995 9.97006 16.2495 11.0101 16.2495 12.3001C16.2495 13.5901 15.2094 14.6301 13.9195 14.6301H8.99945C8.58945 14.6301 8.24945 14.9701 8.24945 15.3801C8.24945 15.7901 8.58945 16.1301 8.99945 16.1301H13.9195C16.0295 16.1301 17.7495 14.4101 17.7495 12.3001C17.7495 10.1901 16.0295 8.48006 13.9195 8.48006Z"></path></svg>
			<span class="text-white text-sm">回網站後台</span>
		</a>
	</div>
	<div class="flex gap-x-2 items-center">

		<div class="flex items-center gap-x-2">
			<?php
			foreach ( $plugin_links as $plugin_link ) {
				if ($plugin_link['disabled']) {
					printf(
						/*html*/'<span class="pc-btn pc-btn-xs no-underline text-xs pc-btn-outline border-solid border-gray-500 text-gray-500">%1$s</span>',
						$plugin_link['label'],
					);
					continue;
				}
				printf(
				/*html*/'<a href="%1$s" class="pc-btn pc-btn-xs no-underline text-xs pc-btn-outline border-solid border-[#f07f51] %3$s">%2$s</a>',
				$plugin_link['url'],
				$plugin_link['label'],
				$plugin_link['current'] ? 'bg-[#f07f51] text-white' : 'text-[#f07f51]',
				);
			}
			?>
		</div>

		<!-- <span class="text-xs text-white font-bold">$ 100,000</span>
		<img class="size-5 rounded-full" src="https://img.daisyui.com/images/stock/photo-1534528741775-53994a69daeb.webp" /> -->
	</div>
</div>
