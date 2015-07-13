<?php
/*
Plugin Name: Open Search Document
Plugin URI: http://wordpress.org/plugins/open-search-document/
Description: Create an Open Search Document for your blog.
Version: 2.0.0
Author: johnnoone, pfefferle
Author URI: https://github.com/pfefferle/wordpress-open-search-document/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
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
		add_filter( 'query_vars', array( 'OpenSearchDocumentPlugin', 'query_vars' ) );
		add_action( 'parse_request', array( 'OpenSearchDocumentPlugin', 'parse_request' ) );
		add_action( 'atom_ns', array( 'OpenSearchDocumentPlugin', 'add_atom_namespace' ) );

		add_action( 'site_icon_image_sizes', array( 'OpenSearchDocumentPlugin', 'site_icon_image_sizes' ) );

		add_action( 'opensearch_1.1', array( 'OpenSearchDocumentPlugin', 'render_discovery' ) );
		add_action( 'opensearch_suggestions', array( 'OpenSearchDocumentPlugin', 'render_suggestions' ), 10, 1 );
		// Backwards compatibility.
		add_action( 'opensearch_true', array( 'OpenSearchDocumentPlugin', 'render_discovery' ) );

		add_action( 'osd_xml', array( 'OpenSearchDocumentPlugin', 'osd_xml' ) );

		// Add autodiscovery.
		add_action( 'wp_head', array( 'OpenSearchDocumentPlugin', 'add_head' ) );
		add_action( 'atom_head', array( 'OpenSearchDocumentPlugin', 'add_head' ) );
		add_action( 'rss2_head', array( 'OpenSearchDocumentPlugin', 'add_rss_head' ) );
		add_filter( 'xrds_simple', array( 'OpenSearchDocumentPlugin', 'add_xrds_simple_links' ) );
		add_filter( 'host_meta', array( 'OpenSearchDocumentPlugin', 'add_xrd_links' ) );
		add_filter( 'webfinger', array( 'OpenSearchDocumentPlugin', 'add_xrd_links' ) );
	}

	/**
	 * Add some query-vars
	 *
	 * @param array $vars query vars.
	 * @return array updated query vars
	 */
	public static function query_vars( $vars ) {
		$vars[] = 'opensearch';
		$vars[] = 's';

		return $vars;
	}

	/**
	 * Parse request and "do" some actions
	 *
	 * @param $wp
	 */
	public static function parse_request( $wp ) {
		// check if it is an opensearch request or not
		if ( array_key_exists( 'opensearch', $wp->query_vars ) ) {
			$opensearch = $wp->query_vars['opensearch'];

			do_action( 'opensearch', $opensearch, $wp->query_vars );
			do_action( "opensearch_{$opensearch}", $wp->query_vars );
		}
	}

	/**
	 * Render the OpenSearch document
	 */
	public static function render_discovery() {
		header( 'Content-Type: application/opensearchdescription+xml' );
		header( 'Encoding: ' . get_bloginfo( 'charset' ) );
		echo '<?xml version="1.0" encoding="' . get_bloginfo( 'charset' ) . '"?>';
	?>

<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/ <?php do_action( 'osd_ns' ); ?>">
	<ShortName><?php bloginfo( 'name' ); ?></ShortName>
	<Description><?php bloginfo( 'description' ); ?></Description>
	<Url type="text/html" method="get" template="<?php echo site_url( '/?s={searchTerms}' ); ?>"></Url>
	<Url type="application/atom+xml" method="get" template="<?php echo add_query_arg( 's', '{searchTerms}', bloginfo( 'atom_url' ) ); ?>" />
	<Url type="application/rss+xml" method="get" template="<?php echo add_query_arg( 's', '{searchTerms}', bloginfo( 'rss2_url' ) ); ?>" />
	<Url type="application/x-suggestions+json" method="get" template="<?php echo site_url( '/?opensearch=suggestions&amp;s={searchTerms}' ); ?>"/>
	<Contact><?php bloginfo( 'admin_email' ); ?></Contact>
	<LongName><?php bloginfo( 'name' ); ?> Web Search</LongName>
	<Tags>wordpress blog</Tags>
	<Query role="example" searchTerms="blog" />
	<Developer>johnnoone, Matthias Pfefferle</Developer>
	<Language><?php bloginfo( 'language' ); ?></Language>
	<OutputEncoding><?php bloginfo( 'charset' ); ?></OutputEncoding>
	<InputEncoding><?php bloginfo( 'charset' ); ?></InputEncoding>
	<?php do_action( 'osd_xml' ); ?>
</OpenSearchDescription>

	<?php
		exit;
	}

	/**
	 * Render the the suggestion JSON, based on the WordPress tags
	 *
	 * @param $query_vars array the query vars as array.
	 */
	public static function render_suggestions( $query_vars ) {
		$tags = array();
		$output = array();

		header( 'Access-Control-Allow-Origin: *' );
		header( 'Content-Type: application/json; charset=' . get_bloginfo( 'charset' ), true );

		if ( ! array_key_exists( 's', $query_vars ) ) {
			echo json_encode( $output );
			exit;
		}

		foreach ( get_tags( 'search='.$query_vars['s'] ) as $tag ) {
			$tags[] = $tag->name;
		}

		$output[] = $query_vars['s'];
		$output[] = $tags;

		echo wp_json_encode( $output );
		exit;
	}

	/**
	 * HTML/Atom autodiscovery header
	 */
	public static function add_head() {
		echo '<link rel="search" type="application/opensearchdescription+xml" title="Search ' . get_bloginfo( 'name' ) . '" href="' . site_url( '/?opensearch=1.1' ) . '" />' . "\n";
	}

	/**
	 * RSS autodiscovery header
	 */
	public static function add_rss_head() {
		echo '<atom:link rel="search" type="application/opensearchdescription+xml" title="Search ' . get_bloginfo( 'name' ) . '" href="' . site_url( '/?opensearch=1.1' ) . '" />' . "\n";
	}

	/**
	 * Atom namespace
	 */
	public static function add_atom_namespace() {
		echo ' xmlns:opensearch="http://a9.com/-/spec/opensearch/1.1/" ' . "\n";
	}

	/**
	 * RSS namespace
	 */
	public static function add_rss_namespace() {
		echo ' xmlns:atom="http://www.w3.org/2005/Atom" ' . "\n";
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
				'URI' => array( array( 'content' => site_url( '/?opensearch=1.1' ) ) ),
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
		$xrd['links'][] = array( 'rel' => 'http://a9.com/-/spec/opensearch/1.1/', 'href' => site_url( '/?opensearch=1.1' ), 'type' => 'application/opensearchdescription+xml' );

		return $xrd;
	}

	/**
	 * Adds OSD Images
	 */
	public static function osd_xml() {
		if ( function_exists( 'get_site_icon_url' ) ) {
?>
	<Image height="16" width="16"><?php echo get_site_icon_url( null, 16 ); ?></Image>
	<Image height="32" width="32"><?php echo get_site_icon_url( null, 32 ); ?></Image>
	<Image height="64" width="64"><?php echo get_site_icon_url( null, 64 ); ?></Image>
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
		$sizes[] = '16';
		$sizes[] = '32';
		$sizes[] = '64';

		return array_unique( $sizes );
	}
}
