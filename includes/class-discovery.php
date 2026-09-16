<?php
/**
 * Discovery.
 *
 * @package OpenSearchDocument
 */

namespace OpenSearchDocument;

/**
 * Announces the OpenSearch description document in HTML, feeds and XRD documents.
 */
class Discovery {

	/**
	 * Hook the discovery mechanisms into WordPress.
	 */
	public static function init() {
		\add_action( 'wp_head', array( static::class, 'add_head' ) );
		\add_action( 'atom_head', array( static::class, 'add_head' ) );
		\add_action( 'rss2_head', array( static::class, 'add_rss_head' ) );
		\add_action( 'atom_ns', array( static::class, 'add_atom_namespace' ) );

		\add_filter( 'host_meta', array( static::class, 'add_xrd_links' ) );
		\add_filter( 'webfinger_user_data', array( static::class, 'add_xrd_links' ) );
		\add_filter( 'web_app_manifest', array( static::class, 'web_app_manifest' ) );

		\add_filter( 'site_icon_image_sizes', array( static::class, 'site_icon_image_sizes' ) );
		\add_action( 'osd_xml', array( static::class, 'osd_xml' ) );
	}

	/**
	 * The title of the search link.
	 *
	 * @return string The title.
	 */
	protected static function get_title() {
		/* translators: %s: the site name */
		return \sprintf( \__( 'Search %s', 'open-search-document' ), \get_bloginfo( 'name' ) );
	}

	/**
	 * HTML and Atom autodiscovery link.
	 */
	public static function add_head() {
		\printf(
			'<link rel="search" type="application/opensearchdescription+xml" title="%1$s" href="%2$s" />' . PHP_EOL,
			\esc_attr( static::get_title() ),
			\esc_url( get_document_url() )
		);
	}

	/**
	 * RSS autodiscovery link.
	 */
	public static function add_rss_head() {
		\printf(
			'<atom:link rel="search" type="application/opensearchdescription+xml" title="%1$s" href="%2$s" />' . PHP_EOL,
			\esc_attr( static::get_title() ),
			\esc_url( get_document_url() )
		);
	}

	/**
	 * Atom namespace.
	 */
	public static function add_atom_namespace() {
		echo ' xmlns:opensearch="http://a9.com/-/spec/opensearch/1.1/"' . PHP_EOL;
	}

	/**
	 * Add the document to host-meta and WebFinger.
	 *
	 * @param array $xrd The current XRD array.
	 *
	 * @return array The updated XRD array.
	 */
	public static function add_xrd_links( $xrd ) {
		if ( ! isset( $xrd['links'] ) || ! \is_array( $xrd['links'] ) ) {
			$xrd['links'] = array();
		}

		$xrd['links'][] = array(
			'rel'  => 'http://a9.com/-/spec/opensearch/1.1/',
			'href' => get_document_url(),
			'type' => 'application/opensearchdescription+xml',
		);

		return $xrd;
	}

	/**
	 * Register the icon sizes used in the document.
	 *
	 * @param int[] $sizes The available site icon sizes.
	 *
	 * @return int[] The updated sizes.
	 */
	public static function site_icon_image_sizes( $sizes ) {
		$sizes[] = 16;
		$sizes[] = 32;
		$sizes[] = 64;

		return \array_unique( $sizes );
	}

	/**
	 * Add the site icon to the document.
	 */
	public static function osd_xml() {
		if ( ! \has_site_icon() ) {
			return;
		}

		$type = \get_post_mime_type( \get_option( 'site_icon' ) );

		foreach ( array( 16, 32, 64 ) as $size ) {
			\printf(
				'	<Image height="%1$d" width="%1$d"%2$s>%3$s</Image>' . PHP_EOL,
				(int) $size,
				$type ? \sprintf( ' type="%s"', \esc_attr( $type ) ) : '',
				\esc_xml( \get_site_icon_url( $size ) )
			);
		}
	}

	/**
	 * Add the site as search provider to the web app manifest.
	 *
	 * See https://developer.mozilla.org/en-US/docs/Mozilla/Add-ons/WebExtensions/manifest.json/chrome_settings_overrides
	 *
	 * @param array $manifest The web app manifest.
	 *
	 * @return array The updated manifest.
	 */
	public static function web_app_manifest( $manifest ) {
		if ( ! isset( $manifest['chrome_settings_overrides'] ) ) {
			$manifest['chrome_settings_overrides'] = array();
		}

		$manifest['chrome_settings_overrides']['search_provider'] = array(
			'name'        => \get_bloginfo( 'name' ),
			'search_url'  => get_url_template( 'html' ),
			'keyword'     => \sanitize_title( \get_bloginfo( 'name' ) ),
			'favicon_url' => \get_site_icon_url( 32 ),
			'encoding'    => \get_bloginfo( 'charset' ),
		);

		return $manifest;
	}
}
