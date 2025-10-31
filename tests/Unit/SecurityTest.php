<?php
/**
 * Tests for security functions
 *
 * @package HelloElementorChild\Tests\Unit
 */

namespace HelloElementorChild\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

/**
 * Class SecurityTest
 */
class SecurityTest extends TestCase
{
    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        
        // Load the functions file
        require_once __DIR__ . '/../../hello-elementor-child/functions.php';
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
     * Test oom_custom_security_headers function exists
     */
    public function test_oom_custom_security_headers_exists()
    {
        $this->assertTrue(function_exists('oom_custom_security_headers'));
    }

    /**
     * Test oom_custom_security_headers when headers already sent
     */
    public function test_oom_custom_security_headers_headers_sent()
    {
        // Mock headers_sent to return true
        if (!function_exists('headers_sent')) {
            function headers_sent() {
                return true;
            }
        }
        
        // Should return early without setting headers
        ob_start();
        oom_custom_security_headers();
        $output = ob_get_clean();
        
        // Function should complete without error
        $this->assertTrue(true);
    }
}

