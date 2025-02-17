<?php
/**
 * Admin Entry
 * 如果要做統一後台才需要，目前為各個外掛各自後台
 */

declare(strict_types=1);

namespace J7\Powerhouse\Admin;

use J7\Powerhouse\Plugin;
use J7\Powerhouse\Bootstrap;
use J7\Powerhouse\Utils\Base;

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

		if ( 'powerhouse_page_powerhouse-settings' !== $screen?->id) {
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
		Bootstrap::enqueue_admin_assets();
		$id        = substr(Base::APP1_SELECTOR, 1);
		$blog_name = \get_bloginfo('name');
		?>
		<!doctype html>
		<html <?php language_attributes(); ?>>

		<head>
			<link rel="stylesheet" href="<?php echo Plugin::$url; ?>/js/dist/css/admin.min.css?ver=<?php echo Plugin::$version; ?>" /><?php //phpcs:ignore ?>
			<meta charset="UTF-8" />
			<meta name="viewport" content="width=device-width, initial-scale=1.0" />
			<title>Powerhouse 後台 | <?php echo $blog_name; ?></title>
		</head>

		<body>
			<main id="<?php echo $id; ?>"></main>
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
		<?php
	}
}
