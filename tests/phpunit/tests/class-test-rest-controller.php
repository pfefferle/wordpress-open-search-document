<?php
/**
 * Test the REST controller.
 *
 * @package OpenSearchDocument
 */

namespace OpenSearchDocument\Tests;

/**
 * Test class for the REST controller.
 *
 * @coversDefaultClass \OpenSearchDocument\Rest_Controller
 */
class Test_Rest_Controller extends \WP_UnitTestCase {

	/**
	 * The REST server.
	 *
	 * @var \WP_REST_Server
	 */
	protected $server;

	/**
	 * Set up the REST server.
	 */
	public function set_up() {
		parent::set_up();

		global $wp_rest_server;
		$wp_rest_server = new \WP_REST_Server(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$this->server   = $wp_rest_server;

		\do_action( 'rest_api_init' );
	}

	/**
	 * Tear down the REST server.
	 */
	public function tear_down() {
		global $wp_rest_server;
		$wp_rest_server = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		parent::tear_down();
	}

	/**
	 * The routes are registered.
	 *
	 * @covers ::register_routes
	 */
	public function test_routes_are_registered() {
		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/opensearch/1.1/document', $routes );
		$this->assertArrayHasKey( '/opensearch/1.1/suggestions', $routes );
	}

	/**
	 * The document is served as XML.
	 *
	 * @covers ::get_document
	 */
	public function test_get_document() {
		$request  = new \WP_REST_Request( 'GET', '/opensearch/1.1/document' );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		$headers = $response->get_headers();
		$this->assertStringStartsWith( 'application/opensearchdescription+xml', $headers['Content-Type'] );
		$this->assertSame( '*', $headers['Access-Control-Allow-Origin'] );

		$xml = \simplexml_load_string( $response->get_data() );
		$this->assertNotFalse( $xml, 'The document is not valid XML.' );
		$this->assertSame( 'OpenSearchDescription', $xml->getName() );
		$this->assertSame( \get_bloginfo( 'name' ), (string) $xml->{'ShortName'} );

		$templates = array();
		$rels      = array();
		foreach ( $xml->{'Url'} as $url ) {
			$templates[ (string) $url['type'] ] = (string) $url['template'];
			$rels[ (string) $url['type'] ]      = (string) $url['rel'];
		}

		$this->assertSame( \home_url( '/?s={searchTerms}' ), $templates['text/html'] );
		$this->assertStringContainsString( '{searchTerms}', $templates['application/atom+xml'] );
		$this->assertStringContainsString( '{searchTerms}', $templates['application/rss+xml'] );
		$this->assertSame( \rest_url( 'opensearch/1.1/suggestions?s={searchTerms}' ), $templates['application/x-suggestions+json'] );
		$this->assertSame( 'suggestions', $rels['application/x-suggestions+json'] );
		$this->assertSame( \rest_url( 'opensearch/1.1/document' ), $templates['application/opensearchdescription+xml'] );
		$this->assertSame( 'self', $rels['application/opensearchdescription+xml'] );

		// Contact has to be an email address, so it is not part of the document.
		$this->assertCount( 0, $xml->{'Contact'} );
		$this->assertCount( 0, $xml->{'Developer'} );
	}

	/**
	 * The `rest_pre_serve_request` filter writes out the XML and claims the request.
	 *
	 * @covers ::serve_document
	 */
	public function test_serve_document() {
		$request  = new \WP_REST_Request( 'GET', '/opensearch/1.1/document' );
		$response = $this->server->dispatch( $request );

		\ob_start();
		$served = \apply_filters( 'rest_pre_serve_request', false, $response, $request, $this->server );
		$output = \ob_get_clean();

		$this->assertTrue( $served );
		$this->assertStringStartsWith( '<?xml', $output );
		$this->assertStringNotContainsString( 'null', $output );
	}

	/**
	 * The XML document escapes the site name.
	 *
	 * @covers ::get_document
	 */
	public function test_get_document_escapes_site_name() {
		\update_option( 'blogname', 'Tom & Jerry <3' );

		$request  = new \WP_REST_Request( 'GET', '/opensearch/1.1/document' );
		$response = $this->server->dispatch( $request );

		$xml = \simplexml_load_string( $response->get_data() );
		$this->assertNotFalse( $xml, 'The document is not valid XML.' );
		$this->assertSame( 'Tom & Jerry <3', (string) $xml->{'ShortName'} );
	}

	/**
	 * The search URL template can be filtered.
	 *
	 * @covers \OpenSearchDocument\get_url_template
	 */
	public function test_search_url_template_filter() {
		\add_filter(
			'osd_search_url_template',
			function ( $url, $type ) {
				return \add_query_arg( 'type', $type, $url );
			},
			10,
			2
		);

		$request  = new \WP_REST_Request( 'GET', '/opensearch/1.1/document' );
		$response = $this->server->dispatch( $request );

		$xml = \simplexml_load_string( $response->get_data() );
		$this->assertSame( \home_url( '/?s={searchTerms}&type=html' ), (string) $xml->{'Url'}[0]['template'] );
	}

	/**
	 * The `Tags` element falls back to a default without tags.
	 *
	 * @covers \OpenSearchDocument\get_document_tags
	 */
	public function test_document_tags_fallback() {
		$request  = new \WP_REST_Request( 'GET', '/opensearch/1.1/document' );
		$response = $this->server->dispatch( $request );

		$xml = \simplexml_load_string( $response->get_data() );
		$this->assertSame( 'WordPress blog', (string) $xml->{'Tags'} );
	}

	/**
	 * The `Tags` element lists the most used tag slugs, most used first.
	 *
	 * @covers \OpenSearchDocument\get_document_tags
	 */
	public function test_document_tags() {
		self::factory()->post->create( array( 'tags_input' => array( 'Word Games', 'Fediverse' ) ) );
		self::factory()->post->create( array( 'tags_input' => array( 'Fediverse' ) ) );
		self::factory()->tag->create( array( 'name' => 'Unused' ) );

		$request  = new \WP_REST_Request( 'GET', '/opensearch/1.1/document' );
		$response = $this->server->dispatch( $request );

		$xml = \simplexml_load_string( $response->get_data() );
		$this->assertSame( 'fediverse word-games', (string) $xml->{'Tags'} );
	}

	/**
	 * The `Tags` element can be filtered and is capped at 256 characters.
	 *
	 * @covers \OpenSearchDocument\get_document_tags
	 */
	public function test_document_tags_filter_and_limit() {
		\add_filter(
			'osd_tags',
			function () {
				return array( 'one', 'two words', '', \str_repeat( 'x', 250 ), 'never' );
			}
		);

		$tags = \OpenSearchDocument\get_document_tags();

		$this->assertSame( 'one two-words', $tags );
		$this->assertLessThanOrEqual( 256, \strlen( $tags ) );
	}

	/**
	 * Suggestions follow the OpenSearch Suggestions format.
	 *
	 * @covers ::get_suggestions
	 */
	public function test_get_suggestions() {
		self::factory()->post->create( array( 'tags_input' => array( 'WordPress', 'Word Games', 'Fediverse' ) ) );

		$request = new \WP_REST_Request( 'GET', '/opensearch/1.1/suggestions' );
		$request->set_param( 's', 'word' );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertSame( 'word', $data[0] );
		$this->assertEqualSets( array( 'WordPress', 'Word Games' ), $data[1] );
		$this->assertNotContains( 'Fediverse', $data[1] );
	}

	/**
	 * Tags without posts are not suggested.
	 *
	 * @covers ::get_suggestions
	 */
	public function test_get_suggestions_skips_empty_tags() {
		self::factory()->tag->create( array( 'name' => 'Wordless' ) );

		$request = new \WP_REST_Request( 'GET', '/opensearch/1.1/suggestions' );
		$request->set_param( 's', 'word' );
		$response = $this->server->dispatch( $request );

		$this->assertSame( array( 'word', array() ), $response->get_data() );
	}

	/**
	 * Suggestions keep spaces in the query.
	 *
	 * @covers ::get_suggestions
	 */
	public function test_get_suggestions_keeps_spaces() {
		self::factory()->post->create( array( 'tags_input' => array( 'Word Games' ) ) );

		$request = new \WP_REST_Request( 'GET', '/opensearch/1.1/suggestions' );
		$request->set_param( 's', 'word ga' );
		$response = $this->server->dispatch( $request );

		$data = $response->get_data();
		$this->assertSame( 'word ga', $data[0] );
		$this->assertSame( array( 'Word Games' ), $data[1] );
	}

	/**
	 * A missing query returns an error.
	 *
	 * @covers ::get_suggestions
	 */
	public function test_get_suggestions_requires_query() {
		$request  = new \WP_REST_Request( 'GET', '/opensearch/1.1/suggestions' );
		$response = $this->server->dispatch( $request );

		$this->assertSame( 400, $response->get_status() );
	}
}
