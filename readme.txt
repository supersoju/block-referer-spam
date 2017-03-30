=== Block Referer Spam ===
Contributors: supersoju, codestic
Tags: spam, referer, semalt, buttons-for-website, floating-share-buttons, 4webmaster, ilovevitaly, referal, referral, analytics, analytics spam, referer spam, referrer spam, referal spam, referral spam, anti referer, anti referrer, anti referral, block analytics, anti-spam, spambot, spam-bot, spam bot, bot block, google spam, seo spam, referer attack, referral attack, referer blockieren, referrer blockieren, spam blockieren, bot filter, spam attack
Requires at least: 3.0.2
Tested up to: 4.7.2
Stable tag: 1.1.9.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Blocks referer/referral spam from accessing your site and cleans up your Google Analytics in the process!

== Description ==

__Block Referer Spam__ aims at blocking all (or most) websites that use Referer Spam to promote their – often somewhat dodgy – website content. This is accomplished by bots that simulate human behavior. They do this so well, that they even show up in __Google Analytics__. This plugin does not require any special configuration after installation. Once active and auto-update is enabled, you will barely see any of those nasty spammers any more.

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

__Features__

* Automatic or manual updates of referer spam list
* Option of adding custom referer spam hosts
* Two methods of blocking: mod_rewrite or WordPress based

__Pro Features__

Pro plans start at only $2/mo. Visit [BlockReferSpam.com](https://blockreferspam.com) for more information.

* Automatic syncing your of custom block lists across all of your sites
* Additional curated block lists

__Examples Blocked__

* semalt
* buttons-for-website
* floating-share-buttons
* 4webmaster
* ilovevitaly
* ... and many more!

If you think you found a bug in Referer Spam Blocker, please contact us! Further, if you want to contribute, feel free!

Anything else, please get in touch!

support / supersoju.com

Cover photo by [Lukas Budimaier](https://unsplash.com/@lukasbudimaier)

== Screenshots ==

1. Admin Interface

== Installation ==

To install Block Referer Spam and start cleaning up your Google Analytics:

1. Install Block Referer Spam automatically or by uploading the ZIP file.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Block Referer Spam is now activated. Go to the "Referer Spam" menu and start review your options.
4. You are now protected!

__Using WP-CLI__
`wp plugin install block-referer-spam --activate`

== Frequently Asked Questions ==

= What sites are blocked? =

To give you the least amount of headache, this plugin is not using one, but indeed several sources of referer spam lists. Our servers merge multiple lists every couple hours to provide you with the best possible protection.

= I still see those websites in my statistics! =

This plugin will not remove existing Google Analytics Spam. What it will do is block further spam from being logged. You can however filter out those websites, a good tutorial for this is here: https://megalytic.com/blog/how-to-filter-out-fake-referrals-and-other-google-analytics-spam.

= I tested my site and those referers can still access my site! =

This can by caused by three reasons.

1. The site is not blocked by our list. The list is updated multiple times a day (every 6 hours) and chances are the site will be on it very soon. If not, try custom blocks.
2. Some plugins interfere with the "Rewrite" block mode on server side level. Examples for these are caching plugins that may not always work. In this case, use the "WordPress" block mode instead.
3. While using the "Rewrite" block mode is faster, you may not be able to write to your servers .htaccess file, in this case please use the "WordPress" block mode instead.

= I found a bug! =

If you find a bug, please report it here. We will always aim to fix the issue within 48 hours.

== Changelog ==

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
