<?php
/**
 * Plugin Name: Open Search Document
 * Plugin URI: https://github.com/pfefferle/wordpress-open-search-document/
 * Description: Create an Open Search Document for your blog.
 * Version: 4.1.1
 * Author: Matthias Pfefferle
 * Author URI: https://github.com/pfefferle/wordpress-open-search-document/
 * License: GPL-2.0-or-later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace OpenSearchDocument;

/**
 * Initialize plugin
 */
function init() {
	require_once( dirname( __FILE__ ) . '/includes/class-wp-rest-controller.php' );
	WP_REST_Controller::init();

	require_once( dirname( __FILE__ ) . '/includes/class-discovery.php' );
	Discovery::init();

	require_once( dirname( __FILE__ ) . '/includes/functions.php' );
}
add_action( 'init', 'OpenSearchDocument\init' );

