<?php

use J7\Powerhouse\Plugin;

$themes = [
	'custom',
	'light',
	'dark',
	'cupcake',
	'bumblebee',
	'emerald',
	'corporate',
	'synthwave',
	'retro',
	'cyberpunk',
	'valentine',
	'halloween',
	'garden',
	'forest',
	'aqua',
	'lofi',
	'pastel',
	'fantasy',
	'wireframe',
	'black',
	'luxury',
	'dracula',
	'cmyk',
	'autumn',
	'business',
	'night',
	'winter',
	'dim',
	'nord',
	'sunset',
];
?>
<div id="pc-theme-changer" class="tw-fixed bottom-8 right-8 z-30 pc-dropdown pc-dropdown-top pc-dropdown-end">

	<div tabindex="0" class="pc-menu pc-dropdown-content bg-base-200 text-base-content rounded-box h-[28.6rem] max-h-[calc(100vh-10rem)] w-56 overflow-y-auto border border-white/5 shadow-2xl outline outline-1 outline-black/5 mt-16">
		<div class="grid grid-cols-1 gap-3 p-3">
			<?php foreach ( $themes as $theme ) : ?>
				<?php Plugin::get('theme/button', [ 'theme' => $theme ]); ?>
			<?php endforeach; ?>
		</div>
	</div>

	<div tabindex="0" role="button" class="mt-2 pc-btn pc-btn-circle shadow-[0_0_0.5rem_rgba(0,0,0,0.2)]">
		<svg class="size-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<g stroke-width="0"></g>
			<g stroke-linecap="round" stroke-linejoin="round"></g>
			<g>
				<path class="fill-accent" d="M22 16.5V19.5C22 21 21 22 19.5 22H6C6.41 22 6.83 21.94 7.22 21.81C7.33 21.77 7.43999 21.73 7.54999 21.68C7.89999 21.54 8.24001 21.34 8.54001 21.08C8.63001 21.01 8.73001 20.92 8.82001 20.83L8.85999 20.79L15.66 14H19.5C21 14 22 15 22 16.5Z"></path>
				<path class="fill-secondary" d="M18.3694 11.29L15.6594 14L8.85938 20.79C9.55937 20.07 9.99939 19.08 9.99939 18V8.33996L12.7094 5.62996C13.7694 4.56996 15.1894 4.56996 16.2494 5.62996L18.3694 7.74996C19.4294 8.80996 19.4294 10.23 18.3694 11.29Z"></path>
				<path class="fill-primary" d="M7.5 2H4.5C3 2 2 3 2 4.5V18C2 18.27 2.02999 18.54 2.07999 18.8C2.10999 18.93 2.13999 19.06 2.17999 19.19C2.22999 19.34 2.28 19.49 2.34 19.63C2.35 19.64 2.35001 19.65 2.35001 19.65C2.36001 19.65 2.36001 19.65 2.35001 19.66C2.49001 19.94 2.65 20.21 2.84 20.46C2.95 20.59 3.06001 20.71 3.17001 20.83C3.28001 20.95 3.4 21.05 3.53 21.15L3.54001 21.16C3.79001 21.35 4.06 21.51 4.34 21.65C4.35 21.64 4.35001 21.64 4.35001 21.65C4.50001 21.72 4.65 21.77 4.81 21.82C4.94 21.86 5.07001 21.89 5.20001 21.92C5.46001 21.97 5.73 22 6 22C6.41 22 6.83 21.94 7.22 21.81C7.33 21.77 7.43999 21.73 7.54999 21.68C7.89999 21.54 8.24001 21.34 8.54001 21.08C8.63001 21.01 8.73001 20.92 8.82001 20.83L8.85999 20.79C9.55999 20.07 10 19.08 10 18V4.5C10 3 9 2 7.5 2ZM6 19.5C5.17 19.5 4.5 18.83 4.5 18C4.5 17.17 5.17 16.5 6 16.5C6.83 16.5 7.5 17.17 7.5 18C7.5 18.83 6.83 19.5 6 19.5Z"></path>
			</g>
		</svg>
	</div>

</div>

<script type="module" async>
	(function($) {
		$(document).ready(function() {

			class ThemeChanger {
				_theme = 'custom';
				$dropdown = null;

				constructor() {
					this.$dropdown = $('#pc-theme-changer');
					this.init();
					this.attachEvent();
				}

				set theme(value) {
					this._theme = value;
					// 修改 html tag attribute data-theme
					$('html').attr('data-theme', this._theme);

					// 儲存到 localStorage
					localStorage.setItem('theme', this._theme);
				}

				get theme() {
					return this._theme;
				}

				init() {
					// 從 localStorage 取得 theme
					const theme = localStorage.getItem('theme');
					if(!theme) {
						return;
					}
					this.theme = localStorage.getItem('theme');
				}

				isDropdownOpen() {
					const content = this.$dropdown[0].querySelector('.pc-dropdown-content');
					return window.getComputedStyle(content).opacity !== '0';
				}


				attachEvent() {
					this.$dropdown.on('click', 'button[data-set-theme]', (e) => {
						const theme = $(e.currentTarget).data('set-theme');
						this.theme = theme;
					});
				}
			}

			new ThemeChanger();
		});
	})(jQuery);
</script>
