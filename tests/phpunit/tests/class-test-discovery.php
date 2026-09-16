<?php
/**
 * Test the discovery links.
 *
 * @package OpenSearchDocument
 */

namespace OpenSearchDocument\Tests;

/**
 * Test class for the discovery links.
 *
 * @coversDefaultClass \OpenSearchDocument\Discovery
 */
class Test_Discovery extends \WP_UnitTestCase {

	/**
	 * The HTML head link is escaped and points to the document.
	 *
	 * @covers ::add_head
	 */
	public function test_add_head() {
		\update_option( 'blogname', 'Tom & Jerry' );

		$output = \get_echo( array( \OpenSearchDocument\Discovery::class, 'add_head' ) );

		$this->assertStringContainsString( 'rel="search"', $output );
		$this->assertStringContainsString( 'type="application/opensearchdescription+xml"', $output );
		$this->assertStringContainsString( 'title="Search Tom &amp; Jerry"', $output );
		$this->assertStringContainsString( 'href="' . \esc_url( \rest_url( 'opensearch/1.1/document' ) ) . '"', $output );
	}

	/**
	 * The XRD link is added to host-meta and WebFinger.
	 *
	 * @covers ::add_xrd_links
	 */
	public function test_add_xrd_links() {
		$xrd = \OpenSearchDocument\Discovery::add_xrd_links( array() );

		$this->assertCount( 1, $xrd['links'] );
		$this->assertSame( 'http://a9.com/-/spec/opensearch/1.1/', $xrd['links'][0]['rel'] );
		$this->assertSame( \rest_url( 'opensearch/1.1/document' ), $xrd['links'][0]['href'] );
	}

	/**
	 * The web app manifest gets a search provider.
	 *
	 * @covers ::web_app_manifest
	 */
	public function test_web_app_manifest() {
		$manifest = \OpenSearchDocument\Discovery::web_app_manifest( array() );

		$provider = $manifest['chrome_settings_overrides']['search_provider'];
		$this->assertSame( \get_bloginfo( 'name' ), $provider['name'] );
		$this->assertSame( \home_url( '/?s={searchTerms}' ), $provider['search_url'] );
	}

	/**
	 * The site icon is added to the document with its MIME type.
	 *
	 * @covers ::osd_xml
	 */
	public function test_osd_xml() {
		$this->assertSame( '', \get_echo( array( \OpenSearchDocument\Discovery::class, 'osd_xml' ) ) );

		// An attachment record is enough, the file itself is never read.
		$attachment_id = self::factory()->attachment->create(
			array(
				'post_mime_type' => 'image/png',
				'file'           => 'icon.png',
			)
		);
		\update_option( 'site_icon', $attachment_id );

		$output = \get_echo( array( \OpenSearchDocument\Discovery::class, 'osd_xml' ) );

		$this->assertSame( 3, \substr_count( $output, '<Image ' ) );
		$this->assertStringContainsString( '<Image height="16" width="16" type="image/png">', $output );
		$this->assertStringContainsString( \get_site_icon_url( 64 ), $output );

		\delete_option( 'site_icon' );
	}

	/**
	 * The icon sizes are registered once.
	 *
	 * @covers ::site_icon_image_sizes
	 */
	public function test_site_icon_image_sizes() {
		$sizes = \OpenSearchDocument\Discovery::site_icon_image_sizes( array( 32, 270 ) );

		$this->assertEqualSets( array( 16, 32, 64, 270 ), $sizes );
	}
}
