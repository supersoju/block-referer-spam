# Block Referer Spam

Blocks referer/referral spam from accessing your site and cleans up your Google Analytics in the process!

- **Contributors:** supersoju, codestic
- **Requires at least:** WordPress 3.0.2
- **Tested up to:** WordPress 7.0.3
- **Stable tag:** 1.1.9.5
- **License:** [GPLv2 or later](http://www.gnu.org/licenses/gpl-2.0.html)

## Description

**Block Referer Spam** aims at blocking all (or most) websites that use referer spam to promote their – often somewhat dodgy – website content. This is accomplished by bots that simulate human behavior. They do this so well, that they even show up in **Google Analytics**. This plugin does not require any special configuration after installation. Once active and auto-update is enabled, you will barely see any of those nasty spammers any more.

From [Wikipedia](https://en.wikipedia.org/wiki/Referer_spam):

> Referrer spam (also known as log spam or referrer bombing) is a kind of spamdexing (spamming aimed at search engines). The technique involves making repeated web site requests using a fake referer URL to the site the spammer wishes to advertise. Sites that publish their access logs, including referer statistics, will then inadvertently link back to the spammer's site. These links will be indexed by search engines as they crawl the access logs.
>
> This benefits the spammer because the free link improves the spammer site's search engine ranking owing to link-counting algorithms that search engines use.

### Features

- Automatic or manual updates of referer spam list
- Option of adding custom referer spam hosts
- Two methods of blocking: mod_rewrite or WordPress based

### Pro Features

Pro plans start at only $2/mo. Visit [BlockReferSpam.com](https://blockreferspam.com) for more information.

- Automatic syncing of your custom block lists across all of your sites
- Additional curated block lists

### Examples Blocked

- semalt
- buttons-for-website
- floating-share-buttons
- 4webmaster
- ilovevitaly
- ... and many more!

If you think you found a bug in Referer Spam Blocker, please [open an issue](https://github.com/supersoju/block-referer-spam/issues). Further, if you want to contribute, feel free!

Anything else, please get in touch: support / supersoju.com

Cover photo by [Lukas Budimaier](https://unsplash.com/@lukasbudimaier)

## Screenshots

1. Admin Interface

## Installation

To install Block Referer Spam and start cleaning up your Google Analytics:

1. Install Block Referer Spam automatically or by uploading the ZIP file.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Block Referer Spam is now activated. Go to the "Referer Spam" menu and start review your options.
4. You are now protected!

### Using WP-CLI

```sh
wp plugin install block-referer-spam --activate
```

## Frequently Asked Questions

**What sites are blocked?**

To give you the least amount of headache, this plugin is not using one, but indeed several sources of referer spam lists. Our servers merge multiple lists every couple hours to provide you with the best possible protection.

**I still see those websites in my statistics!**

This plugin will not remove existing Google Analytics Spam. What it will do is block further spam from being logged. You can however filter out those websites — a good tutorial for this is [here](https://megalytic.com/blog/how-to-filter-out-fake-referrals-and-other-google-analytics-spam).

**I tested my site and those referers can still access my site!**

This can be caused by three reasons:

1. The site is not blocked by our list. The list is updated multiple times a day (every 6 hours) and chances are the site will be on it very soon. If not, try custom blocks.
2. Some plugins interfere with the "Rewrite" block mode on server side level. Examples for these are caching plugins that may not always work. In this case, use the "WordPress" block mode instead.
3. While using the "Rewrite" block mode is faster, you may not be able to write to your server's `.htaccess` file, in this case please use the "WordPress" block mode instead.

**I found a bug!**

If you find a bug, please report it here. We will always aim to fix the issue within 48 hours.

## Changelog

See [`readme.txt`](readme.txt) for the full version history (WordPress.org reads that file directly).

