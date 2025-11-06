<?php
/**
 * Tests for functions in functions.php
 *
 * @package HelloElementorChild\Tests\Unit
 */

namespace HelloElementorChild\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

/**
 * Class FunctionsTest
 */
class FunctionsTest extends TestCase
{
    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        
        // Mock is_admin() before loading functions.php since it's called in oom-optimization-security.php
        Monkey\Functions\when('is_admin')->justReturn(false);
        
        // Mock other WordPress functions that may be called during file loading
        Monkey\Functions\when('add_action')->justReturn();
        Monkey\Functions\when('add_filter')->justReturn();
        Monkey\Functions\when('add_shortcode')->justReturn();
        Monkey\Functions\when('get_option')->justReturn('');
        
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
     * Test wpb_set_post_views with empty count
     */
    public function test_wpb_set_post_views_empty_count()
    {
        $post_id = 1;
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with($post_id, 'wpb_post_views_count', true)
            ->andReturn('');
            
        Monkey\Functions\expect('delete_post_meta')
            ->once()
            ->with($post_id, 'wpb_post_views_count');
            
        Monkey\Functions\expect('add_post_meta')
            ->once()
            ->with($post_id, 'wpb_post_views_count', '1')
            ->andReturn(true);

        wpb_set_post_views($post_id);
        
        $this->assertTrue(true);
    }

    /**
     * Test wpb_set_post_views with existing count
     */
    public function test_wpb_set_post_views_existing_count()
    {
        $post_id = 1;
        $existing_count = 5;
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with($post_id, 'wpb_post_views_count', true)
            ->andReturn($existing_count);
            
        Monkey\Functions\expect('update_post_meta')
            ->once()
            ->with($post_id, 'wpb_post_views_count', 6);

        wpb_set_post_views($post_id);
        
        $this->assertTrue(true);
    }

    /**
     * Test wpb_get_post_views with empty count
     */
    public function test_wpb_get_post_views_empty_count()
    {
        $post_id = 1;
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with($post_id, 'wpb_post_views_count', true)
            ->andReturn('');

        $result = wpb_get_post_views($post_id);
        
        $this->assertEquals('Views', $result);
    }

    /**
     * Test wpb_get_post_views with existing count
     */
    public function test_wpb_get_post_views_with_count()
    {
        $post_id = 1;
        $count = 10;
        
        // Mock is_admin() as it may be called during test execution
        Monkey\Functions\when('is_admin')->justReturn(false);
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with($post_id, 'wpb_post_views_count', true)
            ->andReturn($count);

        $result = wpb_get_post_views($post_id);
        
        $this->assertEquals('10 Views', $result);
    }

    /**
     * Test OOM_THEME_VERSION constant
     */
    public function test_theme_version_constant()
    {
        $this->assertTrue(defined('OOM_THEME_VERSION'));
        $this->assertEquals('1.6.0', OOM_THEME_VERSION);
    }
}

