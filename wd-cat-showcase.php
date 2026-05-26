<?php
/**
 * Plugin Name:       Woodmart Category Showcase
 * Plugin URI:        https://github.com/onabhani/wd-cat-showcase
 * Description:       Dynamic Gutenberg block that displays a WooCommerce product category section (label, title, view-all, child category tabs, products grid) using Woodmart's native product block for the grid — so cards stay visually identical to the rest of the site.
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      8.2
 * Author:            Omar Alnabhani (hdqah)
 * Author URI:        https://hdqah.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wd-cat-showcase
 * Domain Path:       /languages
 *
 * @package Hdqah\WDCS
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

define( 'WDCS_VERSION', '0.1.0' );
define( 'WDCS_FILE', __FILE__ );
define( 'WDCS_DIR', plugin_dir_path( __FILE__ ) );
define( 'WDCS_URL', plugin_dir_url( __FILE__ ) );
define( 'WDCS_BLOCK_NAME', 'hdqah/wd-cat-showcase' );

require_once WDCS_DIR . 'includes/helpers.php';
require_once WDCS_DIR . 'includes/class-dependencies.php';
require_once WDCS_DIR . 'includes/class-renderer.php';
require_once WDCS_DIR . 'includes/class-rest-controller.php';
require_once WDCS_DIR . 'includes/class-plugin.php';

\Hdqah\WDCS\Plugin::instance();
