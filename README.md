# Block Referer Spam

Blocks referer/referral spam bots from accessing your site and keeps them out of your analytics.

- **Contributors:** supersoju, codestic
- **Requires at least:** WordPress 3.0.2
- **Tested up to:** WordPress 7.0.3
- **Stable tag:** 1.2
- **License:** [GPLv2 or later](http://www.gnu.org/licenses/gpl-2.0.html)

## Description

**Block Referer Spam** stops the bots that fake an `HTTP_REFERER` header to advertise their own site in your traffic logs and analytics. They're good enough at simulating real visits that they show up in **Google Analytics** right alongside genuine traffic — this plugin blocks the known offenders before they're ever logged, and needs no configuration to start working.

Two blocking modes cover different hosting setups:

- **Rewrite mode** (Apache only) — adds `RewriteCond` rules to `.htaccess` so spam requests are rejected by the web server itself, before WordPress even loads. Fastest option, and the default when your host is detected as Apache.
- **WordPress mode** — checks the referer during normal WordPress request handling and returns a 403. Works on any host, including nginx, but can't intercept a request that's served entirely from a full-page cache (see the FAQ below) — the plugin will warn you on its settings page if it detects an active caching plugin while running in this mode.

The block list itself is a mix of several public spam-referer sources, merged and refreshed automatically once a day (or on demand from the settings page). You can also add your own domains to block, including internationalized (non-ASCII) domain names — they're normalized automatically.

From [Wikipedia](https://en.wikipedia.org/wiki/Referer_spam):

> Referrer spam (also known as log spam or referrer bombing) is a kind of spamdexing (spamming aimed at search engines). The technique involves making repeated web site requests using a fake referer URL to the site the spammer wishes to advertise. Sites that publish their access logs, including referer statistics, will then inadvertently link back to the spammer's site. These links will be indexed by search engines as they crawl the access logs.
>
> This benefits the spammer because the free link improves the spammer site's search engine ranking owing to link-counting algorithms that search engines use.

### Features

- Automatic daily updates of the referer spam block list, or trigger an update manually
- Add your own custom domains to the block list, with automatic IDN/Punycode normalization
- Two blocking modes to fit Apache or non-Apache hosting: mod_rewrite or WordPress-level
- Warns you if a caching plugin is likely to interfere with WordPress-mode blocking

### Pro

Pro plans start at $2/mo. Visit [BlockReferSpam.com](https://blockreferspam.com) for more information.

- Sync your custom block list across all of your registered sites
- Access to additional curated block lists

### Examples Blocked

- semalt
- buttons-for-website
- floating-share-buttons
- 4webmaster
- ilovevitaly
- ... and many more!

Found a bug, or want to contribute? [Open an issue](https://github.com/supersoju/block-referer-spam/issues), or get in touch: support / supersoju.com

Cover photo by [Lukas Budimaier](https://unsplash.com/@lukasbudimaier)

## Installation

1. Install Block Referer Spam automatically, or upload the plugin ZIP file.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Go to the "Referer Spam" admin menu to review your blocking mode and options — the defaults work for most sites without any changes.

### Using WP-CLI

```sh
wp plugin install block-referer-spam --activate
```

## Frequently Asked Questions

**What sites are blocked?**

This plugin merges several public referer-spam block lists rather than relying on just one, refreshed automatically once a day, to keep coverage broad.

**I still see those websites in my Google Analytics history!**

Block Referer Spam prevents *new* spam hits from being logged — it doesn't retroactively clean up analytics data you already have. To filter out existing spam entries in Google Analytics, see [this tutorial](https://megalytic.com/blog/how-to-filter-out-fake-referrals-and-other-google-analytics-spam).

**I tested my site and a known spam referer can still get through!**

A few common causes:

1. **The site isn't on the block list yet.** The list refreshes once a day, so very new spam domains may take a little while to be added. In the meantime, add it as a custom block yourself.
2. **A caching plugin is serving the request from cache.** In WordPress blocking mode, a full-page cache (e.g. WP Rocket, W3 Total Cache, WP Super Cache, LiteSpeed Cache) can serve a cached page before WordPress — and this plugin — ever runs. Switch to Rewrite mode (Apache only) if you're using a caching plugin, since it blocks at the web-server level before caching applies.
3. **Your host doesn't support Rewrite mode.** If `.htaccess` isn't writable, or you're not on Apache, use WordPress mode instead.

**Does this work with caching plugins?**

Rewrite mode does, since it blocks before caching applies. WordPress mode can be bypassed by full-page caching — the plugin detects common caching plugins and shows a warning on its settings page if you're running WordPress mode alongside one, so you can switch to Rewrite mode if your host supports it.

**I found a bug!**

Please report it — we aim to fix reported issues quickly.

## Screenshots

1. Admin Interface

## Development

This repo carries dev-only tooling that never ships to WordPress.org (`vendor/`, `tests/`, `.gitea/` — see `deploy.sh`):

```sh
composer install       # phpcs + WPCS, PHPUnit, Brain\Monkey
composer run lint      # WPCS, scoped to security/correctness sniffs
composer run test      # PHPUnit
```

CI (`.gitea/workflows/lint.yml`) runs a PHP syntax check, the WPCS lint (on changed files only), and PHPUnit on every pull request against `master`.

## Changelog

See [`readme.txt`](readme.txt) for the full version history (WordPress.org reads that file directly).
