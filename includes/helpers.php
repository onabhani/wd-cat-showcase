<?php
/**
 * Shared helpers.
 *
 * @package Hdqah\WDCS
 */

declare( strict_types=1 );

namespace Hdqah\WDCS\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Generate a deterministic-ish unique block id for Woodmart's product block.
 *
 * Woodmart uses `blockId` for scoped CSS / behavior; collisions break styling
 * on pages with multiple instances. Editor-side we persist a UUID in attributes;
 * server-side this is the fallback when the attribute is empty.
 */
function unique_block_id( string $prefix = 'wdcs' ): string {
	return $prefix . '-' . wp_generate_uuid4();
}

/**
 * Get a product_cat term safely. Returns null when missing or wrong taxonomy.
 */
function get_product_category( int $term_id ): ?\WP_Term {
	if ( $term_id <= 0 ) {
		return null;
	}

	$term = get_term( $term_id, 'product_cat' );

	if ( ! $term || is_wp_error( $term ) ) {
		return null;
	}

	return $term;
}
