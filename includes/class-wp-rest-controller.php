<?php

namespace OpenSearchDocument;

use WP_Error;
use WP_REST_Server;

/**
 * Class WP_REST_Controller
 */
class WP_REST_Controller {
	/**
	 * Initialize class.
	 */
	public static function init() {
		// Configure the REST API route.
		add_action( 'rest_api_init', array( static::class, 'register_routes' ) );
		// Filter the REST API response to output XML if requested.
		// Filter the response to allow plaintext
		add_filter( 'rest_pre_serve_request', array( static::class, 'serve_request' ), 9, 4 );
	}

	/**
	 * Register the API routes.
	 */
	public static function register_routes() {
		register_rest_route(
			'opensearch/1.1',
			'/document',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( static::class, 'get_document' ),
					'permission_callback' => '__return_true',
				),
			)
		);

		register_rest_route(
			'opensearch/1.1',
			'/suggestions',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( static::class, 'get_suggestions' ),
					'args'                => array(
						's' => array(
							'sanitize_callback' => 'sanitize_key',
						),
					),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * Hooks into the REST API output to output alternatives to JSON.
	 *
	 * @access private
	 * @since 0.1.0
	 *
	 * @param bool                      $served  Whether the request has already been served.
	 * @param WP_HTTP_ResponseInterface $result  Result to send to the client. Usually a WP_REST_Response.
	 * @param WP_REST_Request           $request Request used to generate the response.
	 * @param WP_REST_Server            $server  Server instance.
	 * @return true
	 */
	public static function serve_request( $served, $result, $request, $server ) {
		if ( '/opensearch/1.1/document' !== $request->get_route() ) {
			return $served;
		}
		if ( 'GET' !== $request->get_method() ) {
			return $served;
		}
		// If someone tries to poll the webmention endpoint return a webmention form.
		if ( ! headers_sent() ) {
			header( 'Access-Control-Allow-Origin: *' );
			header( sprintf( 'Content-Type: application/opensearchdescription+xml; charset=%s', get_bloginfo( 'charset' ) ), true );
		}

		load_template( dirname( __FILE__ ) . '/../templates/open-search-document.php' );

		return;
	}

	/**
	 * Callback for our API endpoint.
	 *
	 * Returns the JSON object for the post.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public static function get_document( $request ) {
		return;
	}

	/**
	 * Callback for our API endpoint.
	 *
	 * Returns the JSON object for the post.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public static function get_suggestions( $request ) {
		$tags        = array();
		$suggestions = array();

		if ( ! isset( $request['s'] ) ) {
			return new WP_Error( 'no_query', __( 'Missing search query', 'open-search-document' ), array( 'status' => 400 ) );
		}

		if ( ! headers_sent() ) {
			header( 'Access-Control-Allow-Origin: *' );
			header( sprintf( 'Content-Type: application/json; charset=%s', get_bloginfo( 'charset' ) ), true );
		}

		foreach ( get_tags( 'search=' . $request['s'] ) as $tag ) {
			$tags[] = $tag->name;
		}

		$suggestions[] = $request['s'];
		$suggestions[] = $tags;

		return apply_filters( 'open_search_document_suggestions', $suggestions, $request['s'] );
	}
}
