<?php
/**
 * OpenSearch description document.
 *
 * See https://github.com/dewitt/opensearch/blob/master/opensearch-1-1-draft-6.md
 *
 * @package OpenSearchDocument
 */

echo '<?xml version="1.0" encoding="' . \esc_attr( \get_bloginfo( 'charset' ) ) . '"?>' . PHP_EOL;
?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/"<?php \do_action( 'osd_ns' ); ?>>
	<ShortName><?php echo \esc_xml( \get_bloginfo( 'name' ) ); ?></ShortName>
	<Description><?php echo \esc_xml( \get_bloginfo( 'description' ) ); ?></Description>
	<Url type="text/html" method="get" template="<?php echo \esc_xml( \OpenSearchDocument\get_url_template( 'html' ) ); ?>" />
	<Url type="application/atom+xml" method="get" template="<?php echo \esc_xml( \OpenSearchDocument\get_url_template( 'atom' ) ); ?>" />
	<Url type="application/rss+xml" method="get" template="<?php echo \esc_xml( \OpenSearchDocument\get_url_template( 'rss2' ) ); ?>" />
	<Url type="application/x-suggestions+json" rel="suggestions" method="get" template="<?php echo \esc_xml( \OpenSearchDocument\get_suggestions_url() ); ?>" />
	<Url type="application/opensearchdescription+xml" rel="self" template="<?php echo \esc_xml( \OpenSearchDocument\get_document_url() ); ?>" />
	<LongName><?php echo \esc_xml( \OpenSearchDocument\get_long_name() ); ?></LongName>
	<Tags><?php echo \esc_xml( \OpenSearchDocument\get_document_tags() ); ?></Tags>
	<Query role="example" searchTerms="blog" />
	<Language><?php echo \esc_xml( \get_bloginfo( 'language' ) ); ?></Language>
	<OutputEncoding><?php echo \esc_xml( \get_bloginfo( 'charset' ) ); ?></OutputEncoding>
	<InputEncoding><?php echo \esc_xml( \get_bloginfo( 'charset' ) ); ?></InputEncoding>
<?php \do_action( 'osd_xml' ); ?>
</OpenSearchDescription>
