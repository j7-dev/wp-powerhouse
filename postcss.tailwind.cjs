/**
 * Tailwind CLI 專用的 PostCSS 設定（用 `--postcss ./postcss.tailwind.cjs` 指定）
 *
 * ⚠ **不要跟 `postcss.config.cjs` 搞混**：
 * 實測 Tailwind CLI **不會**自動讀取 `postcss.config.cjs`（在裡面放一個會印字的
 * probe plugin，跑 `npx tailwindcss ...` 完全沒有輸出）。那份設定只有在有人直接
 * 呼叫 postcss（例如 Vite 的 CSS pipeline）時才生效。
 * CSS 建置（`build-css:admin` / `build-css:front` / `watch-css:*`）走的是 Tailwind
 * CLI，因此必須用 `--postcss` 旗標明確指定本檔，額外的 PostCSS 步驟才會執行。
 *
 * 這裡只放「Tailwind 產出之後」要跑的處理：
 *   - `postcss-scope-daisyui`：把 daisyUI 的選擇器收進 `#tw`，讓它不再被
 *     WordPress 佈景主題以「同 specificity、後載入」的方式蓋掉（原因詳見該檔）
 *   - `autoprefixer`：瀏覽器前綴
 *
 * Tailwind 自己的編譯由 CLI 負責，**不要**在這裡再 `require('tailwindcss')`，
 * 否則會跑兩次。
 */

// eslint-disable-next-line no-undef
module.exports = {
	plugins: [
		require('./scripts/postcss-scope-daisyui.cjs')(),
		require('autoprefixer'),
	],
}
