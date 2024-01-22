<?php
echo '<?xml version="1.0" encoding="' . get_bloginfo( 'charset' ) . '"?>' . PHP_EOL;
?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/<?php do_action( 'osd_ns' ); ?>">
	<ShortName><?php bloginfo( 'name' ); ?></ShortName>
	<Description><?php bloginfo( 'description' ); ?></Description>
	<Url type="text/html" method="get" template="<?php \OpenSearchDocument\url_template(); ?>"></Url>
	<Url type="application/atom+xml" method="get" template="<?php \OpenSearchDocument\feed_url_template( 'atom' ); ?>" />
	<Url type="application/rss+xml" method="get" template="<?php \OpenSearchDocument\feed_url_template( 'rss2' ); ?>" />
	<Url type="application/x-suggestions+json" method="get" template="<?php echo rest_url( 'opensearch/1.1/suggestions?s={searchTerms}' ); ?>"/>
	<Contact><?php echo get_home_url(); ?></Contact>
	<LongName><?php bloginfo( 'name' ); ?> Web Search</LongName>
	<Tags>WordPress blog</Tags>
	<Query role="example" searchTerms="blog" />
	<Developer>Matthias Pfefferle</Developer>
	<Language><?php bloginfo( 'language' ); ?></Language>
	<OutputEncoding><?php bloginfo( 'charset' ); ?></OutputEncoding>
	<InputEncoding><?php bloginfo( 'charset' ); ?></InputEncoding>
	<?php do_action( 'osd_xml' ); ?>
</OpenSearchDescription>
