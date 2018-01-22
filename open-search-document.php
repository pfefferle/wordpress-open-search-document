<?php
/**
 * Plugin Name: Open Search Document
 * Plugin URI: https://github.com/pfefferle/wordpress-open-search-document/
 * Description: Create an Open Search Document for your blog.
 * Version: 3.0.1
 * Author: johnnoone, pfefferle
 * Author URI: https://github.com/pfefferle/wordpress-open-search-document/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

add_filter( 'init', array( 'OpenSearchDocumentPlugin', 'init' ) );

/**
 * Open search document for WordPress
 *
 * @author Matthias Pfefferle
 * @author johnnoon
 */
class OpenSearchDocumentPlugin {

	/**
	 * Initialize plugin
	 */
	public static function init() {
		add_action( 'atom_ns', array( 'OpenSearchDocumentPlugin', 'add_atom_namespace' ) );

		add_filter( 'site_icon_image_sizes', array( 'OpenSearchDocumentPlugin', 'site_icon_image_sizes' ) );
		add_action( 'osd_xml', array( 'OpenSearchDocumentPlugin', 'osd_xml' ) );

		// Add autodiscovery.
		add_action( 'wp_head', array( 'OpenSearchDocumentPlugin', 'add_head' ) );
		add_action( 'atom_head', array( 'OpenSearchDocumentPlugin', 'add_head' ) );
		add_action( 'rss2_head', array( 'OpenSearchDocumentPlugin', 'add_rss_head' ) );
		add_filter( 'xrds_simple', array( 'OpenSearchDocumentPlugin', 'add_xrds_simple_links' ) );
		add_filter( 'host_meta', array( 'OpenSearchDocumentPlugin', 'add_xrd_links' ) );
		add_filter( 'webfinger_user_data', array( 'OpenSearchDocumentPlugin', 'add_xrd_links' ) );

		// API
		require_once( dirname( __FILE__ ) . '/includes/class-wp-rest-open-search-controller.php' );
		// Configure the REST API route.
		add_action( 'rest_api_init', array( 'WP_REST_Open_Search_Controller', 'register_routes' ) );
		// Filter the REST API response to output XML if requested.
		// Filter the response to allow plaintext
		add_filter( 'rest_pre_serve_request', array( 'WP_REST_Open_Search_Controller', 'serve_request' ), 9, 4 );
	}

	/**
	 * HTML/Atom autodiscovery header
	 */
	public static function add_head() {
		printf( '<link rel="search" type="application/opensearchdescription+xml" title="Search %s" href="%s" />', get_bloginfo( 'name' ), rest_url( 'opensearch/1.1/document' ) ) . PHP_EOL;
	}

	/**
	 * RSS autodiscovery header
	 */
	public static function add_rss_head() {
		printf( '<atom:link rel="search" type="application/opensearchdescription+xml" title="Search %s" href="%s" />', get_bloginfo( 'name' ), rest_url( 'opensearch/1.1/document' ) ) . PHP_EOL;
	}

	/**
	 * Atom namespace
	 */
	public static function add_atom_namespace() {
		echo ' xmlns:opensearch="http://a9.com/-/spec/opensearch/1.1/" ' . PHP_EOL;
	}

	/**
	 * RSS namespace
	 */
	public static function add_rss_namespace() {
		echo ' xmlns:atom="http://www.w3.org/2005/Atom" ' . PHP_EOL;
	}

	/**
	 * XRDS-Simple informations
	 *
	 * @param array $xrds current XRDS-Simple array
	 * @return array updated XRDS-Simple array
	 */
	public static function add_xrds_simple_links( $xrds ) {
		$xrds = xrds_add_service( $xrds, 'main', 'OpenSearchDocument',
			array(
				'Type' => array( array( 'content' => 'http://a9.com/-/spec/opensearch/1.1/' ) ),
				'MediaType' => array( array( 'content' => 'application/opensearchdescription+xml' ) ),
				'URI' => array( array( 'content' => rest_url( 'opensearch/1.1/document' ) ) ),
			)
		);
		return $xrds;
	}

	/**
	 * host-meta/webfinger informations
	 *
	 * @param array $xrd current XRD array
	 * @return array updated XRD array
	 */
	public static function add_xrd_links( $xrd ) {
		$xrd['links'][] = array(
			'rel' => 'http://a9.com/-/spec/opensearch/1.1/',
			'href' => rest_url( 'opensearch/1.1/document' ),
			'type' => 'application/opensearchdescription+xml',
		);

		return $xrd;
	}

	/**
	 * Adds OSD Images
	 */
	public static function osd_xml() {
		if ( function_exists( 'get_site_icon_url' ) && has_site_icon() ) {
?>
	<Image height="16" width="16"><?php echo get_site_icon_url( 16 ); ?></Image>
	<Image height="32" width="32"><?php echo get_site_icon_url( 32 ); ?></Image>
	<Image height="64" width="64"><?php echo get_site_icon_url( 64 ); ?></Image>
<?php
		}
	}

	/**
	 * Add icons
	 *
	 * @param  array $sizes sizes available for the site icon
	 * @return array        updated list of icons
	 */
	public static function site_icon_image_sizes( $sizes ) {
		$sizes[] = 16;
		$sizes[] = 32;
		$sizes[] = 64;

		return array_unique( $sizes );
	}
}
