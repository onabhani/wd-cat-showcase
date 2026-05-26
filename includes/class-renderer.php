<?php
/**
 * Server-side render callback for the showcase block.
 *
 * Responsibility: build the section wrapper, header, child-category nav,
 * then delegate the product grid to Woodmart's `wd/products` block via
 * `do_blocks()`. The plugin must NEVER emit product card markup.
 *
 * @package Hdqah\WDCS
 */

declare( strict_types=1 );

namespace Hdqah\WDCS;

use function Hdqah\WDCS\Helpers\get_product_category;
use function Hdqah\WDCS\Helpers\unique_block_id;

defined( 'ABSPATH' ) || exit;

final class Renderer {

	/**
	 * Block render callback.
	 *
	 * @param array<string,mixed> $attributes Block attributes (already typed by block.json).
	 */
	public static function render( array $attributes ): string {
		if ( ! Dependencies::has_woocommerce() ) {
			return '';
		}

		$category_id = absint( $attributes['categoryId'] ?? 0 );
		$term        = get_product_category( $category_id );

		// Without a valid category there's nothing meaningful to render.
		if ( ! $term ) {
			return self::render_editor_placeholder();
		}

		$label                  = (string) ( $attributes['label'] ?? '' );
		$title                  = (string) ( $attributes['title'] ?? '' );
		$show_view_all          = (bool) ( $attributes['showViewAll'] ?? true );
		$view_all_text          = (string) ( $attributes['viewAllText'] ?? '' );
		$view_all_url           = (string) ( $attributes['viewAllUrl'] ?? '' );
		$show_children          = (bool) ( $attributes['showChildren'] ?? true );
		$show_parent_first      = (bool) ( $attributes['showParentAsFirstTab'] ?? true );
		$parent_tab_label       = (string) ( $attributes['parentTabLabel'] ?? '' );
		$block_id               = (string) ( $attributes['blockId'] ?? '' );

		if ( '' === $block_id ) {
			$block_id = unique_block_id();
		}

		// Fallbacks driven by the selected term.
		if ( '' === $title ) {
			$title = $term->name;
		}
		if ( '' === $view_all_url ) {
			$view_all_url = (string) get_term_link( $term );
		}

		$wrapper_attrs = get_block_wrapper_attributes(
			[
				'class' => 'wdcs',
				'id'    => $block_id,
			]
		);

		ob_start();
		?>
		<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes returns escaped output. ?>>
			<?php echo self::render_header( $label, $title, $show_view_all, $view_all_text, $view_all_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- header markup is escaped internally. ?>

			<?php if ( $show_children ) : ?>
				<?php echo self::render_children( $term, $show_parent_first, $parent_tab_label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- children markup is escaped internally. ?>
			<?php endif; ?>

			<div class="wdcs__products">
				<?php echo self::render_products_grid( $attributes, $block_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- delegated to do_blocks(). ?>
			</div>
		</section>
		<?php
		return (string) ob_get_clean();
	}

	private static function render_header(
		string $label,
		string $title,
		bool $show_view_all,
		string $view_all_text,
		string $view_all_url
	): string {
		ob_start();
		?>
		<div class="wdcs__header">
			<div class="wdcs__heading">
				<?php if ( '' !== $label ) : ?>
					<span class="wdcs__label"><?php echo esc_html( $label ); ?></span>
				<?php endif; ?>
				<h2 class="wdcs__title"><?php echo esc_html( $title ); ?></h2>
			</div>

			<?php if ( $show_view_all && '' !== $view_all_url ) : ?>
				<a class="wdcs__view-all" href="<?php echo esc_url( $view_all_url ); ?>">
					<?php echo esc_html( '' !== $view_all_text ? $view_all_text : __( 'View all', 'wd-cat-showcase' ) ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	private static function render_children(
		\WP_Term $parent,
		bool $show_parent_first,
		string $parent_tab_label
	): string {
		$children = get_terms(
			[
				'taxonomy'   => 'product_cat',
				'parent'     => $parent->term_id,
				'hide_empty' => true,
			]
		);

		if ( is_wp_error( $children ) ) {
			return '';
		}

		if ( empty( $children ) && ! $show_parent_first ) {
			return '';
		}

		ob_start();
		?>
		<nav class="wdcs__children" aria-label="<?php esc_attr_e( 'Product subcategories', 'wd-cat-showcase' ); ?>">
			<?php if ( $show_parent_first ) : ?>
				<?php $first_label = '' !== $parent_tab_label ? $parent_tab_label : $parent->name; ?>
				<a class="wdcs__child wdcs__child--parent" href="<?php echo esc_url( (string) get_term_link( $parent ) ); ?>">
					<?php echo esc_html( $first_label ); ?>
				</a>
			<?php endif; ?>

			<?php foreach ( $children as $child ) : ?>
				<a class="wdcs__child" href="<?php echo esc_url( (string) get_term_link( $child ) ); ?>">
					<?php echo esc_html( $child->name ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Build a wd/products block comment, run it through do_blocks(), return the HTML.
	 *
	 * Critical: this is the SOLE source of product card markup. We never emit our own.
	 */
	private static function render_products_grid( array $attributes, string $block_id ): string {
		if ( ! Dependencies::has_woodmart_products_block() ) {
			// Don't break the page for visitors; show a hint to admins only.
			if ( current_user_can( 'edit_posts' ) ) {
				return sprintf(
					'<p class="wdcs__admin-notice">%s</p>',
					esc_html__( 'Woodmart product block is not available. Activate the Woodmart theme to render products.', 'wd-cat-showcase' )
				);
			}
			return '';
		}

		$category_id = absint( $attributes['categoryId'] );

		$wd_attrs = [
			'blockId'           => 'wd-' . $block_id,
			'blockVersion'      => '2',
			'categoriesIds'     => (string) $category_id,
			'post_type'         => (string) ( $attributes['productSource'] ?? 'bestselling' ),
			'orderby'           => (string) ( $attributes['orderby'] ?? 'popularity' ),
			'order'             => (string) ( $attributes['order'] ?? 'DESC' ),
			'hide_out_of_stock' => (bool) ( $attributes['hideOutOfStock'] ?? true ),
			'spacing'           => (string) ( $attributes['spacing'] ?? '16' ),
			'items_per_page'    => (string) ( $attributes['itemsPerPage'] ?? 4 ),
		];

		/**
		 * Filter the attributes passed to the underlying wd/products block.
		 *
		 * @param array<string,mixed>  $wd_attrs    Attributes for `wd/products`.
		 * @param array<string,mixed>  $attributes  This block's attributes.
		 */
		$wd_attrs = apply_filters( 'wdcs_woodmart_products_attrs', $wd_attrs, $attributes );

		$block_comment = sprintf(
			'<!-- wp:wd/products %s /-->',
			wp_json_encode( $wd_attrs )
		);

		return (string) do_blocks( $block_comment );
	}

	/**
	 * Empty-state shown in the editor / preview when no category is picked.
	 * On the frontend (no edit_posts cap), returns an empty string.
	 */
	private static function render_editor_placeholder(): string {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return '';
		}

		return sprintf(
			'<div class="wdcs wdcs--placeholder"><p>%s</p></div>',
			esc_html__( 'Select a product category in the block sidebar to render the showcase.', 'wd-cat-showcase' )
		);
	}
}
