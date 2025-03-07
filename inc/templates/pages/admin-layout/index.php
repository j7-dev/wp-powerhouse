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

		<body class="md:pt-8">
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
