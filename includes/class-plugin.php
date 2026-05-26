<?php
/**
 * Plugin bootstrap.
 *
 * @package Hdqah\WDCS
 */

declare( strict_types=1 );

namespace Hdqah\WDCS;

defined( 'ABSPATH' ) || exit;

final class Plugin {

	private static ?self $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->register_hooks();
		}
		return self::$instance;
	}

	private function __construct() {}

	private function register_hooks(): void {
		add_action( 'init', [ $this, 'register_block' ] );
		add_action( 'rest_api_init', [ REST_Controller::class, 'register_routes' ] );
		add_action( 'admin_notices', [ Dependencies::class, 'maybe_render_admin_notice' ] );
	}

	public function register_block(): void {
		register_block_type(
			WDCS_DIR,
			[
				'render_callback' => [ Renderer::class, 'render' ],
			]
		);
	}
}
