<?php

namespace WPBlockRefererSpam\Tests\Unit;

use Brain\Monkey\Functions;

final class GetListTest extends TestCase
{
    public function test_merges_and_dedupes_internal_and_custom_lists(): void
    {
        Functions\when('wp_cache_get')->justReturn(false);
        Functions\when('wp_cache_set')->justReturn(true);
        Functions\when('get_option')->alias(function (string $option) {
            if ($option === 'ref-blocker-list') {
                return "semalt.com\nbuttons-for-website.com\nspam.com";
            }

            if ($option === 'ref-spam-custom-blocks') {
                return "custom-spam.com\nspam.com"; // "spam.com" duplicates the internal list
            }

            return false;
        });

        $blocker = $this->makeBlocker();
        $list = $this->callPrivateMethod($blocker, 'getList');

        $this->assertSame(
            ['semalt.com', 'buttons-for-website.com', 'spam.com', 'custom-spam.com'],
            array_values($list)
        );
    }

    public function test_ignores_blank_lines_from_either_list(): void
    {
        Functions\when('wp_cache_get')->justReturn(false);
        Functions\when('wp_cache_set')->justReturn(true);
        Functions\when('get_option')->alias(function (string $option) {
            if ($option === 'ref-blocker-list') {
                return "semalt.com\n\n\ncustom-block.com";
            }

            return '';
        });

        $blocker = $this->makeBlocker();
        $list = $this->callPrivateMethod($blocker, 'getList');

        $this->assertSame(['semalt.com', 'custom-block.com'], array_values($list));
    }

    public function test_returns_the_cached_value_without_reparsing_options(): void
    {
        Functions\when('wp_cache_get')->justReturn(['already-cached.com']);
        // If getList() ignored the cache and fell through to get_option(), this test
        // would need get_option stubbed too — its absence here is the assertion.

        $blocker = $this->makeBlocker();
        $list = $this->callPrivateMethod($blocker, 'getList');

        $this->assertSame(['already-cached.com'], $list);
    }
}
