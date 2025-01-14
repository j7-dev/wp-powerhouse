<?php
/**
 * Admin Entry
 * 如果要做統一後台才需要，目前為各個外掛各自後台
 */

declare(strict_types=1);

namespace J7\Powerhouse\Admin;

use J7\Powerhouse\Plugin;
use J7\Powerhouse\Bootstrap;


/**
 * Class Entry
 */
final class Entry {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Add the admin page for full-screen.
		\add_action('current_screen', [ __CLASS__, 'maybe_output_admin_page' ], 10);
	}

	/**
	 * Output the dashboard admin page.
	 */
	public static function maybe_output_admin_page(): void {
		// Exit if not in admin.
		if (!\is_admin()) {
			return;
		}

		// Make sure we're on the right screen.
		$screen = \get_current_screen();

		if ('toplevel_page_' . Plugin::$kebab !== $screen?->id) {
			return;
		}

		self::render_page();

		exit;
	}

	/**
	 * Output landing page header.
	 *
	 * Credit: SliceWP Setup Wizard.
	 */
	public static function render_page(): void {
		\do_action('powerhouse_before_render_page');
		$blog_name = \get_bloginfo('name');
		?>
		<!doctype html>
		<html lang="zh_tw">

		<head>
			<link rel="stylesheet" href="<?php echo Plugin::$url; ?>/inc/assets/dist/css/index.css?ver=<?php echo Plugin::$version; ?>" /><?php //phpcs:ignore ?>
			<meta charset="UTF-8" />
			<meta name="viewport" content="width=device-width, initial-scale=1.0" />
			<title>Powerhouse 後台 | <?php echo $blog_name; ?></title>
		<?php \do_action('powerhouse_admin_head'); ?>
		</head>

		<body class="tailwind" style="background-color: #f5f5f5;">

		<?php
		Plugin::get('powerhouse');
		/**
		 * Prints any scripts and data queued for the footer.
		 *
		 * @since 2.8.0
		 */
		\do_action('admin_print_footer_scripts');
		\do_action('powerhouse_admin_footer');
		?>
		</body>

		</html>
		<?php
		\do_action('powerhouse_after_render_page');
	}
}
