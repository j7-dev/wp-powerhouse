import { defineConfig } from 'vite'
import tsconfigPaths from 'vite-tsconfig-paths'
import alias from '@rollup/plugin-alias'
import path from 'path'
import liveReload from 'vite-plugin-live-reload'
import optimizer from 'vite-plugin-optimizer'
import { terser } from 'rollup-plugin-terser'

export default defineConfig({
	build: {
		emptyOutDir: true,
		minify: true,
		outDir: path.resolve(__dirname, 'inc/assets/dist'),
		watch: {
			include: ['./inc/**/*.php', './inc/**/*.ts', './inc/**/*.tsx', './js/src/**/*.tsx',
				'./js/src/**/*.ts',],
			exclude:
				'js/**, modules/**, node_modules/**, release/**, vendor/**, .git/**, .vscode/**',
		},
		rollupOptions: {
			input: {
				admin: 'inc/assets/src/admin.ts',
				frontend: 'inc/assets/src/frontend.ts',
			},
			output: {
				entryFileNames: '[name].js',
				assetFileNames: '[ext]/index.[ext]'
			},
		},
	},
	plugins: [
		alias(),
		tsconfigPaths(),
		liveReload([
			__dirname + '/**/*.php',
		]),
		optimizer({
			jquery: 'const $ = window.jQuery; export { $ as default }',
		}),
		terser({
			mangle: {
				reserved: ['$'], // 指定 $ 不被改變
			},
		}),
	],
	resolve: {
		alias: {
			'@': path.resolve(__dirname, 'inc/assets/src'),
		},
	},
})
