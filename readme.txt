=== Open Search ===
Contributors: pfefferle, johnnoone
Tags: open search, opensearch, open search document, osd, search
Requires at least: 4.6
Tested up to: 4.6.2
Stable tag: 3.0.0

Create an OpenSearch Document for your blog.

== description ==

Create an OpenSearch Document for your blog.

From the [spec](http://www.opensearch.org/Specifications/OpenSearch/1.1):

> Search clients can use OpenSearch description documents to learn about the
> public interface of a search engine. These description documents contain
> parameterized URL templates that indicate how the search client should make
> search requests. Search engines can use the OpenSearch response elements to
> add search metadata to results in a variety of content formats.

The plugin supports:

* Extension links for [HTML](http://www.opensearch.org/Specifications/OpenSearch/1.1#Autodiscovery_in_HTML.2FXHTML), [Atom and RSS](http://www.opensearch.org/Specifications/OpenSearch/1.1#Autodiscovery_in_RSS.2FAtom)
* Autodiscovery via [XRDS-Simple](http://wordpress.org/extend/plugins/xrds-simple/), [host-meta](http://wordpress.org/extend/plugins/host-meta/) and [webfinger](http://wordpress.org/extend/plugins/webfinger/)
* [RSS and Atom search responses](http://www.opensearch.org/Specifications/OpenSearch/1.1#Examples_of_OpenSearch_responses)
* [OpenSearch Suggestions extension](http://www.opensearch.org/Specifications/OpenSearch/Extensions/Suggestions/1.0)

== Changelog ==

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
