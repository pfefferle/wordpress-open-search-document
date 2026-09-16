<?php
/**
 * OpenSearch REST Controller.
 *
 * @package OpenSearchDocument
 */

namespace OpenSearchDocument;

/**
 * Serves the OpenSearch description document and the suggestions endpoint.
 */
class Rest_Controller extends \WP_REST_Controller {

	/**
	 * The namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'opensearch/1.1';

	/**
	 * Register the routes.
	 */
	public function register_routes() {
		// The REST server serializes responses as JSON, the document needs to be served as XML.
		\add_filter( 'rest_pre_serve_request', array( $this, 'serve_document' ), 9, 3 );

		\register_rest_route(
			$this->namespace,
			'/document',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_document' ),
					'permission_callback' => '__return_true',
				),
			)
		);

		\register_rest_route(
			$this->namespace,
			'/suggestions',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_suggestions' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						's' => array(
							'description'       => \__( 'The search term to get suggestions for.', 'open-search-document' ),
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
			)
		);
	}

	/**
	 * Build the OpenSearch description document.
	 *
	 * The REST API serializes responses as JSON, so the XML is rendered here
	 * and written out by `serve_document()`.
	 *
	 * @return \WP_REST_Response The response, with the XML document as data.
	 */
	public function get_document() {
		\ob_start();
		\load_template( OPEN_SEARCH_DOCUMENT_PLUGIN_DIR . 'templates/open-search-document.php', false );
		$xml = \ob_get_clean();

		$response = new \WP_REST_Response( $xml );
		$response->header( 'Access-Control-Allow-Origin', '*' );
		$response->header( 'Content-Type', \sprintf( 'application/opensearchdescription+xml; charset=%s', \get_bloginfo( 'charset' ) ) );

		return $response;
	}

	/**
	 * Write out the XML document instead of the JSON encoded response.
	 *
	 * @param bool              $served  Whether the request has already been served.
	 * @param \WP_HTTP_Response $result  The response.
	 * @param \WP_REST_Request  $request The request.
	 *
	 * @return bool Whether the request has been served.
	 */
	public function serve_document( $served, $result, $request ) {
		if ( $served ) {
			return $served;
		}

		if ( '/' . $this->namespace . '/document' !== $request->get_route() ) {
			return $served;
		}

		if ( 'GET' !== $request->get_method() ) {
			return $served;
		}

		if ( 200 !== $result->get_status() ) {
			return $served;
		}

		echo $result->get_data(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		return true;
	}

	/**
	 * Return search suggestions in the OpenSearch Suggestions format.
	 *
	 * The format is a JSON array with the query as first item and a list of
	 * completions as second item. Suggestions are taken from the tag names.
	 *
	 * @param \WP_REST_Request $request The request.
	 *
	 * @return \WP_REST_Response|\WP_Error The response.
	 */
	public function get_suggestions( $request ) {
		$query = $request->get_param( 's' );

		if ( empty( $query ) ) {
			return new \WP_Error(
				'osd_missing_query',
				\__( 'Missing search query.', 'open-search-document' ),
				array( 'status' => 400 )
			);
		}

		$completions = array();

		$tags = \get_tags(
			array(
				'search' => $query,
				'number' => 10,
			)
		);

		if ( \is_array( $tags ) ) {
			$completions = \wp_list_pluck( $tags, 'name' );
		}

		$suggestions = array( $query, \array_values( $completions ) );

		/**
		 * Filters the search suggestions.
		 *
		 * @param array  $suggestions The suggestions, an array with the query and the completions.
		 * @param string $query       The search query.
		 */
		$suggestions = \apply_filters( 'open_search_document_suggestions', $suggestions, $query );

		$response = new \WP_REST_Response( $suggestions );
		$response->header( 'Access-Control-Allow-Origin', '*' );

		return $response;
	}
}
