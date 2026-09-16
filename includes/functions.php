<?php
/**
 * Helper functions.
 *
 * @package OpenSearchDocument
 */

namespace OpenSearchDocument;

/**
 * Get the URL of the OpenSearch description document.
 *
 * @return string The document URL.
 */
function get_document_url() {
	return \rest_url( 'opensearch/1.1/document' );
}

/**
 * Get the URL of the suggestions endpoint.
 *
 * @return string The suggestions URL, with the `{searchTerms}` placeholder.
 */
function get_suggestions_url() {
	return \rest_url( 'opensearch/1.1/suggestions?s={searchTerms}' );
}

/**
 * Get the long name of the search engine.
 *
 * @return string The long name.
 */
function get_long_name() {
	/* translators: %s: the site name */
	$long_name = \sprintf( \__( '%s Web Search', 'open-search-document' ), \get_bloginfo( 'name' ) );

	/**
	 * Filters the long name of the search engine.
	 *
	 * @param string $long_name The long name.
	 */
	return \apply_filters( 'osd_long_name', $long_name );
}

/**
 * Get the keywords for the `Tags` element of the document.
 *
 * The spec wants single words, separated by spaces and not longer than
 * 256 characters in total. Tag slugs are used because tag names may
 * contain spaces. Sites without tags fall back to `WordPress blog`.
 *
 * @return string The keywords, separated by spaces.
 */
function get_document_tags() {
	$tags = \get_tags(
		array(
			'orderby' => 'count',
			'order'   => 'DESC',
			'number'  => 10,
		)
	);

	$keywords = array();

	if ( \is_array( $tags ) ) {
		$keywords = \wp_list_pluck( $tags, 'slug' );
	}

	if ( empty( $keywords ) ) {
		$keywords = array( 'WordPress', 'blog' );
	}

	/**
	 * Filters the keywords for the `Tags` element of the document.
	 *
	 * @param string[] $keywords The keywords, one word each.
	 */
	$keywords = \apply_filters( 'osd_tags', $keywords );

	$result = '';

	foreach ( $keywords as $keyword ) {
		// Tags are single words, so spaces inside a keyword are not allowed.
		$keyword = \preg_replace( '/\s+/', '-', \trim( (string) $keyword ) );

		if ( '' === $keyword ) {
			continue;
		}

		$candidate = \trim( $result . ' ' . $keyword );

		if ( \mb_strlen( $candidate ) > 256 ) {
			break;
		}

		$result = $candidate;
	}

	return $result;
}

/**
 * Get the search URL template for a given response type.
 *
 * The returned template contains the literal `{searchTerms}` placeholder as
 * defined by the OpenSearch spec. It is not escaped, escape it for the
 * context you output it in.
 *
 * @param string $type The response type: `html`, `atom` or `rss2`.
 *
 * @return string|\WP_Error The URL template or a WP_Error for unknown types.
 */
function get_url_template( $type = 'html' ) {
	switch ( $type ) {
		case 'html':
			$url_template = \add_query_arg( 's', 'searchTerms', \home_url( '/' ) );
			break;
		case 'atom':
		case 'rss2':
			$url_template = \add_query_arg( 's', 'searchTerms', \get_bloginfo( "{$type}_url" ) );
			break;
		default:
			return new \WP_Error(
				'unsupported_type',
				\__( 'Unsupported search response type.', 'open-search-document' ),
				array( 'type' => $type )
			);
	}

	/**
	 * Filters the search URL template.
	 *
	 * The template uses the plain string `searchTerms` as placeholder, it is
	 * replaced by `{searchTerms}` after filtering.
	 *
	 * @since 4.0.0
	 *
	 * @param string $url_template The search URL template.
	 * @param string $type         The response type: `html`, `atom` or `rss2`.
	 */
	$url_template = \apply_filters( 'osd_search_url_template', $url_template, $type );

	// `esc_url_raw` strips curly braces, that's why the placeholder is added afterwards.
	$url_template = \esc_url_raw( $url_template );

	return \str_replace( 'searchTerms', '{searchTerms}', $url_template );
}

/**
 * Output the HTML search URL template.
 *
 * @deprecated 4.2.0 Use get_url_template( 'html' ) instead.
 *
 * @param bool $display Echo the result or return it.
 *
 * @return void|string
 */
function url_template( $display = true ) {
	\_deprecated_function( __FUNCTION__, '4.2.0', 'OpenSearchDocument\get_url_template' );

	$url_template = \esc_xml( get_url_template( 'html' ) );

	if ( $display ) {
		echo $url_template; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		return $url_template;
	}
}

/**
 * Output the feed search URL template.
 *
 * @deprecated 4.2.0 Use get_url_template( $feed ) instead.
 *
 * @param string $feed    The feed type: `atom` or `rss2`.
 * @param bool   $display Echo the result or return it.
 *
 * @return void|string|\WP_Error
 */
function feed_url_template( $feed, $display = true ) {
	\_deprecated_function( __FUNCTION__, '4.2.0', 'OpenSearchDocument\get_url_template' );

	if ( ! \in_array( $feed, array( 'atom', 'rss2' ), true ) ) {
		return new \WP_Error( 'unsupported_feed_type' );
	}

	$url_template = \esc_xml( get_url_template( $feed ) );

	if ( $display ) {
		echo $url_template; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		return $url_template;
	}
}
