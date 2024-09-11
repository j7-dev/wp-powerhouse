<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
      important: '.tailwind',
			corePlugins: {
				preflight: false,
				container: false,
			},
			theme: {
				animation: {
					pulse: 'tw-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
				},
				extend: {
					screens: {
						sm: '576px', // iphone SE
						md: '810px', // ipad Portrait
						lg: '1080px', // ipad Landscape
						xl: '1280px', // mac air
						xxl: '1440px',
					},
					keyframes: {
						'tw-pulse': {
							'50%': { opacity: '0.5' },
						},
					},
				},
			},
			plugins: [
				function ({ addUtilities }) {
					const newUtilities = {
						'.rtl': {
							direction: 'rtl',
						},

						// 與 WordPress 衝突的 class
						'.tw-hidden': {
							display: 'none',
						},
						'.tw-columns-1': {
							columnCount: 1,
						},
						'.tw-columns-2': {
							columnCount: 2,
						},
						'.tw-fixed': {
							position: 'fixed',
						},
						'.tw-inline': {
							display: 'inline'
						}
					}
					addUtilities(newUtilities, ['responsive', 'hover'])
				},
			],
			safelist: [],
			blocklist: ['hidden', 'columns-1', 'columns-2', 'fixed', 'inline'],
    }
  </script>
