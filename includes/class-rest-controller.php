<?php
/**
 * REST endpoint that powers the editor's product-category picker.
 *
 * Route: GET /wp-json/wdcs/v1/categories?search=foo&per_page=20
 *
 * @package Hdqah\WDCS
 */

declare( strict_types=1 );

namespace Hdqah\WDCS;

defined( 'ABSPATH' ) || exit;

final class REST_Controller {

	private const NAMESPACE = 'wdcs/v1';
	private const ROUTE     = '/categories';

	public static function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			self::ROUTE,
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ self::class, 'get_categories' ],
				'permission_callback' => [ self::class, 'permission_check' ],
				'args'                => [
					'search'   => [
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'default'           => '',
					],
					'per_page' => [
						'type'    => 'integer',
						'default' => 50,
						'minimum' => 1,
						'maximum' => 100,
					],
					'parent'   => [
						'type'    => 'integer',
						'default' => 0,
						'minimum' => 0,
					],
				],
			]
		);
	}

	public static function permission_check(): bool {
		// Editor-only endpoint; same threshold as inserting a block.
		return current_user_can( 'edit_posts' );
	}

	public static function get_categories( \WP_REST_Request $request ): \WP_REST_Response {
		if ( ! Dependencies::has_woocommerce() ) {
			return new \WP_REST_Response( [], 200 );
		}

		$args = [
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'number'     => absint( $request->get_param( 'per_page' ) ),
			'orderby'    => 'name',
			'order'      => 'ASC',
		];

		$search = (string) $request->get_param( 'search' );
		if ( '' !== $search ) {
			$args['search'] = $search;
		}

		$parent = (int) $request->get_param( 'parent' );
		if ( $parent > 0 ) {
			$args['parent'] = $parent;
		}

		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) ) {
			return new \WP_REST_Response( [], 200 );
		}

		$data = array_map(
			static function ( \WP_Term $term ): array {
				return [
					'id'     => $term->term_id,
					'name'   => $term->name,
					'slug'   => $term->slug,
					'parent' => $term->parent,
					'count'  => $term->count,
				];
			},
			$terms
		);

		return new \WP_REST_Response( $data, 200 );
	}
}
