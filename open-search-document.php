<?php
/**
 * Plugin Name: Open Search Document
 * Plugin URI: https://github.com/pfefferle/wordpress-open-search-document/
 * Description: Lets browsers search your site straight from the address bar. Adds an OpenSearch description document with search suggestions.
 * Version: 4.2.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Author: Matthias Pfefferle
 * Author URI: https://notiz.blog/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: open-search-document
 *
 * @package OpenSearchDocument
 */

namespace OpenSearchDocument;

\defined( 'ABSPATH' ) || exit;

\define( 'OPEN_SEARCH_DOCUMENT_VERSION', '4.2.0' );
\define( 'OPEN_SEARCH_DOCUMENT_PLUGIN_DIR', \plugin_dir_path( __FILE__ ) );
\define( 'OPEN_SEARCH_DOCUMENT_PLUGIN_FILE', __FILE__ );

require_once OPEN_SEARCH_DOCUMENT_PLUGIN_DIR . 'includes/functions.php';
require_once OPEN_SEARCH_DOCUMENT_PLUGIN_DIR . 'includes/class-rest-controller.php';
require_once OPEN_SEARCH_DOCUMENT_PLUGIN_DIR . 'includes/class-discovery.php';

/**
 * Initialize REST routes.
 */
function rest_init() {
	( new Rest_Controller() )->register_routes();
}
\add_action( 'rest_api_init', __NAMESPACE__ . '\rest_init' );

/**
 * Initialize the plugin.
 */
function plugin_init() {
	Discovery::init();
}
\add_action( 'plugins_loaded', __NAMESPACE__ . '\plugin_init' );
