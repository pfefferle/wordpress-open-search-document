<?php
/*
 Plugin Name: Open Search Document Maker
 Plugin URI: http://wordpress.org/extend/plugins/open-search-document/
 Description: Create an Open Search Document for your blog.
 Version: 1.3
 Author: XBA, pfefferle
 Author URI: http://wordpress.org/extend/plugins/open-search-document/
 */

/*  Copyright 2006  XBA, Matthias Pfefferle

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// register
if (isset($wp_version)) {
  add_filter('query_vars', array('OpenSearchDocument', 'add_query_vars'));
  add_filter('generate_rewrite_rules', array('OpenSearchDocument', 'rewrite_add_rule'));
  add_action('parse_query', array('OpenSearchDocument', 'execute_request'));
  add_action('wp_head', array('OpenSearchDocument', 'display_in_header'));
  add_filter('xrds_simple', array('OpenSearchDocument', 'xrds_simple'));
  add_filter('host_meta', array('OpenSearchDocument', 'add_xrd'));
  add_filter('webfinger', array('OpenSearchDocument', 'add_xrd'));
  register_activation_hook(__FILE__, array('OpenSearchDocument', 'activation_hook'));
  
  // add feed autodiscovery
  add_action('atom_ns', array('OpenSearchDocument', 'atom_namespace'));
  add_action('atom_head', array('OpenSearchDocument', 'display_in_atom_header'));
  add_action('rss2_head', array('OpenSearchDocument', 'display_in_rss_header'));
}

/**
 * open search document for wordpress
 *
 * @author Matthias Pfefferle
 * @author XBA
 */
class OpenSearchDocument {

  /**
   * add open-search query var
   *
   * @param array $vars query vars
   * @return array updated query vars
   */
  function add_query_vars( $vars ) {
    $vars[] = 'opensearch';
    $vars[] = 'opensearch-suggestions';
    return $vars;
  }
  
  /**
   * activation hook
   */
  function activation_hook() {
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
  }

  /**
   * add some open-search rewrite rules
   *
   * @param array $wp_rewrite array of rewrite rules
   */
  function rewrite_add_rule( $wp_rewrite ) {
    global $wp_rewrite;
    $new_rules = array(
      'osd.xml'    => 'index.php?opensearch=true',
      'opensearch.xml' => 'index.php?opensearch=true',
      'opensearch'   => 'index.php?opensearch=true',
    );

    $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
  }

  /**
   * 
   *
   */
  function execute_request() {
    global $wp;

    if( array_key_exists('opensearch', $wp->query_vars) ) {
      self::print_xml();
    } else if ( array_key_exists('opensearch-suggestions', $wp->query_vars) ) {
      $tags = array();
      $output = array();
      foreach (get_tags('search='.$wp->query_vars['opensearch-suggestions']) as $tag) {
        $tags[] = $tag->name;
      }
      
      $output[] = $wp->query_vars['opensearch-suggestions'];
      $output[] = $tags;
      
      header("Access-Control-Allow-Origin: *");
      header("Content-Type: application/json; charset=" . get_bloginfo('charset'), true);
      echo json_encode($output);
      exit;
    }
  }
  
  /**
   * function to render the open-search document
   *
   */
  function print_xml() {
    //header('Content-Type: application/opensearchdescription+xml');
    header('Content-Type: text/xml');
    header('Encoding: '.get_option('blog_charset'));
    echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?>';
  ?>
  <OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/<?php do_action("osd_ns"); ?>">
    <ShortName><?php bloginfo('name'); ?></ShortName>
    <Description><?php bloginfo('description'); ?></Description>
    <Url type="text/html" method="get" template="<?php echo site_url("/?s={searchTerms}"); ?>"></Url>
    <Url type="application/atom+xml" template="<?php echo add_query_arg( 's', '{searchTerms}', bloginfo('atom_url') ); ?>" />
    <Url type="application/rss+xml" template="<?php echo add_query_arg( 's', '{searchTerms}', bloginfo('rss2_url') ); ?>" />
    <Url type="application/x-suggestions+json" template="<?php echo site_url("/?opensearch-suggestions={searchTerms}"); ?>"/>
    <Contact><?php bloginfo('admin_email'); ?></Contact>
    <LongName>Search through <?php bloginfo('name'); ?></LongName>
    <Tags>wordpress blog</Tags>
    <Query role="example" searchTerms="blog" />
    <Developer>XSD, Matthias Pfefferle</Developer>
    <Language><?php bloginfo('language'); ?></Language>
    <OutputEncoding><?php echo get_option('blog_charset'); ?></OutputEncoding>
    <InputEncoding><?php bloginfo('charset'); ?></InputEncoding>
    <?php do_action("osd_xml"); ?>
  </OpenSearchDescription>
  <?php
    exit;
  }

  /**
   * contribute the open-search autodiscovery-header
   *
   */
  function display_in_header() {
    echo '<link rel="search" type="application/opensearchdescription+xml" title="'. get_bloginfo('name') .'" href="'.site_url("/?opensearch=true").'" />'."\n";
  }

  /**
   * contribute the open-search atom-autodiscovery header
   *
   */
  function display_in_atom_header() {
    echo '<link rel="search" href="'.site_url("/?opensearch=true").'" type="application/opensearchdescription+xml" title="Content Search" />';
  }

  /**
   * contribute the open-search rss-autodiscovery header
   *
   */
  function display_in_rss_header() {
    echo '<atom:link rel="search" href="'.site_url("/?opensearch=true").'" type="application/opensearchdescription+xml" title="Content Search" />';
  }

  /**
   * Contribute the open-search atom-namespace
   *
   */
  function atom_namespace() {
    echo ' xmlns:opensearch="http://a9.com/-/spec/opensearch/1.1/" '."\n";
  }

  /**
   * Contribute the open-search rss-namespace
   *
   */
  function rss_namespace() {
    echo ' xmlns:atom="http://www.w3.org/2005/Atom" '."\n";
  }

  /**
   * contribute the OpenSearch to XRDS-Simple.
   *
   * @param array $xrds current XRDS-Simple array
   * @return array updated XRDS-Simple array
   */
  function xrds_simple($xrds) {
    $xrds = xrds_add_service($xrds, 'main', 'OpenSearchDocument',
      array(
        'Type' => array( array('content' => 'http://a9.com/-/spec/opensearch/1.1/') ),
        'MediaType' => array( array('content' => 'application/opensearchdescription+xml') ),
        'URI' => array( array('content' => site_url("/?opensearch=true")) )
      )
    );
    return $xrds;
  }
  
  /**
   * add the host meta information
   */
  function add_xrd($array) {     
    $array["links"][] = array("rel" => "http://a9.com/-/spec/opensearch/1.1/", "href" => site_url("/?opensearch=true"), "type" => "application/opensearchdescription+xml");

    return $array;
  }
}
?>