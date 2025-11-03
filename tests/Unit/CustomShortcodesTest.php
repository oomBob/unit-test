<?php
/**
 * Tests for custom shortcode functions in oom-custom-shortcode.php
 *
 * @package HelloElementorChild\Tests\Unit
 */

namespace HelloElementorChild\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Mockery;

/**
 * Class CustomShortcodesTest
 */
class CustomShortcodesTest extends TestCase
{
    private static $fileLoaded = false;
    private static $shortcodes = [];
    
    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        
        // Define ABSPATH before any WordPress functions check it
        if (!defined('ABSPATH')) {
            define('ABSPATH', '/fake/wordpress/path/');
        }
        
        // Mock WordPress functions and capture shortcode registrations
        Monkey\Functions\when('add_shortcode')->alias(function($tag, $callback) {
            self::$shortcodes[$tag] = $callback;
        });
        Monkey\Functions\when('add_action')->justReturn();
        Monkey\Functions\when('register_rest_route')->justReturn();
        // Mock only escaping and utility functions globally
        Monkey\Functions\when('site_url')->returnArg();
        Monkey\Functions\when('esc_url')->returnArg();
        Monkey\Functions\when('esc_attr')->returnArg();
        Monkey\Functions\when('esc_html')->returnArg();
        Monkey\Functions\when('esc_js')->returnArg();
        Monkey\Functions\when('esc_textarea')->returnArg();
        Monkey\Functions\when('get_bloginfo')->returnArg();
        Monkey\Functions\when('get_site_url')->justReturn('https://example.com');
        Monkey\Functions\when('get_stylesheet_directory_uri')->justReturn('https://example.com/wp-content/themes/hello-elementor-child');
        Monkey\Functions\when('wp_remote_get')->justReturn([]);
        Monkey\Functions\when('is_wp_error')->justReturn(false);
        Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        Monkey\Functions\when('wp_remote_retrieve_body')->justReturn('{}');
        Monkey\Functions\when('get_posts')->justReturn([]);
        
