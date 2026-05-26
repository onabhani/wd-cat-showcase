<?php
/**
 * Dependency checks (WooCommerce, Woodmart).
 *
 * @package Hdqah\WDCS
 */

declare( strict_types=1 );

namespace Hdqah\WDCS;

defined( 'ABSPATH' ) || exit;

final class Dependencies {

	public static function has_woocommerce(): bool {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Woodmart's product block is registered as `wd/products`. Presence of the
	 * registered block is the most reliable signal that we can delegate rendering.
	 */
	public static function has_woodmart_products_block(): bool {
		$registry = \WP_Block_Type_Registry::get_instance();
		return $registry->is_registered( 'wd/products' );
	}

	/**
	 * Admin notice when WooCommerce is missing. Hooked on `admin_notices`.
	 */
	public static function maybe_render_admin_notice(): void {
		if ( self::has_woocommerce() ) {
			return;
		}

		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			esc_html__( 'Woodmart Category Showcase requires WooCommerce to be active.', 'wd-cat-showcase' )
		);
	}
}
