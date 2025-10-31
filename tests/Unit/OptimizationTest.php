<?php
/**
 * Tests for optimization and security functions
 *
 * @package HelloElementorChild\Tests\Unit
 */

namespace HelloElementorChild\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

/**
 * Class OptimizationTest
 */
class OptimizationTest extends TestCase
{
    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        
        // Load the optimization file
        require_once __DIR__ . '/../../hello-elementor-child/oom/oom-optimization-security.php';
    }

    /**
     * Tear down test environment
     */
    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Test remove_block_css function exists
     */
    public function test_remove_block_css_exists()
    {
        $this->assertTrue(function_exists('remove_block_css'));
    }

    /**
     * Test disable_emojis function exists
     */
    public function test_disable_emojis_exists()
    {
        $this->assertTrue(function_exists('disable_emojis'));
    }

    /**
     * Test disable_emojis_tinymce function exists
     */
    public function test_disable_emojis_tinymce_exists()
    {
        $this->assertTrue(function_exists('disable_emojis_tinymce'));
    }

    /**
     * Test disable_emojis_dns_prefetch function exists
     */
    public function test_disable_emojis_dns_prefetch_exists()
    {
        $this->assertTrue(function_exists('disable_emojis_dns_prefetch'));
    }
}

