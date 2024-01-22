<?php

namespace OpenSearchDocument;

use WP_Error;

use function OpenSearchDocument\url_template;

/**
 * Handles all discovery mechanisms
 */
class Discovery {
	/**
	 * Initialize class.
	 */
	public static function init() {
		add_action( 'atom_ns', array( static::class, 'add_atom_namespace' ) );

		add_filter( 'site_icon_image_sizes', array( static::class, 'site_icon_image_sizes' ) );
		add_action( 'osd_xml', array( static::class, 'osd_xml' ) );
		add_filter( 'web_app_manifest', array( static::class, 'web_app_manifest' ) );

		// Add autodiscovery.
		add_action( 'wp_head', array( static::class, 'add_head' ) );
		add_action( 'atom_head', array( static::class, 'add_head' ) );
		add_action( 'rss2_head', array( static::class, 'add_rss_head' ) );
		add_filter( 'xrds_simple', array( static::class, 'add_xrds_simple_links' ) );
		add_filter( 'host_meta', array( static::class, 'add_xrd_links' ) );
		add_filter( 'webfinger_user_data', array( static::class, 'add_xrd_links' ) );
	}

	/**
	 * HTML/Atom autodiscovery header.
	 */
	public static function add_head() {
		printf( '<link rel="search" type="application/opensearchdescription+xml" title="Search %s" href="%s" />', get_bloginfo( 'name' ), rest_url( 'opensearch/1.1/document' ) ) . PHP_EOL;
	}

	/**
	 * RSS autodiscovery header.
	 */
	public static function add_rss_head() {
		printf( '<atom:link rel="search" type="application/opensearchdescription+xml" title="Search %s" href="%s" />', get_bloginfo( 'name' ), rest_url( 'opensearch/1.1/document' ) ) . PHP_EOL;
	}

	/**
	 * Atom namespace.
	 */
	public static function add_atom_namespace() {
		echo ' xmlns:opensearch="http://a9.com/-/spec/opensearch/1.1/" ' . PHP_EOL;
	}

	/**
	 * RSS namespace.
	 */
	public static function add_rss_namespace() {
		echo ' xmlns:atom="http://www.w3.org/2005/Atom" ' . PHP_EOL;
	}

	/**
	 * XRDS-Simple informations.
	 *
	 * @param array $xrds current XRDS-Simple array.
	 *
	 * @return array updated XRDS-Simple array.
	 */
	public static function add_xrds_simple_links( $xrds ) {
		$xrds = xrds_add_service(
			$xrds,
			'main',
			'OpenSearchDocument',
			array(
				'Type'      => array(
					array( 'content' => 'http://a9.com/-/spec/opensearch/1.1/' ),
				),
				'MediaType' => array(
					array( 'content' => 'application/opensearchdescription+xml' ),
				),
				'URI'       => array(
					array( 'content' => rest_url( 'opensearch/1.1/document' ) ),
				),
			)
		);
		return $xrds;
	}

	/**
	 * host-meta/webfinger informations.
	 *
	 * @param array $xrd current XRD array.
	 *
	 * @return array updated XRD array.
	 */
	public static function add_xrd_links( $xrd ) {
		$xrd['links'][] = array(
			'rel'  => 'http://a9.com/-/spec/opensearch/1.1/',
			'href' => rest_url( 'opensearch/1.1/document' ),
			'type' => 'application/opensearchdescription+xml',
		);

		return $xrd;
	}

	/**
	 * Add icons.
	 *
	 * @param array $sizes sizes available for the site icon.
	 *
	 * @return array updated list of icons.
	 */
	public static function site_icon_image_sizes( $sizes ) {
		$sizes[] = 16;
		$sizes[] = 32;
		$sizes[] = 64;

		return array_unique( $sizes );
	}

	/**
	 * Adds OSD Images.
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
	 * Modifies the site's web app manifest.
	 *
	 * @param array $manifest The associative web app manifest array.
	 * @return array The filtered $manifest.
	 */
	public static function web_app_manifest( $manifest ) {
		if ( ! isset( $manifest['chrome_settings_overrides'] ) ) {
			$manifest['chrome_settings_overrides'] = array();
		}

		$manifest['chrome_settings_overrides']['search_provider'] = array(
			'name' => \get_bloginfo( 'name' ),
			'search_url' => url_template( false ),
			'keyword' => \sanitize_title( get_bloginfo( 'name' ) ),
			'favicon_url' => \get_site_icon_url( 32 ),
			'encoding' => \get_bloginfo( 'charset' ),
		);

		return $manifest;
	}
}
