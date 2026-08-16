<?php

namespace WPBlockRefererSpam\Tests\Unit;

use Brain\Monkey;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use ReflectionClass;
use WPBlockRefererSpam\RefSpamBlocker;

abstract class TestCase extends PHPUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void
    {
        // Functions\expect()->once() etc. are verified by Mockery, not by a PHPUnit
        // assertion call — without this, tests that only use expect() are flagged
        // "risky: no assertions" even though the mock verification did run.
        $this->addToAssertionCount(\Mockery::getContainer()->mockery_getExpectationCount());

        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * The constructor registers WP hooks (register_activation_hook, add_action, ...) which
     * these unit tests have no need to stub — they exercise a single private method in
     * isolation, so skip the constructor entirely.
     */
    protected function makeBlocker(): RefSpamBlocker
    {
        $reflection = new ReflectionClass(RefSpamBlocker::class);

        return $reflection->newInstanceWithoutConstructor();
    }

    protected function callPrivateMethod(object $object, string $method, array $args = [])
    {
        $reflection = new ReflectionClass($object);
        $reflectionMethod = $reflection->getMethod($method);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs($object, $args);
    }
}
