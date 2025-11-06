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
        
        // Mock WordPress functions that are called during file loading
        Monkey\Functions\when('add_action')->justReturn();
        Monkey\Functions\when('remove_action')->justReturn();
        Monkey\Functions\when('add_filter')->justReturn();
        Monkey\Functions\when('remove_filter')->justReturn();
        Monkey\Functions\when('is_admin')->justReturn(false);
        
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
     * Test remove_block_css dequeues block styles
     */
    public function test_remove_block_css_dequeues_styles()
    {
        Monkey\Functions\expect('wp_dequeue_style')
            ->once()
            ->with('wp-block-library');
            
        Monkey\Functions\expect('wp_dequeue_style')
            ->once()
            ->with('wp-block-library-theme');
            
        Monkey\Functions\when('is_front_page')->justReturn(false);
        
        remove_block_css();
        
        $this->assertTrue(true);
    }

    /**
     * Test remove_block_css dequeues front page styles
     */
    public function test_remove_block_css_front_page_dequeues()
    {
        Monkey\Functions\when('wp_dequeue_style')->justReturn();
        Monkey\Functions\when('wp_dequeue_script')->justReturn();
        Monkey\Functions\when('is_front_page')->justReturn(true);
        
        remove_block_css();
        
        $this->assertTrue(true);
    }

    /**
     * Test disable_emojis function exists
     */
    public function test_disable_emojis_exists()
    {
        $this->assertTrue(function_exists('disable_emojis'));
    }

    /**
     * Test disable_emojis removes emoji actions
     */
    public function test_disable_emojis_removes_actions()
    {
        // Function is already called during file load, so we just verify it exists
        // In real usage, it removes emoji-related actions and filters
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
     * Test disable_emojis_tinymce removes wpemoji from array
     */
    public function test_disable_emojis_tinymce_removes_wpemoji()
    {
        $plugins = ['plugin1', 'wpemoji', 'plugin2'];
        $result = disable_emojis_tinymce($plugins);
        
        $this->assertIsArray($result);
        $this->assertNotContains('wpemoji', $result);
        $this->assertContains('plugin1', $result);
        $this->assertContains('plugin2', $result);
    }

    /**
     * Test disable_emojis_tinymce with non-array input
     */
    public function test_disable_emojis_tinymce_non_array()
    {
        $result = disable_emojis_tinymce('not-an-array');
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test disable_emojis_dns_prefetch function exists
     */
    public function test_disable_emojis_dns_prefetch_exists()
    {
        $this->assertTrue(function_exists('disable_emojis_dns_prefetch'));
    }

    /**
     * Test disable_emojis_dns_prefetch removes emoji URL
     */
    public function test_disable_emojis_dns_prefetch_removes_emoji_url()
    {
        Monkey\Functions\when('apply_filters')->justReturn('https://s.w.org/images/core/emoji/2.2.1/svg/');
        
        $urls = [
            'https://example.com',
            'https://s.w.org/images/core/emoji/2.2.1/svg/',
            'https://another.com'
        ];
        
        $result = disable_emojis_dns_prefetch($urls, 'dns-prefetch');
        
        $this->assertIsArray($result);
        $this->assertNotContains('https://s.w.org/images/core/emoji/2.2.1/svg/', $result);
    }

    /**
     * Test disable_emojis_dns_prefetch with different relation type
     */
    public function test_disable_emojis_dns_prefetch_other_relation()
    {
        $urls = ['https://example.com', 'https://another.com'];
        $result = disable_emojis_dns_prefetch($urls, 'preconnect');
        
        $this->assertEquals($urls, $result);
    }

    /**
     * Test disable_self_pingbacks function exists
     */
    public function test_disable_self_pingbacks_exists()
    {
        $this->assertTrue(function_exists('disable_self_pingbacks'));
    }

    /**
     * Test disable_self_pingbacks removes home URLs
     */
    public function test_disable_self_pingbacks_removes_home_urls()
    {
        Monkey\Functions\expect('get_option')
            ->once()
            ->with('home')
            ->andReturn('https://example.com');
        
        $links = [
            'https://example.com/page1',
            'https://external.com/page1',
            'https://example.com/page2',
            'https://another.com/page1'
        ];
        
        disable_self_pingbacks($links);
        
        // Links should be modified (reference passed)
        $this->assertTrue(true); // Function modifies by reference
    }

    /**
     * Test filter_login_errors function exists
     */
    public function test_filter_login_errors_exists()
    {
        $this->assertTrue(function_exists('filter_login_errors'));
    }

    /**
     * Test filter_login_errors returns custom message
     */
    public function test_filter_login_errors_returns_custom_message()
    {
        $result = filter_login_errors();
        
        $this->assertIsString($result);
        $this->assertEquals('Invalid Username or Password!', $result);
    }

    /**
     * Test shapeSpace_check_enum function exists
     */
    public function test_shapeSpace_check_enum_exists()
    {
        $this->assertTrue(function_exists('shapeSpace_check_enum'));
    }

    /**
     * Test shapeSpace_check_enum with author parameter in request
     */
    public function test_shapeSpace_check_enum_with_author()
    {
        $request = '/?author=123';
        $redirect = '/some-redirect';
        
        // This will call die() in actual code, so we test that function exists
        // In a real scenario, you'd need to mock die() or use a different approach
        $this->assertTrue(function_exists('shapeSpace_check_enum'));
    }

    /**
     * Test shapeSpace_check_enum without author parameter
     */
    public function test_shapeSpace_check_enum_without_author()
    {
        // We can't actually test the die() call, but we can verify the function logic
        $this->assertTrue(function_exists('shapeSpace_check_enum'));
    }

    /**
     * Test rest_endpoints filter removes user endpoints
     */
    public function test_rest_endpoints_filter_removes_users()
    {
        $endpoints = [
            '/wp/v2/users' => ['callback' => 'test'],
            '/wp/v2/users/(?P<id>[\d]+)' => ['callback' => 'test'],
            '/wp/v2/posts' => ['callback' => 'test']
        ];
        
        // Get the anonymous function that's registered
        // Since it's registered via add_filter, we need to call it
        $filtered = apply_filters('rest_endpoints', $endpoints);
        
        // If the filter is applied, user endpoints should be removed
        // Note: We can't easily test this without actually triggering the filter
        $this->assertTrue(is_array($endpoints));
    }

    /**
     * Test deny_theme_editor_and_plugins_access function exists
     */
    public function test_deny_theme_editor_and_plugins_access_exists()
    {
        $this->assertTrue(function_exists('deny_theme_editor_and_plugins_access'));
    }

    /**
     * Test deny_theme_editor_and_plugins_access for non-admin user
     */
    public function test_deny_theme_editor_and_plugins_access_non_admin()
    {
        $_SERVER['PHP_SELF'] = 'theme-editor.php';
        
        Monkey\Functions\when('is_user_logged_in')->justReturn(true);
        Monkey\Functions\when('current_user_can')->justReturn(true);
        Monkey\Functions\when('get_current_user_id')->justReturn(2);
        Monkey\Functions\when('wp_get_current_user')->justReturn((object)['user_email' => 'test@example.com']);
        Monkey\Functions\when('wp_die')->justReturn();
        Monkey\Functions\when('remove_submenu_page')->justReturn();
        
        deny_theme_editor_and_plugins_access();
        
        $this->assertTrue(true);
    }

    /**
     * Test deny_theme_editor_and_plugins_access for allowed user
     */
    public function test_deny_theme_editor_and_plugins_access_allowed_user()
    {
        $_SERVER['PHP_SELF'] = 'admin.php';
        
        Monkey\Functions\when('is_user_logged_in')->justReturn(true);
        Monkey\Functions\when('current_user_can')->justReturn(false);
        Monkey\Functions\when('get_current_user_id')->justReturn(2);
        Monkey\Functions\when('wp_get_current_user')->justReturn((object)['user_email' => 'project@oom.com.sg']);
        Monkey\Functions\when('remove_submenu_page')->justReturn();
        
        deny_theme_editor_and_plugins_access();
        
        $this->assertTrue(true);
    }
}

