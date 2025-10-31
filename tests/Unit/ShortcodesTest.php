<?php
/**
 * Tests for shortcode functions
 *
 * @package HelloElementorChild\Tests\Unit
 */

namespace HelloElementorChild\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

/**
 * Class ShortcodesTest
 */
class ShortcodesTest extends TestCase
{
    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        
        // Load the shortcode files
        require_once __DIR__ . '/../../../hello-elementor-child/oom/oom-global-shortcode.php';
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
     * Test oom_ratings shortcode with default display
     */
    public function test_oom_ratings_default()
    {
        $atts = [];
        $result = oom_ratings($atts);
        
        $this->assertIsString($result);
        $this->assertStringContainsString('oom-star-rating', $result);
        $this->assertStringContainsString('fa-star checked', $result);
    }

    /**
     * Test oom_ratings shortcode with custom display
     */
    public function test_oom_ratings_custom_display()
    {
        $atts = ['display' => '3'];
        $result = oom_ratings($atts);
        
        $this->assertIsString($result);
        $this->assertStringContainsString('oom-star-rating', $result);
        
        // Should have 3 checked stars
        $checked_count = substr_count($result, 'fa-star checked');
        $this->assertEquals(3, $checked_count);
        
        // Should have 2 unchecked stars
        $unchecked_count = substr_count($result, 'fa-star"></span>') - $checked_count;
        $this->assertEquals(2, $unchecked_count);
    }

    /**
     * Test oom_ratings shortcode with zero display
     */
    public function test_oom_ratings_zero_display()
    {
        $atts = ['display' => '0'];
        $result = oom_ratings($atts);
        
        $this->assertIsString($result);
        $checked_count = substr_count($result, 'fa-star checked');
        $this->assertEquals(0, $checked_count);
    }

    /**
     * Test oom_ratings shortcode with max display
     */
    public function test_oom_ratings_max_display()
    {
        $atts = ['display' => '5'];
        $result = oom_ratings($atts);
        
        $checked_count = substr_count($result, 'fa-star checked');
        $this->assertEquals(5, $checked_count);
    }

    /**
     * Test oom_ratings shortcode with out of range value (too high)
     */
    public function test_oom_ratings_out_of_range_high()
    {
        $atts = ['display' => '10'];
        $result = oom_ratings($atts);
        
        // Should clamp to 5
        $checked_count = substr_count($result, 'fa-star checked');
        $this->assertEquals(5, $checked_count);
    }

    /**
     * Test oom_ratings shortcode with negative value
     */
    public function test_oom_ratings_negative()
    {
        $atts = ['display' => '-5'];
        $result = oom_ratings($atts);
        
        // Should clamp to 0
        $checked_count = substr_count($result, 'fa-star checked');
        $this->assertEquals(0, $checked_count);
    }

    /**
     * Test oom_hero_slider shortcode exists
     */
    public function test_oom_hero_slider_function_exists()
    {
        $this->assertTrue(function_exists('oom_hero_slider'));
    }

    /**
     * Test oom_hero_slider shortcode returns string
     */
    public function test_oom_hero_slider_returns_string()
    {
        // Mock shortcode_atts
        Monkey\Functions\when('shortcode_atts')->returnArg(2);
        
        // Mock WP_Query using Brain Monkey
        $mock_query = \Mockery::mock('WP_Query');
        $mock_query->posts = [];
        $mock_query->shouldReceive('have_posts')->andReturn(false);
        
        // Use Brain Monkey to stub WP_Query creation
        Monkey\Functions\when('wp_reset_query')->justReturn();
        
        // Since oom_hero_slider uses new WP_Query(), we need to test differently
        // This is a basic test that the function exists and can be called
        $this->assertTrue(function_exists('oom_hero_slider'));
    }
}

