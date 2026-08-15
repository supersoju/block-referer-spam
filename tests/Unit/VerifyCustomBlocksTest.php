<?php

namespace WPBlockRefererSpam\Tests\Unit;

use Brain\Monkey\Functions;

final class VerifyCustomBlocksTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // wp_parse_url() is a thin wrapper around parse_url() for these inputs — no
        // WordPress-specific behavior (relative URLs, IDN) is exercised by these tests.
        Functions\when('wp_parse_url')->alias('parse_url');
        Functions\when('__')->returnArg(1);
        Functions\when('wp_cache_delete')->justReturn(true);
    }

    private function stubOptions(string $internalList, string $customBlocks): void
    {
        Functions\when('get_option')->alias(function (string $option) use ($internalList, $customBlocks) {
            if ($option === 'ref-blocker-list') {
                return $internalList;
            }

            if ($option === 'ref-spam-custom-blocks') {
                return $customBlocks;
            }

            return false;
        });
    }

    public function test_valid_custom_domain_is_kept_and_saved(): void
    {
        $this->stubOptions('semalt.com', 'some-dodgy-site.com');

        Functions\expect('add_settings_error')->never();
        Functions\expect('update_option')
            ->once()
            ->with('ref-spam-custom-blocks', 'some-dodgy-site.com');

        $blocker = $this->makeBlocker();
        $this->callPrivateMethod($blocker, 'verifyCustomBlocks');
    }

    public function test_custom_domain_already_in_internal_list_is_dropped_as_duplicate(): void
    {
        $this->stubOptions('semalt.com', "semalt.com\nnew-block.com");

        Functions\expect('add_settings_error')
            ->once()
            ->with('ref-spam-custom-blocks', 'redundant_hosts', \Mockery::type('string'), 'error');
        Functions\expect('update_option')
            ->once()
            ->with('ref-spam-custom-blocks', 'new-block.com');

        $blocker = $this->makeBlocker();
        $this->callPrivateMethod($blocker, 'verifyCustomBlocks');
    }

    public function test_invalid_entry_is_dropped_with_an_error(): void
    {
        $this->stubOptions('', 'not a valid host!!');

        Functions\expect('add_settings_error')
            ->once()
            ->with('ref-spam-custom-blocks', 'invalid_hosts', \Mockery::type('string'), 'error');
        Functions\expect('update_option')
            ->once()
            ->with('ref-spam-custom-blocks', '');

        $blocker = $this->makeBlocker();
        $this->callPrivateMethod($blocker, 'verifyCustomBlocks');
    }
}
