=== Open Search ===
Contributors: pfefferle, johnnoone
Tags: open search, opensearch, open search document, osd, search
Requires at least: 4.6
Tested up to: 6.4
Stable tag: 4.0.1

Create an OpenSearch Document for your blog.

== description ==

Create an OpenSearch Document for your blog.

The plugin supports Google Chromes "[Tab to Search](https://www.chromium.org/tab-to-search)", Firefox' "[OpenSearch plugins](https://developer.mozilla.org/de/docs/OpenSearch_Plugin_f%C3%BCr_Firefox_erstellen)", Safaris "[Quick Website Search](https://developer.apple.com/library/content/releasenotes/General/WhatsNewInSafari/Articles/Safari_8_0.html)", and [custom searches](https://support.microsoft.com/de-de/instantanswers/390c87f8-911e-47a3-adca-c80a1e4076ca/change-the-default-search-engine-in-microsoft-edge) for Microsofts Edge browser.

From the [spec](http://www.opensearch.org/Specifications/OpenSearch/1.1):

> Search clients can use OpenSearch description documents to learn about the public interface of a search engine. These description documents contain parameterized URL templates that indicate how the search client should make search requests. Search engines can use the OpenSearch response elements to add search metadata to results in a variety of content formats.

The plugin includes:

* Extension links for [HTML](http://www.opensearch.org/Specifications/OpenSearch/1.1#Autodiscovery_in_HTML.2FXHTML), [Atom and RSS](http://www.opensearch.org/Specifications/OpenSearch/1.1#Autodiscovery_in_RSS.2FAtom)
* Autodiscovery via [XRDS-Simple](http://wordpress.org/plugins/xrds-simple/), [host-meta](http://wordpress.org/plugins/host-meta/) and [WebFinger](http://wordpress.org/plugins/webfinger/)
* [RSS and Atom search responses](http://www.opensearch.org/Specifications/OpenSearch/1.1#Examples_of_OpenSearch_responses)
* [OpenSearch Suggestions extension](http://www.opensearch.org/Specifications/OpenSearch/Extensions/Suggestions/1.0)

== Screenshots ==

1. Firefox OpenSearch plugins
2. Safari Quick Website Search
3. Chrome Tab to Search

== Frequently Asked Questions ==

= Chromes "Tab to Search" no longer works =

Chromes "Tab to Search" is now an opt-in feature. Go to `chrome://settings/searchEngines`, search for your Website and press the "Activate" button.

= How to add query params to the Search-URLs =

You can add custom params to the search URL using the `osd_search_url_template` filter.

    function custom_osd_extend( $url, $type ) {
	    $url = add_query_arg( 'mtm_campaign', 'opensearch', $url );

	    return $url;
    }
    add_filter( 'osd_search_url_template', 'custom_osd_extend', 10, 2 );

== Changelog ==

= 4.0.1 =
* fix broken XML output

= 4.0.0 =
* modernize code
* added filters for the search URLs in the OSD document

= 3.0.3 =
* fix missing permission callback

= 3.0.2 =
* update requirements

= 3.0.1 =
* added screenshots
* code improvements

= 3.0.0 =
* moved to WordPress API

= 2.1.2 =
* fixed site icon implementation

= 2.1.1 =
* fixed site icon implementation

= 2.1.0 =
* fixed XML output
* encapsulated XML data

= 2.0.0 =
* complete refactoring
* WordPress coding style
* Site icon support

= 1.3.1 =
* Some smaller fixes

= 1.3 =
* fixed host-meta link
* added webfinger support

= 1.2.2 =
* Added function to flush rewrite_rules

= 1.2.1 =
* Autodiscovery for host-meta

= 1.2 =
* OpenSearch Suggestions extension

= 1.1 =
* WordPress 2.8.x compatibility
* Autodiscovery for RSS/Atom and XRDS
* Profile-Services

= 1.0 =
* Initial release

== Installation ==

1. Upload `open-search-document`-folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the *Plugins* menu in WordPress
3. that's it :)
