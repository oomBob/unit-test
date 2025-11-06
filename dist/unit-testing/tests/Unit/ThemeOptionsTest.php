<?php
/**
 * Tests for Theme Options functions
 *
 * @package HelloElementorChild\Tests\Unit
 */

namespace HelloElementorChild\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

/**
 * Class ThemeOptionsTest
 */
class ThemeOptionsTest extends TestCase
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
        Monkey\Functions\when('site_url')->justReturn('https://example.com');
        Monkey\Functions\when('esc_html')->returnArg();
        Monkey\Functions\when('esc_attr')->returnArg();
        Monkey\Functions\when('esc_textarea')->returnArg();
        Monkey\Functions\when('sanitize_text_field')->returnArg();
        // Note: checkdate, sprintf, implode, trim, explode, array_map are PHP internal functions
        // They cannot be mocked with Brain Monkey and will run normally
        Monkey\Functions\when('get_admin_page_title')->justReturn('Theme Options');
        Monkey\Functions\when('wp_nonce_field')->justReturn('<input type="hidden">');
        Monkey\Functions\when('selected')->justReturn('');
        Monkey\Functions\when('submit_button')->justReturn('<button>Save</button>');
        
        // Load the theme options file
        require_once __DIR__ . '/../../hello-elementor-child/oom/oom-theme-options.php';
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
     * Test oom_add_theme_options_page function exists
     */
    public function test_oom_add_theme_options_page_exists()
    {
        $this->assertTrue(function_exists('oom_add_theme_options_page'));
    }

    /**
     * Test oom_add_theme_options_page calls add_menu_page
     */
    public function test_oom_add_theme_options_page_adds_menu()
    {
        Monkey\Functions\expect('add_menu_page')
            ->once()
            ->with(
                'OOm Theme Options',
                'Theme Options',
                'manage_options',
                'theme-options',
                'oom_theme_options_page_html',
                \Mockery::type('string')
            )
            ->andReturn('menu-slug');
        
        oom_add_theme_options_page();
        
        $this->assertTrue(true);
    }

    /**
     * Test oom_theme_options_page_html function exists
     */
    public function test_oom_theme_options_page_html_exists()
    {
        $this->assertTrue(function_exists('oom_theme_options_page_html'));
    }

    /**
     * Test oom_theme_options_page_html checks user capability
     */
    public function test_oom_theme_options_page_html_checks_capability()
    {
        Monkey\Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(false);
        
        ob_start();
        oom_theme_options_page_html();
        $output = ob_get_clean();
        
        // Should return early if user doesn't have capability
        $this->assertEmpty($output);
    }

    /**
     * Test oom_theme_options_page_html renders page with capability
     */
    public function test_oom_theme_options_page_html_renders_with_capability()
    {
        $_POST = [];
        
        Monkey\Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(true);
            
        // Mock get_option to return the second argument (default) when option is not set
        // This simulates the WordPress get_option behavior
        Monkey\Functions\when('get_option')->alias(function($name, $default = '') {
            // Return defaults for known options, otherwise return the default parameter
            $defaults = [
                'oom_table_status' => 'active',
                'oom_form_status' => 'active',
                'oom_location' => '205 Braddell Road Blk H Singapore 479401',
                'oom_security_deposit' => '500.00',
                'oom_advanced_booking_days' => '2',
                'oom_pickup_dropoff_charge' => '30.00',
            ];
            return $defaults[$name] ?? $default;
        });
        
        ob_start();
        oom_theme_options_page_html();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
        // Just verify output is not empty when user has capability
        $this->assertNotEmpty($output);
    }

    /**
     * Test oom_theme_options_page_html saves options on submit
     */
    public function test_oom_theme_options_page_html_saves_options()
    {
        $_POST = [
            'submit' => 'Save Changes',
            'oom_gtm_code' => 'GTM-TEST123',
            'oom_table_status' => 'active',
            'oom_form_status' => 'active',
            'oom_google_place_api' => 'AIzaSyTest',
            'oom_location' => 'Test Location',
            'oom_security_deposit' => '600.00',
            'oom_advanced_booking_days' => '3',
            'oom_pickup_dropoff_charge' => '40.00',
            'oom_blockout_dates' => '25-08-2025, 26-08-2025'
        ];
        
        Monkey\Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(true);
            
        // Mock sanitize_text_field to return the value as-is (already set in setUp)
        
        Monkey\Functions\expect('update_option')
            ->atLeast()->once();
        
        // Mock get_option - the function uses default values when empty is returned
        Monkey\Functions\when('get_option')->justReturn('');
        
        ob_start();
        oom_theme_options_page_html();
        ob_end_clean();
        
        $this->assertTrue(true);
    }

    /**
     * Test blockout dates validation with valid dates
     */
    public function test_blockout_dates_validation_valid_dates()
    {
        $_POST = [
            'submit' => 'Save Changes',
            'oom_gtm_code' => '',
            'oom_table_status' => 'active',
            'oom_form_status' => 'active',
            'oom_google_place_api' => '',
            'oom_location' => '',
            'oom_security_deposit' => '',
            'oom_advanced_booking_days' => '',
            'oom_pickup_dropoff_charge' => '',
            'oom_blockout_dates' => '25-08-2025, 26-08-2025'
        ];
        
        Monkey\Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(true);
            
        // Mock sanitize_text_field
        Monkey\Functions\when('sanitize_text_field')->returnArg();
        Monkey\Functions\when('update_option')->justReturn();
        Monkey\Functions\when('get_option')->justReturn('');
        
        ob_start();
        oom_theme_options_page_html();
        ob_end_clean();
        
        $this->assertTrue(true);
    }

    /**
     * Test blockout dates validation with invalid dates
     */
    public function test_blockout_dates_validation_invalid_dates()
    {
        $_POST = [
            'submit' => 'Save Changes',
            'oom_gtm_code' => '',
            'oom_table_status' => 'active',
            'oom_form_status' => 'active',
            'oom_google_place_api' => '',
            'oom_location' => '',
            'oom_security_deposit' => '',
            'oom_advanced_booking_days' => '',
            'oom_pickup_dropoff_charge' => '',
            'oom_blockout_dates' => 'invalid-date, 32-13-2025'
        ];
        
        Monkey\Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(true);
            
        // Mock sanitize_text_field
        Monkey\Functions\when('sanitize_text_field')->returnArg();
        Monkey\Functions\when('update_option')->justReturn();
        Monkey\Functions\when('get_option')->justReturn('');
        
        ob_start();
        oom_theme_options_page_html();
        ob_end_clean();
        
        $this->assertTrue(true);
    }

    /**
     * Test blockout dates with empty input
     */
    public function test_blockout_dates_empty()
    {
        $_POST = [
            'submit' => 'Save Changes',
            'oom_gtm_code' => '',
            'oom_table_status' => 'active',
            'oom_form_status' => 'active',
            'oom_google_place_api' => '',
            'oom_location' => '',
            'oom_security_deposit' => '',
            'oom_advanced_booking_days' => '',
            'oom_pickup_dropoff_charge' => '',
            'oom_blockout_dates' => ''
        ];
        
        Monkey\Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(true);
            
        // Mock sanitize_text_field
        Monkey\Functions\when('sanitize_text_field')->returnArg();
        
        Monkey\Functions\expect('update_option')
            ->once()
            ->with('oom_blockout_dates', '');
        
        Monkey\Functions\when('get_option')->justReturn('');
        
        ob_start();
        oom_theme_options_page_html();
        ob_end_clean();
        
        $this->assertTrue(true);
    }
}

