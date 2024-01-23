<?php

namespace OpenSearchDocument;

/**
 * Get the HTML search-url-template.
 *
 * @param boolean $echo Echo result or simply return it.
 *
 * @return void|string
 */
function url_template( $echo = true ) {
	$url_template = site_url( '/?s=searchTerms' );

	/**
	 * Filters the Search-URL-Template
	 *
	 * @since OpenSearchDocument 4.0.0
	 *
	 * @param array $args {
	 *     An array of Search-URL-Template arguments.
	 *
	 *     @type string $feed_url_template The Search-URL-Template.
	 *     @type string $type              The Response-Type: `html`.
	 * }
	 */
	$url_template = apply_filters( 'osd_search_url_template', $url_template, 'html' );

	$url_template = esc_xml( esc_url_raw( $url_template ) );
	$url_template = str_replace( 'searchTerms', '{searchTerms}', $url_template );

	if ( $echo ) {
		echo $url_template;
	} else {
		return $url_template;
	}
}

/**
 * Get the feed-search-template.
 *
 * @param string  $feed The feed-type.
 * @param boolean $echo Echo result or simply return it.
 *
 * @return void|string
 */
function feed_url_template( $feed, $echo = true ) {
	if ( ! in_array( $feed, array( 'atom', 'rss2' ), true ) ) {
		return new WP_Error( 'unsupported_feed_type' );
	}

	$feed_url_template = add_query_arg( 's', 'searchTerms', get_bloginfo( "{$feed}_url" ) );

	/**
	 * Filters the Search-URL-Template
	 *
	 * @since OpenSearchDocument 4.0.0
	 *
	 * @param array $args {
	 *     An array of Search-URL-Template arguments.
	 *
	 *     @type string $feed_url_template The Feed-Search-URL-Template.
	 *     @type string $type              The Response-Type: `rss2` or `atom`.
	 * }
	 */
	$feed_url_template = apply_filters( 'osd_search_url_template', $feed_url_template, $feed );

	$feed_url_template = esc_xml( esc_url_raw( $feed_url_template ) );
	$feed_url_template = str_replace( 'searchTerms', '{searchTerms}', $feed_url_template );

	if ( $echo ) {
		echo $feed_url_template;
	} else {
		return $feed_url_template;
	}
}
