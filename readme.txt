=== Block Referer Spam ===
Contributors: supersoju, codestic
Tags: spam, referer spam, referrer spam, referral spam, analytics spam, semalt, anti-spam, spambot, bot block, security
Requires at least: 3.0.2
Tested up to: 7.0.3
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Blocks referer/referral spam bots from accessing your site and keeps them out of your analytics.

== Description ==

**Block Referer Spam** stops the bots that fake an `HTTP_REFERER` header to advertise their own site in your traffic logs and analytics. They're good enough at simulating real visits that they show up in **Google Analytics** right alongside genuine traffic — this plugin blocks the known offenders before they're ever logged, and needs no configuration to start working.

Two blocking modes cover different hosting setups:

* **Rewrite mode** (Apache only) — adds `RewriteCond` rules to `.htaccess` so spam requests are rejected by the web server itself, before WordPress even loads. Fastest option, and the default when your host is detected as Apache.
* **WordPress mode** — checks the referer during normal WordPress request handling and returns a 403. Works on any host, including nginx, but can't intercept a request that's served entirely from a full-page cache (see the FAQ below) — the plugin will warn you on its settings page if it detects an active caching plugin while running in this mode.

The block list itself is a mix of several public spam-referer sources, merged and refreshed automatically once a day (or on demand from the settings page). You can also add your own domains to block, including internationalized (non-ASCII) domain names — they're normalized automatically.

From [Wikipedia](https://en.wikipedia.org/wiki/Referer_spam):

`Referrer spam (also known as log spam or referrer
bombing) is a kind of spamdexing (spamming aimed
at search engines). The technique involves making
repeated web site requests using a fake referer URL
to the site the spammer wishes to advertise. Sites that
publish their access logs, including referer statistics,
will then inadvertently link back to the spammer's site.
These links will be indexed by search engines
as they crawl the access logs.

This benefits the spammer because the free link improves
the spammer site's search engine ranking owing
to link-counting algorithms that search engines use.`

= Features =

* Automatic daily updates of the referer spam block list, or trigger an update manually
* Add your own custom domains to the block list, with automatic IDN/Punycode normalization
* Two blocking modes to fit Apache or non-Apache hosting: mod_rewrite or WordPress-level
* Warns you if a caching plugin is likely to interfere with WordPress-mode blocking

= Pro =

Pro plans start at $2/mo. Visit [BlockReferSpam.com](https://blockreferspam.com) for more information.

* Sync your custom block list across all of your registered sites
* Access to additional curated block lists

= Examples Blocked =

* semalt
* buttons-for-website
* floating-share-buttons
* 4webmaster
* ilovevitaly
* ... and many more!

Found a bug, or want to contribute? Get in touch — support / supersoju.com

Cover photo by [Lukas Budimaier](https://unsplash.com/@lukasbudimaier)

== Installation ==

1. Install Block Referer Spam automatically, or upload the plugin ZIP file.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Go to the "Referer Spam" admin menu to review your blocking mode and options — the defaults work for most sites without any changes.

= Using WP-CLI =

`wp plugin install block-referer-spam --activate`

== Frequently Asked Questions ==

= What sites are blocked? =

This plugin merges several public referer-spam block lists rather than relying on just one, refreshed automatically once a day, to keep coverage broad.

= I still see those websites in my Google Analytics history! =

Block Referer Spam prevents *new* spam hits from being logged — it doesn't retroactively clean up analytics data you already have. To filter out existing spam entries in Google Analytics, see [this tutorial](https://megalytic.com/blog/how-to-filter-out-fake-referrals-and-other-google-analytics-spam).

= I tested my site and a known spam referer can still get through! =

A few common causes:

1. **The site isn't on the block list yet.** The list refreshes once a day, so very new spam domains may take a little while to be added. In the meantime, add it as a custom block yourself.
2. **A caching plugin is serving the request from cache.** In WordPress blocking mode, a full-page cache (e.g. WP Rocket, W3 Total Cache, WP Super Cache, LiteSpeed Cache) can serve a cached page before WordPress — and this plugin — ever runs. Switch to Rewrite mode (Apache only) if you're using a caching plugin, since it blocks at the web-server level before caching applies.
3. **Your host doesn't support Rewrite mode.** If `.htaccess` isn't writable, or you're not on Apache, use WordPress mode instead.

= Does this work with caching plugins? =

Rewrite mode does, since it blocks before caching applies. WordPress mode can be bypassed by full-page caching — the plugin detects common caching plugins and shows a warning on its settings page if you're running WordPress mode alongside one, so you can switch to Rewrite mode if your host supports it.

= I found a bug! =

Please report it — we aim to fix reported issues quickly.

== Screenshots ==

1. Admin Interface

== Changelog ==

= 1.2 =
* Security: added a CSRF nonce check to the manual list-download action
* Security: enabled TLS certificate verification on the pro license request
* Security: fixed stored XSS in the Blocked Sites list and the pro key status message
* Security: replaced a raw PHP stream request with the WordPress HTTP API for list downloads
* Fix: flash messages no longer rely on PHP sessions (which were never actually started)
* Fix: uninstall now removes all plugin options, `.htaccess` rules, and the scheduled cron event
* Fix: scheduled cron event is now cleared on deactivation, instead of left running
* Fix: daily cron now respects the Auto Update setting instead of ignoring it
* Fix: PHP notice when no HTTP referer is present on the request
* Fix: removed unused/dead code left over from the bundled IDN library that failed to parse on PHP 8
* Improvement: `.htaccess` rewrite rules are kept ahead of caching-plugin rules, so Rewrite mode still blocks spam when a caching plugin is active
* Improvement: new admin notice when WordPress Block mode is active alongside a known caching plugin
* Improvement: the merged block list is now cached instead of re-parsed on every request
* Improvement: custom-block domain normalization now uses PHP's native IDN support when available, only falling back to the bundled library on hosts without it
* Tested up to WordPress 7.0.3

= 1.1.9.3 =
* Tested for WordPress 5.0
* Fix issue where updated list sometimes does not get written to .htaccess when called via cron

= 1.1.9.1 =
* Cleans up after itself on deactivation

= 1.1.9 =
* Pro version available

= 1.1.8.5 =
* Readme typo fixes
* Blocklist back to using custom solution

= 1.1.7 =
* Readme typo fixes
* Blocklist back to using custom solution

= 1.1.6 =
* Updated blocklist provider

= 1.1.5 =
* Added functionality to backup previous .htaccess in case something goes wrong.

= 1.1.4 =
* Tested successfully on WordPress latest version (4.4).
* Fixed a bug where other plugins could prevent Block Referer Spam from working.
* Added box to honor contributors of this plugin.

= 1.1.3 =
* Added Spanish translations!
* If you would like to help and translate this plugin, get in touch!

= 1.1.2 =
* Bugfix! Sorry guys! :)

= 1.1 =
* Major update!
* Fixed some critical bugs that could break the site.
* Fixed some more bugs that could have produced notices.
* Added validation and support for international domains!
* Added cloud-feature to learn from all installations worldwide!
* Fixed some mistakes in the German translation.

= 1.0.4 =
* Fixed the plugin to run on PHP versions lower than 5.4. It should now work on older providers and servers that have not been updated for a while.

= 1.0.3 =
* Fixed a bug that in "Rewrite Blocking", rules were not actually enforced. Sorry about that!

= 1.0.2 =
* Improved FAQs
* Added writable check for .htaccess
* Added WP-CLI installation instructions
* Added part of Wikipedia about referer spam

= 1.0.1 =
* Added German localization
* Updated screenshot and fixed typos
* Added frequently asked questions

= 1.0 =
* Initial version
