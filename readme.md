# Open Search Document

- Contributors: pfefferle, johnnoone
- Donate link: https://notiz.blog/donate/
- Tags: opensearch, search, browser, autodiscovery, suggestions
- Requires at least: 6.4
- Tested up to: 7.1
- Stable tag: 4.2.0
- Requires PHP: 7.4
- License: GPL-2.0-or-later
- License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lets browsers search your site straight from the address bar.

## Description

Browsers can learn how to search a website if the site tells them. This plugin adds that information to your WordPress site, in the form of an [OpenSearch](https://github.com/dewitt/opensearch/blob/master/opensearch-1-1-draft-6.md) description document.

Once activated, visitors can add your site as a search engine in their browser, or the browser picks it up on its own. Typing a keyword and a search term into the address bar then searches your site instead of the web.

There is nothing to configure. Install it, activate it, done.

### What visitors get

* **Chrome and Edge**: your site shows up under "Site search" in the browser settings. After activating it there, visitors type your domain, hit `Tab` and search your site. Chrome calls this ["Tab to Search"](https://www.chromium.org/tab-to-search/).
* **Firefox**: the address bar offers to add your site as a search engine. Visitors can also right click the address bar and pick "Add" ([OpenSearch in Firefox](https://developer.mozilla.org/en-US/docs/Web/XML/Guides/OpenSearch)).
* **Safari**: with ["Quick Website Search"](https://developer.apple.com/library/archive/releasenotes/General/WhatsNewInSafari/Articles/Safari_8_0.html) enabled, Safari remembers your site after the first search and offers it in the address bar.
* **Search suggestions**: while typing, the browser can show suggestions. The plugin uses your tags for that.
* **Your site icon** is used as the icon of the search engine, if you have one set under *Appearance > Customize > Site Identity*.

### What the plugin adds

* The OpenSearch description document at `/wp-json/opensearch/1.1/document`
* A suggestions endpoint at `/wp-json/opensearch/1.1/suggestions?s=…`
* Autodiscovery links in the [HTML head](https://github.com/dewitt/opensearch/blob/master/opensearch-1-1-draft-6.md#autodiscovery-in-htmlxhtml) and in the [Atom and RSS feeds](https://github.com/dewitt/opensearch/blob/master/opensearch-1-1-draft-6.md#autodiscovery-in-rssatom)
* A search provider entry in the web app manifest, for the ["search_provider" WebExtension](https://developer.mozilla.org/en-US/docs/Mozilla/Add-ons/WebExtensions/manifest.json/chrome_settings_overrides) setting
* Links in [host-meta](https://wordpress.org/plugins/host-meta/) and [WebFinger](https://wordpress.org/plugins/webfinger/), if those plugins are installed
* Search URLs for the [Atom and RSS search responses](https://github.com/dewitt/opensearch/blob/master/opensearch-1-1-draft-6.md#examples-of-opensearch-responses) and the [OpenSearch Suggestions extension](https://github.com/dewitt/opensearch/blob/master/mediawiki/Specifications/OpenSearch/Extensions/Suggestions/1.1/Draft%201.wiki)

## Screenshots

1. Firefox OpenSearch plugins
2. Safari Quick Website Search
3. Chrome Tab to Search

## Frequently Asked Questions

### How do I check that it works?

Open `https://yoursite.com/wp-json/opensearch/1.1/document` in your browser. You should see a short XML file with the name of your site and the search URLs.

### Does it change the search on my site?

No. The plugin only describes the search you already have. The browser sends visitors to your normal search results page.

### Chrome does not offer my site for "Tab to Search"

Chrome no longer adds sites on its own. Go to `chrome://settings/searchEngines`, look for your site under "Inactive shortcuts" and activate it. Edge works the same way under `edge://settings/searchEngines`.

### Where do the suggestions come from?

From your tags. Other plugins can change that with the `open_search_document_suggestions` filter.

### Can I add query params to the search URLs?

Yes, with the `osd_search_url_template` filter. The second argument tells you which search URL is filtered: `html`, `atom` or `rss2`.

    function custom_osd_extend( $url, $type ) {
        return add_query_arg( 'mtm_campaign', 'opensearch', $url );
    }
    add_filter( 'osd_search_url_template', 'custom_osd_extend', 10, 2 );

### What are the tags in the document?

The `Tags` element lists the ten most used tags of your site, as keywords for the search engine. Sites without tags get `WordPress blog`. The `osd_tags` filter lets you change the list.

### Can I add more elements to the document?

Yes. The `osd_xml` action runs inside the document, right before the closing tag. The `osd_ns` action lets you add namespaces to the root element.

## Changelog

### 4.2.0

* Requires WordPress 6.4 and PHP 7.4
* Search suggestions keep spaces and upper case letters in the query
* The `Tags` element lists the most used tags of the site
* Site icons carry their MIME type
* The suggestions URL has `rel="suggestions"` and the document links to itself with `rel="self"`
* Removed the `Contact` element, the spec expects an email address there
* Removed the `Developer` element
* Removed the XRDS-Simple integration, the plugin is closed on WordPress.org
* Fixed a stray `null` at the end of the document
* Site name, description and URLs are escaped for XML
* The REST controller extends the WordPress `WP_REST_Controller` class
* New filters: `osd_tags` and `osd_long_name`
* `url_template()` and `feed_url_template()` are deprecated, use `get_url_template()`
* Added tests, CI and a new readme

### 4.1.3

* update plugin structure

### 4.1.1

* fix PHP deprecated: strstr(): Passing null to parameter

### 4.1.0

* added ["search_provider" WebExtension](https://developer.mozilla.org/en-US/docs/Mozilla/Add-ons/WebExtensions/manifest.json/chrome_settings_overrides) support

### 4.0.1

* fix broken XML output

### 4.0.0

* modernize code
* added filters for the search URLs in the OSD document

### 3.0.3

* fix missing permission callback

### 3.0.2

* update requirements

### 3.0.1

* added screenshots
* code improvements

### 3.0.0

* moved to WordPress API

### 2.1.2

* fixed site icon implementation

### 2.1.1

* fixed site icon implementation

### 2.1.0

* fixed XML output
* encapsulated XML data

### 2.0.0

* complete refactoring
* WordPress coding style
* Site icon support

### 1.3.1

* Some smaller fixes

### 1.3

* fixed host-meta link
* added webfinger support

### 1.2.2

* Added function to flush rewrite_rules

### 1.2.1

* Autodiscovery for host-meta

### 1.2

* OpenSearch Suggestions extension

### 1.1

* WordPress 2.8.x compatibility
* Autodiscovery for RSS/Atom and XRDS
* Profile-Services

### 1.0

* Initial release

## Upgrade Notice

### 4.2.0

This version requires WordPress 6.4 or higher. Older sites will not be offered the update. The document no longer contains the `Contact` and `Developer` elements, and the XRDS-Simple integration is removed.

## Installation

1. Install the plugin from the WordPress plugin directory, or upload the `open-search-document` folder to `/wp-content/plugins/`
2. Activate it through the *Plugins* menu
3. That's it, there are no settings
