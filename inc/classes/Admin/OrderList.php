<?php
/**
 * Order-List
 */

declare(strict_types=1);

namespace J7\Powerhouse\Admin;

use J7\WpUtils\Classes\WC;

if ( class_exists( 'J7\Powerhouse\Admin\OrderList' ) ) {
	return;
}
/**
 * Class OrderList
 */
final class OrderList {
	use \J7\WpUtils\Traits\SingletonTrait;

	const PRODUCT_COLUMN_NAME = 'elittleworld_extension_order_products';

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( WC::is_hpos_enabled() ) {
			\add_filter( 'manage_woocommerce_page_wc-orders_columns', [ __CLASS__, 'add_order_column' ], 20, 1 );
			\add_action( 'manage_woocommerce_page_wc-orders_custom_column', [ __CLASS__, 'render_order_column_hpos' ], 20, 2 );
		} else {
			\add_filter( 'manage_edit-shop_order_columns', [ __CLASS__, 'add_order_column' ], 20, 1 );
			\add_action( 'manage_shop_order_posts_custom_column', [ __CLASS__, 'render_order_column_shop_order' ], 20, 1 );
		}
	}

	/**
	 * Add order column.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function add_order_column( array $columns ): array {
		$columns[ self::PRODUCT_COLUMN_NAME ] = '訂單商品';
		return $columns;
	}

	/**
	 * Render order column.
	 *
	 * @param string                                        $column Column.
	 * @param \Automattic\WooCommerce\Admin\Overrides\Order $order Post ID.
	 * @return void
	 */
	public static function render_order_column_hpos( $column, $order ): void {
		$order_id = (int) $order?->get_id();
		self::render_order_column( $column, $order_id );
	}

	/**
	 * Render order column.
	 *
	 * @param string $column Column.
	 * @return void
	 */
	public static function render_order_column_shop_order( $column ): void {
		global $post;
		$order_id = (int) $post?->ID;
		self::render_order_column( $column, $order_id );
	}

	/**
	 * Render order column.
	 *
	 * @param string $column Column.
	 * @param int    $order_id Order ID.
	 * @return void
	 */
	public static function render_order_column( $column, int $order_id ): void {
		if ( self::PRODUCT_COLUMN_NAME === $column ) {
			$order = \wc_get_order( $order_id );
			$items = $order?->get_items();
			$items = is_array( $items ) ? $items : [];
			foreach ( $items as $item ) {
				/**
				 * Type
				 *
				 * @var \WC_Order_Item_Product $item Order item.
				 */
				$product      = $item?->get_product();
				$product_name = $product?->get_name();
				$product_id   = $product?->get_id();
				$quantity     = $item?->get_quantity();
				$product_link = \get_edit_post_link( $product_id );
				echo '<a href="' . esc_url( $product_link ) . '">' . esc_html( $product_name ) . '</a> x ' . esc_html( $quantity ) . '<br />';
			}
		}
	}
}
