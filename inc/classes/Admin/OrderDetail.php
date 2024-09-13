<?php
/**
 * Order 訂單備註轉為傳統編輯器
 * BUG: 編輯器無法顯示
 */

declare(strict_types=1);

namespace J7\Powerhouse\Admin;

use J7\WpUtils\Classes\WC;

if ( class_exists( 'J7\Powerhouse\Admin\OrderDetail' ) ) {
	return;
}
/**
 * Class Order
 */
final class OrderDetail {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action( 'admin_head', [ __CLASS__, 'remove_origin_order_note' ], 100 );
		\add_action( 'admin_head', [ __CLASS__, 'add_custom_order_note' ], 110 );
	}


	/**
	 * Remove origin order note meta box
	 */
	public static function remove_origin_order_note() {
		$screen = \get_current_screen();
		if ('shop_order' !== $screen->id) {
			return;
		}
		\remove_meta_box( 'woocommerce-order-notes', 'woocommerce_page_wc-orders', 'side' );
	}

	/**
	 * Add custom order note
	 */
	public static function add_custom_order_note() {
		$screen = \get_current_screen();
		if ('shop_order' !== $screen->id) {
			return;
		}

		$metabox_screen = WC::is_hpos_enabled() ? 'woocommerce_page_wc-orders' : 'shop_order';

		\add_meta_box('woocommerce-order-notes', sprintf(__('%s notes', 'woocommerce'), __('Order', 'woocommerce')), [__CLASS__, 'output'], $metabox_screen, 'advanced', 'default'); // phpcs:ignore
	}

	/**
	 * Output custom order note
	 *
	 * @param \WP_Post $post Order object
	 * @return void
	 */
	public static function output( $post ) {
		echo '<div class="ph">';

		$order_id = $post->ID;

		$args = [ 'order_id' => $order_id ];

		$notes = [];
		if ( 0 !== $order_id ) {
			$notes = \wc_get_order_notes( $args );
		}
		$woocommerce_path = \untrailingslashit( \WC()->plugin_path() );

		include $woocommerce_path . '/includes/admin/meta-boxes/views/html-order-notes.php';
		?>
		<div class="bg-gray-100 text-center py-8" id="add_note_editor_loading">
		<div type="button" class="inline-flex items-center font-semibold leading-6 text-sm">
	<svg class="animate-spin -ml-1 mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
		<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
		<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
	</svg>
	編輯器載入中...
	</div>

		</div>

		<div class="add_note" id="add_note_editor_init" style="display:none;">
			<p>
				<label for="add_order_note"><?php esc_html_e( 'Add note', 'woocommerce' ); ?> <?php echo wc_help_tip( __( 'Add a note for your reference, or add a customer note (the user will be notified).', 'woocommerce' ) ); //phpcs:ignore ?></label>

		<?php

		$args = [
			'textarea_rows' => 10,
					// 'tinymce'       => array(
					// 'toolbar1'      => 'bold,italic,underline,separator,alignleft,aligncenter,alignright,separator,link,unlink,undo,redo',
					// 'toolbar2'      => '',
					// 'toolbar3'      => '',
					// ),
		];
		\wp_editor( '', 'add_order_note_classic_editor', $args );
		?>
				<textarea type="text" name="order_note" id="add_order_note" class="input-text" cols="20" rows="5" style="display: none;"></textarea>
			</p>
			<p>
				<label for="order_note_type" class="screen-reader-text"><?php esc_html_e( 'Note type', 'woocommerce' ); ?></label>
				<select name="order_note_type" id="order_note_type">
					<option value=""><?php esc_html_e( 'Private note', 'woocommerce' ); ?></option>
					<option value="customer"><?php esc_html_e( 'Note to customer', 'woocommerce' ); ?></option>
				</select>
				<button type="button" class="add_note button"><?php esc_html_e( 'Add', 'woocommerce' ); ?></button>
			</p>
		</div>

		<script>
			(function($) {
				$(document).ready(function() {
					if (typeof tinyMCE !== 'undefined') {
						tinyMCE.init({
							selector: '#add_order_note_classic_editor', // 替換為您的編輯器ID
						});
						$(document).on('tinymce-editor-init', function(e) {
							const editor = tinyMCE.activeEditor;
							$('#add_note_editor_loading').hide();
							$('#add_note_editor_init').show();
							editor.on('change', function(e) {
									$('#add_order_note').val(editor.getContent());
								});
						});
					}
				});
			})(jQuery);
		</script>

		<style>
			.order_notes .note_content img{
				max-width: 100% !important;
				object-fit: contain;
			}
		</style>
		<?php
		echo '</div>';
	}
}