        // Load the shortcode file only once
        if (!self::$fileLoaded) {
            require_once __DIR__ . '/../../hello-elementor-child/oom/oom-custom-shortcode.php';
            self::$fileLoaded = true;
        }
    }

    /**
     * Tear down test environment
     */
    protected function tearDown(): void
    {
        Mockery::close();
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Test current_year_shortcode function exists
     */
    public function test_current_year_shortcode_function_exists()
    {
        $this->assertTrue(function_exists('current_year_shortcode'));
    }

    /**
     * Test current_year_shortcode returns current year
     */
    public function test_current_year_shortcode_returns_current_year()
    {
        $result = current_year_shortcode();
        $current_year = date('Y');
        
        $this->assertEquals($current_year, $result);
        $this->assertIsString($result);
        $this->assertEquals(4, strlen($result));
    }

    /**
     * Test current_year_shortcode returns numeric string
     */
    public function test_current_year_shortcode_returns_numeric()
    {
        $result = current_year_shortcode();
        
        $this->assertIsNumeric($result);
        $this->assertGreaterThanOrEqual(2020, (int)$result);
    }

    /**
     * Test current_year_shortcode with different years
     */
    public function test_current_year_shortcode_format()
    {
        $result = current_year_shortcode();
        
        // Should be exactly 4 digits
        $this->assertMatchesRegularExpression('/^\d{4}$/', $result);
    }

    /**
     * Test oom_custom_menu_shortcode function exists
     */
    public function test_oom_custom_menu_shortcode_function_exists()
    {
        $this->assertTrue(function_exists('oom_custom_menu_shortcode'));
    }

    /**
     * Test oom_custom_menu_shortcode with no id returns empty
     */
    public function test_oom_custom_menu_shortcode_no_id_returns_empty()
    {
        // Mock shortcode_atts
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->andReturn(['id' => '']);
        
        $result = oom_custom_menu_shortcode([]);
        
        $this->assertEquals('', $result);
    }

    /**
     * Test oom_custom_menu_shortcode with valid id
     */
    public function test_oom_custom_menu_shortcode_with_valid_id()
    {
        $menu_html = '<ul class="oom-shortcode-menu"><li>Menu Item</li></ul>';
        
        // Mock shortcode_atts
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->andReturn(['id' => '5']);
        
        // Mock wp_nav_menu
        Monkey\Functions\expect('wp_nav_menu')
            ->once()
            ->with(Mockery::on(function($args) {
                return $args['menu'] === 5 
                    && $args['echo'] === false 
                    && $args['container'] === false
                    && $args['menu_class'] === 'oom-shortcode-menu';
            }))
            ->andReturn($menu_html);
        
        $result = oom_custom_menu_shortcode(['id' => '5']);
        
        $this->assertEquals($menu_html, $result);
        $this->assertStringContainsString('oom-shortcode-menu', $result);
    }

    /**
     * Test oom_custom_menu_shortcode when menu returns false
     */
    public function test_oom_custom_menu_shortcode_menu_not_found()
    {
        // Mock shortcode_atts
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->andReturn(['id' => '999']);
        
        // Mock wp_nav_menu returns false for invalid menu
        Monkey\Functions\expect('wp_nav_menu')
            ->once()
            ->andReturn(false);
        
        $result = oom_custom_menu_shortcode(['id' => '999']);
        
        $this->assertEquals('', $result);
    }

    /**
     * Test oom_custom_menu_shortcode with string id
     */
    public function test_oom_custom_menu_shortcode_string_id()
    {
        $menu_html = '<ul class="oom-shortcode-menu"><li>Test</li></ul>';
        
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->andReturn(['id' => '10']);
        
        Monkey\Functions\expect('wp_nav_menu')
            ->once()
            ->andReturn($menu_html);
        
        $result = oom_custom_menu_shortcode(['id' => '10']);
        
        $this->assertNotEmpty($result);
    }

    /**
     * Test oom_get_blockout_dates_for_datepicker function exists
     */
    public function test_oom_get_blockout_dates_for_datepicker_exists()
    {
        $this->assertTrue(function_exists('oom_get_blockout_dates_for_datepicker'));
    }

    /**
     * Test oom_get_blockout_dates_for_datepicker with empty option
     */
    public function test_oom_get_blockout_dates_empty()
    {
        Monkey\Functions\expect('get_option')
            ->once()
            ->with('oom_blockout_dates', '')
            ->andReturn('');
        
        $result = oom_get_blockout_dates_for_datepicker();
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test oom_get_blockout_dates_for_datepicker with valid dates
     */
    public function test_oom_get_blockout_dates_with_valid_dates()
    {
        $dates_string = '25-12-2024, 01-01-2025, 15-02-2025';
        
        Monkey\Functions\expect('get_option')
            ->once()
            ->with('oom_blockout_dates', '')
            ->andReturn($dates_string);
        
        $result = oom_get_blockout_dates_for_datepicker();
        
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertContains('25-12-2024', $result);
        $this->assertContains('01-01-2025', $result);
        $this->assertContains('15-02-2025', $result);
    }

    /**
     * Test oom_get_blockout_dates_for_datepicker with invalid date format
     */
    public function test_oom_get_blockout_dates_with_invalid_format()
    {
        $dates_string = '25-12-2024, invalid-date, 01-01-2025, 2025/02/15';
        
        Monkey\Functions\expect('get_option')
            ->once()
            ->with('oom_blockout_dates', '')
            ->andReturn($dates_string);
        
        $result = oom_get_blockout_dates_for_datepicker();
        
        $this->assertIsArray($result);
        $this->assertCount(2, $result); // Only valid dates
        $this->assertContains('25-12-2024', $result);
        $this->assertContains('01-01-2025', $result);
        $this->assertNotContains('invalid-date', $result);
        $this->assertNotContains('2025/02/15', $result);
    }

    /**
     * Test oom_get_blockout_dates_for_datepicker with whitespace
     */
    public function test_oom_get_blockout_dates_with_whitespace()
    {
        $dates_string = '  25-12-2024  ,  01-01-2025  ';
        
        Monkey\Functions\expect('get_option')
            ->once()
            ->with('oom_blockout_dates', '')
            ->andReturn($dates_string);
        
        $result = oom_get_blockout_dates_for_datepicker();
        
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertContains('25-12-2024', $result);
        $this->assertContains('01-01-2025', $result);
    }

    /**
     * Test oom_get_blockout_dates_for_datepicker with single date
     */
    public function test_oom_get_blockout_dates_single_date()
    {
        $dates_string = '25-12-2024';
        
        Monkey\Functions\expect('get_option')
            ->once()
            ->with('oom_blockout_dates', '')
            ->andReturn($dates_string);
        
        $result = oom_get_blockout_dates_for_datepicker();
        
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('25-12-2024', $result[0]);
    }

    /**
     * Test oom_get_blockout_dates_for_datepicker with multiple commas
     */
    public function test_oom_get_blockout_dates_multiple_commas()
    {
        $dates_string = '25-12-2024,, 01-01-2025, , 15-02-2025';
        
        Monkey\Functions\expect('get_option')
            ->once()
            ->with('oom_blockout_dates', '')
            ->andReturn($dates_string);
        
        $result = oom_get_blockout_dates_for_datepicker();
        
        $this->assertIsArray($result);
        // Empty strings after trim won't match the regex, so should be filtered out
        $this->assertCount(3, $result);
    }

    /**
     * Test oom_get_blockout_dates_for_datepicker validates date format strictly
     */
    public function test_oom_get_blockout_dates_strict_format()
    {
        $dates_string = '1-1-2024, 01-1-2024, 01-01-2024, 001-01-2024';
        
        Monkey\Functions\expect('get_option')
            ->once()
            ->with('oom_blockout_dates', '')
            ->andReturn($dates_string);
        
        $result = oom_get_blockout_dates_for_datepicker();
        
        $this->assertIsArray($result);
        // Only 01-01-2024 matches dd-mm-yyyy format
        $this->assertCount(1, $result);
        $this->assertContains('01-01-2024', $result);
    }

    /**
     * Test oom_get_blockout_dates_for_datepicker with null option
     */
    public function test_oom_get_blockout_dates_null_option()
    {
        Monkey\Functions\expect('get_option')
            ->once()
            ->with('oom_blockout_dates', '')
            ->andReturn(null);
        
        $result = oom_get_blockout_dates_for_datepicker();
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test oom_get_blockout_dates_for_datepicker with false option
     */
    public function test_oom_get_blockout_dates_false_option()
    {
        Monkey\Functions\expect('get_option')
            ->once()
            ->with('oom_blockout_dates', '')
            ->andReturn(false);
        
        $result = oom_get_blockout_dates_for_datepicker();
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test that the file loads without errors
     */
    public function test_file_loads_successfully()
    {
        $this->assertTrue(self::$fileLoaded);
    }

    /**
     * Test shortcode file defines expected functions
     */
    public function test_expected_functions_defined()
    {
        $expected_functions = [
            'current_year_shortcode',
            'oom_custom_menu_shortcode',
            'oom_get_blockout_dates_for_datepicker',
        ];
        
        foreach ($expected_functions as $function) {
            $this->assertTrue(
                function_exists($function),
                "Function {$function} should be defined"
            );
        }
    }

    /**
     * Test that all expected shortcodes are registered
     */
    public function test_shortcodes_registered()
    {
        $expected_shortcodes = [
            'current_year',
            'oom_rental_price_box',
            'rental_vehicle_type_swiper_pagi',
            'rental_vehicle_type_swiper',
            'oom-custom-menu',
            'oom_daily_rental_form',
            'oom_daily_rental_hidden_fields',
            'oom_single_rental_form',
            'oom_single_rental_pricing',
        ];
        
        foreach ($expected_shortcodes as $shortcode) {
            $this->assertArrayHasKey(
                $shortcode,
                self::$shortcodes,
                "Shortcode {$shortcode} should be registered"
            );
        }
    }

    /**
     * Test oom_rental_price_box shortcode not on rental singular page
     */
    public function test_oom_rental_price_box_not_singular()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(false);
        
        $callback = self::$shortcodes['oom_rental_price_box'];
        $result = $callback();
        
        $this->assertEquals('', $result);
    }

    /**
     * Test oom_rental_price_box with regular price only
     */
    public function test_oom_rental_price_box_regular_price()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(true);
        
        Monkey\Functions\expect('get_the_ID')
            ->once()
            ->andReturn(123);
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'rental_price', true)
            ->andReturn('150.00');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'rental_sale_price', true)
            ->andReturn('');
        
        Monkey\Functions\expect('esc_html')
            ->andReturnUsing(function($text) { return $text; });
        
        $callback = self::$shortcodes['oom_rental_price_box'];
        $result = $callback();
        
        $this->assertStringContainsString('oom-custom-pricing-box', $result);
        $this->assertStringContainsString('highlight', $result);
        $this->assertStringContainsString('$150', $result);
    }

    /**
     * Test oom_rental_price_box with sale price
     */
    public function test_oom_rental_price_box_with_sale()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(true);
        
        Monkey\Functions\expect('get_the_ID')
            ->once()
            ->andReturn(123);
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'rental_price', true)
            ->andReturn('200.00');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'rental_sale_price', true)
            ->andReturn('150.00');
        
        Monkey\Functions\expect('esc_html')
            ->andReturnUsing(function($text) { return $text; });
        
        $callback = self::$shortcodes['oom_rental_price_box'];
        $result = $callback();
        
        $this->assertStringContainsString('oom-custom-pricing-box', $result);
        $this->assertStringContainsString('strike', $result);
        $this->assertStringContainsString('$200', $result);
        $this->assertStringContainsString('$150', $result);
    }

    /**
     * Test oom_rental_price_box with no price
     */
    public function test_oom_rental_price_box_no_price()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(true);
        
        Monkey\Functions\expect('get_the_ID')
            ->once()
            ->andReturn(123);
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'rental_price', true)
            ->andReturn('');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'rental_sale_price', true)
            ->andReturn('');
        
        $callback = self::$shortcodes['oom_rental_price_box'];
        $result = $callback();
        
        $this->assertEquals('', $result);
    }

    /**
     * Test oom_rental_price_box formats integer price
     */
    public function test_oom_rental_price_box_integer_format()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(true);
        
        Monkey\Functions\expect('get_the_ID')
            ->once()
            ->andReturn(123);
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'rental_price', true)
            ->andReturn('100.00');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'rental_sale_price', true)
            ->andReturn('');
        
        Monkey\Functions\expect('esc_html')
            ->andReturnUsing(function($text) { return $text; });
        
        $callback = self::$shortcodes['oom_rental_price_box'];
        $result = $callback();
        
        $this->assertStringContainsString('$100', $result);
        // Integer prices should not have decimals
        $this->assertStringNotContainsString('.00', $result);
    }

    /**
     * Test oom_rental_price_box formats decimal price
     */
    public function test_oom_rental_price_box_decimal_format()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(true);
        
        Monkey\Functions\expect('get_the_ID')
            ->once()
            ->andReturn(123);
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'rental_price', true)
            ->andReturn('99.50');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'rental_sale_price', true)
            ->andReturn('');
        
        Monkey\Functions\expect('esc_html')
            ->andReturnUsing(function($text) { return $text; });
        
        $callback = self::$shortcodes['oom_rental_price_box'];
        $result = $callback();
        
        $this->assertStringContainsString('$99.50', $result);
    }

    /**
     * Test rental_vehicle_type_swiper_pagi not on singular rental
     */
    public function test_rental_vehicle_swiper_pagi_not_singular()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(false);
        
        $callback = self::$shortcodes['rental_vehicle_type_swiper_pagi'];
        $result = $callback();
        
        $this->assertEquals('', $result);
    }

    /**
     * Test rental_vehicle_type_swiper_pagi on rental post
     */
    public function test_rental_vehicle_swiper_pagi_on_rental()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(true);
        
        Monkey\Functions\expect('get_the_ID')
            ->once()
            ->andReturn(456);
        
        Monkey\Functions\expect('esc_attr')
            ->andReturnUsing(function($text) { return $text; });
        
        $callback = self::$shortcodes['rental_vehicle_type_swiper_pagi'];
        $result = $callback();
        
        $this->assertStringContainsString('oomModelSwiper', $result);
        $this->assertStringContainsString('oomModelSwiperPagi', $result);
        $this->assertStringContainsString('data-rental-id="456"', $result);
        $this->assertStringContainsString('Loading models...', $result);
    }

    /**
     * Test rental_vehicle_type_swiper not on singular rental
     */
    public function test_rental_vehicle_swiper_not_singular()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(false);
        
        $callback = self::$shortcodes['rental_vehicle_type_swiper'];
        $result = $callback();
        
        $this->assertEquals('', $result);
    }

    /**
     * Test rental_vehicle_type_swiper on rental post
     */
    public function test_rental_vehicle_swiper_on_rental()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(true);
        
        Monkey\Functions\expect('get_the_ID')
            ->once()
            ->andReturn(789);
        
        Monkey\Functions\expect('esc_attr')
            ->andReturnUsing(function($text) { return $text; });
        
        $callback = self::$shortcodes['rental_vehicle_type_swiper'];
        $result = $callback();
        
        $this->assertStringContainsString('oomModelSwiper', $result);
        $this->assertStringNotContainsString('oomModelSwiperPagi', $result);
        $this->assertStringContainsString('data-rental-id="789"', $result);
    }

    /**
     * Test oom_daily_rental_hidden_fields shortcode
     */
    public function test_oom_daily_rental_hidden_fields_output()
    {
        $callback = self::$shortcodes['oom_daily_rental_hidden_fields'];
        $result = $callback();
        
        $this->assertStringContainsString('oom-daily-rental-hidden-fields', $result);
        $this->assertStringContainsString('sessionStorage.getItem', $result);
        $this->assertStringContainsString('oom_daily_rental_data', $result);
        $this->assertStringContainsString('DOMContentLoaded', $result);
        $this->assertStringContainsString('oom-daily-rental-pickup-date', $result);
        $this->assertStringContainsString('oom-daily-rental-drop-off-date', $result);
    }

    /**
     * Test oom_single_rental_pricing not on singular rental
     */
    public function test_oom_single_rental_pricing_not_singular()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(false);
        
        $callback = self::$shortcodes['oom_single_rental_pricing'];
        $result = $callback();
        
        $this->assertEquals('', $result);
    }

    /**
     * Test oom_single_rental_pricing with regular price
     */
    public function test_oom_single_rental_pricing_with_regular_price()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(true);
        
        Monkey\Functions\expect('get_the_ID')
            ->once()
            ->andReturn(100);
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(100, 'rental_price', true)
            ->andReturn('150.50');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(100, 'rental_sale_price', true)
            ->andReturn('');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(100, 'minimum_days_for_discount', true)
            ->andReturn('7');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(100, 'daily_discount_amount', true)
            ->andReturn('10');
        
        Monkey\Functions\expect('esc_attr')
            ->andReturnUsing(function($text) { return $text; });
        
        Monkey\Functions\expect('esc_html')
            ->andReturnUsing(function($text) { return $text; });
        
        $callback = self::$shortcodes['oom_single_rental_pricing'];
        $result = $callback();
        
        $this->assertStringContainsString('oom-single-rental-pricing', $result);
        $this->assertStringContainsString('data-base-price="150.50"', $result);
        $this->assertStringContainsString('data-daily-rate="150.50"', $result);
        $this->assertStringContainsString('data-minimum-days-for-discount="7"', $result);
        $this->assertStringContainsString('data-daily-discount-amount="10"', $result);
        $this->assertStringContainsString('SGD', $result);
    }

    /**
     * Test oom_single_rental_pricing with sale price
     */
    public function test_oom_single_rental_pricing_with_sale_price()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(true);
        
        Monkey\Functions\expect('get_the_ID')
            ->once()
            ->andReturn(200);
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(200, 'rental_price', true)
            ->andReturn('200.00');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(200, 'rental_sale_price', true)
            ->andReturn('180.00');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(200, 'minimum_days_for_discount', true)
            ->andReturn('');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(200, 'daily_discount_amount', true)
            ->andReturn('');
        
        Monkey\Functions\expect('esc_attr')
            ->andReturnUsing(function($text) { return $text; });
        
        Monkey\Functions\expect('esc_html')
            ->andReturnUsing(function($text) { return $text; });
        
        $callback = self::$shortcodes['oom_single_rental_pricing'];
        $result = $callback();
        
        $this->assertStringContainsString('oom-single-rental-pricing', $result);
        $this->assertStringContainsString('data-base-price="180.00"', $result);
        $this->assertStringContainsString('180.00', $result);
    }

    /**
     * Test oom_daily_rental_form shortcode output
     */
    public function test_oom_daily_rental_form_output()
    {
        Monkey\Functions\when('get_option')->justReturn('');
        
        $callback = self::$shortcodes['oom_daily_rental_form'];
        $result = $callback();
        
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('jquery', strtolower($result));
        $this->assertStringContainsString('style', strtolower($result));
    }

    /**
     * Test oom_single_rental_form shortcode output
     */
    public function test_oom_single_rental_form_output()
    {
        Monkey\Functions\when('get_the_ID')->justReturn(123);
        Monkey\Functions\when('get_post_meta')->justReturn('');
        Monkey\Functions\when('get_option')->justReturn('');
        
        $callback = self::$shortcodes['oom_single_rental_form'];
        $result = $callback();
        
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('jquery', strtolower($result));
        $this->assertStringContainsString('style', strtolower($result));
    }

    /**
     * Test rental_vehicle_type_swiper_pagi_backup shortcode exists
     */
    public function test_rental_vehicle_swiper_pagi_backup_exists()
    {
        $this->assertArrayHasKey('rental_vehicle_type_swiper_pagi_backup', self::$shortcodes);
    }

    /**
     * Test rental_vehicle_type_swiper_pagi_backup not on rental post
     */
    public function test_rental_vehicle_swiper_pagi_backup_not_singular()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(false);
        
        $callback = self::$shortcodes['rental_vehicle_type_swiper_pagi_backup'];
        $result = $callback();
        
        $this->assertEquals('', $result);
    }

    /**
     * Test rental_vehicle_type_swiper_pagi_backup on rental post with no vehicles
     */
    public function test_rental_vehicle_swiper_pagi_backup_on_rental()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(true);
        
        Monkey\Functions\expect('get_the_ID')
            ->once()
            ->andReturn(999);
        
        $callback = self::$shortcodes['rental_vehicle_type_swiper_pagi_backup'];
        $result = $callback();
        
        // When no vehicles found, it returns a message
        $this->assertStringContainsString('No related vehicles', $result);
    }

    /**
     * Test price formatting with various decimal scenarios
     */
    public function test_rental_price_box_various_decimals()
    {
        // Test with .50 cents
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(true);
        
        Monkey\Functions\expect('get_the_ID')
            ->once()
            ->andReturn(123);
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'rental_price', true)
            ->andReturn('49.99');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'rental_sale_price', true)
            ->andReturn('');
        
        $callback = self::$shortcodes['oom_rental_price_box'];
        $result = $callback();
        
        $this->assertStringContainsString('$49.99', $result);
    }

    /**
     * Test oom_single_rental_pricing with zero discount
     */
    public function test_oom_single_rental_pricing_zero_discount()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(true);
        
        Monkey\Functions\expect('get_the_ID')
            ->once()
            ->andReturn(300);
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(300, 'rental_price', true)
            ->andReturn('100');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(300, 'rental_sale_price', true)
            ->andReturn('');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(300, 'minimum_days_for_discount', true)
            ->andReturn('0');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(300, 'daily_discount_amount', true)
            ->andReturn('0');
        
        $callback = self::$shortcodes['oom_single_rental_pricing'];
        $result = $callback();
        
        $this->assertStringContainsString('data-minimum-days-for-discount="0"', $result);
        $this->assertStringContainsString('data-daily-discount-amount="0"', $result);
    }

    /**
     * Test oom_daily_rental_hidden_fields contains all required field names
     */
    public function test_oom_daily_rental_hidden_fields_complete()
    {
        $callback = self::$shortcodes['oom_daily_rental_hidden_fields'];
        $result = $callback();
        
        $this->assertStringContainsString('oom-daily-rental-pickup-location', $result);
        $this->assertStringContainsString('oom-daily-rental-drop-off-location', $result);
        $this->assertStringContainsString('oom-daily-rental-pickup-time', $result);
        $this->assertStringContainsString('oom-daily-rental-drop-off-time', $result);
    }

    /**
     * Test current_year shortcode is registered
     */
    public function test_current_year_shortcode_registered()
    {
        $this->assertArrayHasKey('current_year', self::$shortcodes);
    }

    /**
     * Test current_year shortcode callback
     */
    public function test_current_year_via_shortcode_callback()
    {
        if (isset(self::$shortcodes['current_year'])) {
            $callback = self::$shortcodes['current_year'];
            
            // If it's a string, it's a function name
            if (is_string($callback)) {
                $result = $callback();
                $this->assertEquals(date('Y'), $result);
            }
        } else {
            $this->markTestSkipped('current_year shortcode not registered');
        }
    }

    /**
     * Test oom_get_blockout_dates_for_datepicker with mixed valid and invalid dates
     */
    public function test_oom_get_blockout_dates_mixed()
    {
        // Note: The regex only checks format (dd-mm-yyyy), not if date is valid
        // So 32-13-2025 will pass the format check even though it's invalid
        $dates_string = '01-01-2025, 1-1-2025, 15-02-2025, abc, 28-02-2025';
        
        Monkey\Functions\expect('get_option')
            ->once()
            ->with('oom_blockout_dates', '')
            ->andReturn($dates_string);
        
        $result = oom_get_blockout_dates_for_datepicker();
        
        $this->assertIsArray($result);
        $this->assertCount(3, $result); // Only properly formatted dates
        $this->assertContains('01-01-2025', $result);
        $this->assertContains('15-02-2025', $result);
        $this->assertContains('28-02-2025', $result);
        $this->assertNotContains('1-1-2025', $result); // Single digit, should fail
        $this->assertNotContains('abc', $result);
    }

    /**
     * Test oom_rental_price_box with large numbers
     */
    public function test_rental_price_box_large_numbers()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(true);
        
        Monkey\Functions\expect('get_the_ID')
            ->once()
            ->andReturn(123);
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'rental_price', true)
            ->andReturn('1000.00');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'rental_sale_price', true)
            ->andReturn('');
        
        $callback = self::$shortcodes['oom_rental_price_box'];
        $result = $callback();
        
        $this->assertStringContainsString('$1,000', $result);
        $this->assertStringNotContainsString('.00', $result);
    }

    /**
     * Test oom_single_rental_pricing with large discount amounts
     */
    public function test_oom_single_rental_pricing_large_discount()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(true);
        
        Monkey\Functions\expect('get_the_ID')
            ->once()
            ->andReturn(400);
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(400, 'rental_price', true)
            ->andReturn('500');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(400, 'rental_sale_price', true)
            ->andReturn('');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(400, 'minimum_days_for_discount', true)
            ->andReturn('30');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(400, 'daily_discount_amount', true)
            ->andReturn('50');
        
        $callback = self::$shortcodes['oom_single_rental_pricing'];
        $result = $callback();
        
        $this->assertStringContainsString('data-minimum-days-for-discount="30"', $result);
        $this->assertStringContainsString('data-daily-discount-amount="50"', $result);
    }

    /**
     * Test oom_rental_price_box with both prices having decimals
     */
    public function test_rental_price_box_both_with_decimals()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(true);
        
        Monkey\Functions\expect('get_the_ID')
            ->once()
            ->andReturn(123);
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'rental_price', true)
            ->andReturn('299.99');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'rental_sale_price', true)
            ->andReturn('199.99');
        
        $callback = self::$shortcodes['oom_rental_price_box'];
        $result = $callback();
        
        $this->assertStringContainsString('$299.99', $result);
        $this->assertStringContainsString('$199.99', $result);
        $this->assertStringContainsString('strike', $result);
    }

    /**
     * Test oom_rental_price_box with non-numeric price
     */
    public function test_rental_price_box_non_numeric()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(true);
        
        Monkey\Functions\expect('get_the_ID')
            ->once()
            ->andReturn(123);
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'rental_price', true)
            ->andReturn('abc');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(123, 'rental_sale_price', true)
            ->andReturn('');
        
        $callback = self::$shortcodes['oom_rental_price_box'];
        $result = $callback();
        
        // Non-numeric prices are displayed as-is
        $this->assertStringContainsString('$abc', $result);
    }

    /**
     * Test oom_single_rental_pricing with empty price
     */
    public function test_oom_single_rental_pricing_empty_price()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(true);
        
        Monkey\Functions\expect('get_the_ID')
            ->once()
            ->andReturn(500);
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(500, 'rental_price', true)
            ->andReturn('');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(500, 'rental_sale_price', true)
            ->andReturn('');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(500, 'minimum_days_for_discount', true)
            ->andReturn('');
        
        Monkey\Functions\expect('get_post_meta')
            ->once()
            ->with(500, 'daily_discount_amount', true)
            ->andReturn('');
        
        $callback = self::$shortcodes['oom_single_rental_pricing'];
        $result = $callback();
        
        $this->assertStringContainsString('oom-single-rental-pricing', $result);
        $this->assertStringContainsString('data-base-price="0.00"', $result);
    }

    /**
     * Test oom_get_blockout_dates_for_datepicker with dates containing extra spaces
     */
    public function test_oom_get_blockout_dates_extra_spaces()
    {
        $dates_string = '  01-01-2025  ,    15-02-2025   ,  28-12-2024  ';
        
        Monkey\Functions\expect('get_option')
            ->once()
            ->with('oom_blockout_dates', '')
            ->andReturn($dates_string);
        
        $result = oom_get_blockout_dates_for_datepicker();
        
        $this->assertCount(3, $result);
        // Trim should handle the extra spaces
        $this->assertContains('01-01-2025', $result);
        $this->assertContains('15-02-2025', $result);
        $this->assertContains('28-12-2024', $result);
    }

    /**
     * Test oom_custom_menu_shortcode with empty ID string
     */
    public function test_oom_custom_menu_shortcode_empty_id_string()
    {
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->andReturn(['id' => '']);
        
        $result = oom_custom_menu_shortcode(['id' => '']);
        
        $this->assertEquals('', $result);
    }

    /**
     * Test oom_custom_menu_shortcode with zero ID
     */
    public function test_oom_custom_menu_shortcode_zero_id()
    {
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->andReturn(['id' => '0']);
        
        $result = oom_custom_menu_shortcode(['id' => '0']);
        
        $this->assertEquals('', $result);
    }

    /**
     * Test rental swiper with different rental IDs
     */
    public function test_rental_vehicle_swiper_various_ids()
    {
        Monkey\Functions\expect('is_singular')
            ->once()
            ->with('rental')
            ->andReturn(true);
        
        Monkey\Functions\expect('get_the_ID')
            ->once()
            ->andReturn(12345);
        
        $callback = self::$shortcodes['rental_vehicle_type_swiper'];
        $result = $callback();
        
        $this->assertStringContainsString('data-rental-id="12345"', $result);
    }

    /**
     * Test oom_daily_rental_form contains blockout dates script
     */
    public function test_oom_daily_rental_form_blockout_dates()
    {
        Monkey\Functions\when('get_option')->justReturn('01-01-2025, 25-12-2024');
        
        $callback = self::$shortcodes['oom_daily_rental_form'];
        $result = $callback();
        
        // Form should contain Google Places or autocomplete references
        $this->assertStringContainsString('pac-container', strtolower($result));
    }

    /**
     * Test oom_single_rental_form contains single rental specific elements
     */
    public function test_oom_single_rental_form_specific_elements()
    {
        Monkey\Functions\when('get_the_ID')->justReturn(456);
        Monkey\Functions\when('get_post_meta')->justReturn('');
        Monkey\Functions\when('get_option')->justReturn('');
        
        $callback = self::$shortcodes['oom_single_rental_form'];
        $result = $callback();
        
        $this->assertStringContainsString('datepicker', strtolower($result));
    }
}
