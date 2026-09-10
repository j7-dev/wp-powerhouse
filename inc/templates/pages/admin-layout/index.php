<?php
/** @var array{title: string, id: string} $args */

use J7\Powerhouse\Plugin;

@[
	'title' => $app_title,
	'id' => $app_id,
] = $args;

?>
<!doctype html>
		<html <?php language_attributes(); ?>>

		<head>
			<link rel="stylesheet" href="<?php echo Plugin::$url; ?>/js/dist/css/admin.min.css?ver=<?php echo Plugin::$version; ?>" /><?php //phpcs:ignore ?>
			<link rel="stylesheet" href="<?php echo Plugin::$url; ?>/js/dist/css/style.css?ver=<?php echo Plugin::$version; ?>" /><?php //phpcs:ignore ?>

			<meta charset="UTF-8" />
			<meta name="viewport" content="width=device-width, initial-scale=1.0" />
			<title><?php echo $app_title; ?></title>
		</head>

			<?php
			/*
			 * `id="tw"` 是 Tailwind utility 生效的前提：`tailwind.config.cjs` 設定
			 * `important: '#tw'`，所有 utility 都輸出成 `#tw .flex { ... }`。
			 *
			 * 前台由 `Theme\Core\FrontEnd::add_html_attr()` 把 `id="tw"` 加在 `<html>` 上，
			 * 但後台一直沒有對應的處理——admin.min.css 內約 900 條 utility 因此從未生效，
			 * 各 power-* 外掛只能各自打包一份無 scope 的 Tailwind 才有樣式。
			 * 補上這個 id 之後，powerhouse 的 admin CSS 才真的能被子外掛共用。
			 */
			?>
		<body id="tw" class="md:pt-8">
			<?php Plugin::load_template('admin-layout/bar'); ?>
			<main id="<?php echo $app_id; ?>"></main>
		<?php
		/**
		 * Prints any scripts and data queued for the footer.
		 *
		 * @since 2.8.0
		 */
		\do_action('admin_print_footer_scripts');

		?>
		</body>

		</html>
