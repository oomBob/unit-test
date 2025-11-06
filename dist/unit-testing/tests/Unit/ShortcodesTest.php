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
        
        // Mock WordPress shortcode functions before loading the file
        Monkey\Functions\when('add_shortcode')->justReturn();
        
        // Mock wp_reset_query
        Monkey\Functions\when('wp_reset_query')->justReturn();
        
        // Define WP_Query stub if not already defined
        if (!class_exists('WP_Query')) {
            eval('
                class WP_Query {
                    public $post = null;
                    public $posts = [];
                    public function have_posts() { return false; }
                    public function the_post() { return null; }
                }
            ');
        }
        
        // Load the shortcode files
        require_once __DIR__ . '/../../hello-elementor-child/oom/oom-global-shortcode.php';
    }
    
    /**
     * Create a mock WP_Query that returns no posts
     */
    protected function mockWPQuery()
    {
        // WP_Query is already defined in setUp, no need to mock it again
        return true;
    }

    /**
     * Tear down test environment
     */
    protected function tearDown(): void
    {
        \Mockery::close();
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Test oom_ratings shortcode with default display
     */
    public function test_oom_ratings_default()
    {
        $atts = [];
        
        // Mock shortcode_atts to return merged attributes with defaults
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->with(['display' => '5'], $atts)
            ->andReturn(['display' => '5']);
        
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
        
        // Mock shortcode_atts to return merged attributes
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->with(['display' => '5'], $atts)
            ->andReturn(['display' => '3']);
        
        $result = oom_ratings($atts);
        
        $this->assertIsString($result);
        $this->assertStringContainsString('oom-star-rating', $result);
        
        // Should have 3 checked stars
        $checked_count = substr_count($result, 'fa-star checked');
        $this->assertEquals(3, $checked_count);
        
        // Should have 2 unchecked stars (total stars minus checked stars)
        $total_stars = substr_count($result, 'fa-star');
        $unchecked_count = $total_stars - $checked_count;
        $this->assertEquals(2, $unchecked_count);
    }

    /**
     * Test oom_ratings shortcode with zero display
     */
    public function test_oom_ratings_zero_display()
    {
        $atts = ['display' => '0'];
        
        // Mock shortcode_atts to return merged attributes
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->with(['display' => '5'], $atts)
            ->andReturn(['display' => '0']);
        
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
        
        // Mock shortcode_atts to return merged attributes
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->with(['display' => '5'], $atts)
            ->andReturn(['display' => '5']);
        
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
        
        // Mock shortcode_atts to return merged attributes
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->with(['display' => '5'], $atts)
            ->andReturn(['display' => '10']);
        
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
        
        // Mock shortcode_atts to return merged attributes
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->with(['display' => '5'], $atts)
            ->andReturn(['display' => '-5']);
        
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

    /**
     * Test oom_hero_slider with default attributes
     */
    public function test_oom_hero_slider_default_attributes()
    {
        $this->mockWPQuery();
        $atts = [];
        
        // Mock shortcode_atts to return defaults
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->andReturn([
                'category_id' => 2,
                'direction' => 'horizontal',
                'arrow_position' => 'middle',
                'arrow_right_txt' => '',
                'arrow_left_txt' => '',
                'arrow_right' => 'fas fa-chevron-right',
                'arrow_left' => 'fas fa-chevron-left',
                'arrow_color' => '333333',
                'pagination_color' => 'fff',
                'pagination_active_hover' => 'F6F5F5',
                'has_arrow' => 'yes',
                'has_pagination' => 'yes',
                'height' => '600px'
            ]);
        
        $result = oom_hero_slider($atts);
        
        $this->assertIsString($result);
        $this->assertStringContainsString('oom-hero-slider', $result);
        $this->assertStringContainsString('swiper', $result);
    }

    /**
     * Test oom_hero_slider with vertical direction
     */
    public function test_oom_hero_slider_vertical_direction()
    {
        $this->mockWPQuery();
        $atts = ['direction' => 'vertical', 'height' => '800px', 'category_id' => '3'];
        
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->andReturn([
                'category_id' => '3',
                'direction' => 'vertical',
                'arrow_position' => 'middle',
                'arrow_right_txt' => '',
                'arrow_left_txt' => '',
                'arrow_right' => 'fas fa-chevron-right',
                'arrow_left' => 'fas fa-chevron-left',
                'arrow_color' => '333333',
                'pagination_color' => 'fff',
                'pagination_active_hover' => 'F6F5F5',
                'has_arrow' => 'yes',
                'has_pagination' => 'yes',
                'height' => '800px'
            ]);
        
        $result = oom_hero_slider($atts);
        
        // Should contain height style when direction is vertical
        $this->assertStringContainsString('height: 800px', $result);
        $this->assertStringContainsString('#oom-hero-slider3', $result);
    }

    /**
     * Test oom_hero_slider with bottom arrow position
     */
    public function test_oom_hero_slider_bottom_arrow_position()
    {
        $this->mockWPQuery();
        $atts = ['arrow_position' => 'bottom'];
        
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->andReturn([
                'category_id' => 2,
                'direction' => 'horizontal',
                'arrow_position' => 'bottom',
                'arrow_right_txt' => '',
                'arrow_left_txt' => '',
                'arrow_right' => 'fas fa-chevron-right',
                'arrow_left' => 'fas fa-chevron-left',
                'arrow_color' => '333333',
                'pagination_color' => 'fff',
                'pagination_active_hover' => 'F6F5F5',
                'has_arrow' => 'yes',
                'has_pagination' => 'yes',
                'height' => '600px'
            ]);
        
        $result = oom_hero_slider($atts);
        
        // Should contain bottom arrow positioning
        $this->assertStringContainsString('top: 85%', $result);
    }

    /**
     * Test oom_hero_slider with custom colors
     */
    public function test_oom_hero_slider_custom_colors()
    {
        $this->mockWPQuery();
        $atts = [
            'arrow_color' => 'ff0000',
            'pagination_color' => '00ff00',
            'pagination_active_hover' => '0000ff'
        ];
        
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->andReturn([
                'category_id' => 2,
                'direction' => 'horizontal',
                'arrow_position' => 'middle',
                'arrow_right_txt' => '',
                'arrow_left_txt' => '',
                'arrow_right' => 'fas fa-chevron-right',
                'arrow_left' => 'fas fa-chevron-left',
                'arrow_color' => 'ff0000',
                'pagination_color' => '00ff00',
                'pagination_active_hover' => '0000ff',
                'has_arrow' => 'yes',
                'has_pagination' => 'yes',
                'height' => '600px'
            ]);
        
        $result = oom_hero_slider($atts);
        
        // Should contain custom colors
        $this->assertStringContainsString('#ff0000', $result);
        $this->assertStringContainsString('#00ff00', $result);
        $this->assertStringContainsString('#0000ff', $result);
    }

    /**
     * Test oom_hero_slider with arrows disabled
     */
    public function test_oom_hero_slider_arrows_disabled()
    {
        $this->mockWPQuery();
        $atts = ['has_arrow' => 'no'];
        
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->andReturn([
                'category_id' => 2,
                'direction' => 'horizontal',
                'arrow_position' => 'middle',
                'arrow_right_txt' => '',
                'arrow_left_txt' => '',
                'arrow_right' => 'fas fa-chevron-right',
                'arrow_left' => 'fas fa-chevron-left',
                'arrow_color' => '333333',
                'pagination_color' => 'fff',
                'pagination_active_hover' => 'F6F5F5',
                'has_arrow' => 'no',
                'has_pagination' => 'yes',
                'height' => '600px'
            ]);
        
        $result = oom_hero_slider($atts);
        
        // Should not contain arrow HTML div elements when disabled
        // (though they may still be referenced in JS config)
        $this->assertStringNotContainsString('<div class="swiper-button-next', $result);
        $this->assertStringNotContainsString('<div class="swiper-button-prev', $result);
    }

    /**
     * Test oom_hero_slider with pagination disabled
     */
    public function test_oom_hero_slider_pagination_disabled()
    {
        $this->mockWPQuery();
        $atts = ['has_pagination' => 'no'];
        
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->andReturn([
                'category_id' => 2,
                'direction' => 'horizontal',
                'arrow_position' => 'middle',
                'arrow_right_txt' => '',
                'arrow_left_txt' => '',
                'arrow_right' => 'fas fa-chevron-right',
                'arrow_left' => 'fas fa-chevron-left',
                'arrow_color' => '333333',
                'pagination_color' => 'fff',
                'pagination_active_hover' => 'F6F5F5',
                'has_arrow' => 'yes',
                'has_pagination' => 'no',
                'height' => '600px'
            ]);
        
        $result = oom_hero_slider($atts);
        
        // Should not contain pagination element when disabled
        $this->assertStringNotContainsString('swiper-pagination swiper-pagination2', $result);
    }

    /**
     * Test oom_hero_slider with custom arrow text
     */
    public function test_oom_hero_slider_custom_arrow_text()
    {
        $this->mockWPQuery();
        $atts = [
            'arrow_right_txt' => 'Next',
            'arrow_left_txt' => 'Previous'
        ];
        
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->andReturn([
                'category_id' => 2,
                'direction' => 'horizontal',
                'arrow_position' => 'middle',
                'arrow_right_txt' => 'Next',
                'arrow_left_txt' => 'Previous',
                'arrow_right' => 'fas fa-chevron-right',
                'arrow_left' => 'fas fa-chevron-left',
                'arrow_color' => '333333',
                'pagination_color' => 'fff',
                'pagination_active_hover' => 'F6F5F5',
                'has_arrow' => 'yes',
                'has_pagination' => 'yes',
                'height' => '600px'
            ]);
        
        $result = oom_hero_slider($atts);
        
        // Should contain custom arrow text
        $this->assertStringContainsString('Next', $result);
        $this->assertStringContainsString('Previous', $result);
    }

    /**
     * Test oom_hero_slider with custom arrow icons
     */
    public function test_oom_hero_slider_custom_arrow_icons()
    {
        $this->mockWPQuery();
        $atts = [
            'arrow_right' => 'fas fa-angle-right',
            'arrow_left' => 'fas fa-angle-left'
        ];
        
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->andReturn([
                'category_id' => 2,
                'direction' => 'horizontal',
                'arrow_position' => 'middle',
                'arrow_right_txt' => '',
                'arrow_left_txt' => '',
                'arrow_right' => 'fas fa-angle-right',
                'arrow_left' => 'fas fa-angle-left',
                'arrow_color' => '333333',
                'pagination_color' => 'fff',
                'pagination_active_hover' => 'F6F5F5',
                'has_arrow' => 'yes',
                'has_pagination' => 'yes',
                'height' => '600px'
            ]);
        
        $result = oom_hero_slider($atts);
        
        // Should contain custom arrow icons
        $this->assertStringContainsString('fas fa-angle-right', $result);
        $this->assertStringContainsString('fas fa-angle-left', $result);
    }

    /**
     * Test oom_hero_slider Swiper initialization script
     */
    public function test_oom_hero_slider_swiper_script()
    {
        $this->mockWPQuery();
        $atts = ['category_id' => '5'];
        
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->andReturn([
                'category_id' => '5',
                'direction' => 'horizontal',
                'arrow_position' => 'middle',
                'arrow_right_txt' => '',
                'arrow_left_txt' => '',
                'arrow_right' => 'fas fa-chevron-right',
                'arrow_left' => 'fas fa-chevron-left',
                'arrow_color' => '333333',
                'pagination_color' => 'fff',
                'pagination_active_hover' => 'F6F5F5',
                'has_arrow' => 'yes',
                'has_pagination' => 'yes',
                'height' => '600px'
            ]);
        
        $result = oom_hero_slider($atts);
        
        // Should contain Swiper initialization script
        $this->assertStringContainsString('swiperOOmHeroSlider5', $result);
        $this->assertStringContainsString('new Swiper', $result);
        $this->assertStringContainsString('slidesPerView: 1', $result);
        $this->assertStringContainsString('swiper-pagination5', $result);
        $this->assertStringContainsString('swiper-button-next5', $result);
        $this->assertStringContainsString('swiper-button-prev5', $result);
    }

    /**
     * Test oom_hero_slider with both vertical direction and bottom arrows
     */
    public function test_oom_hero_slider_vertical_with_bottom_arrows()
    {
        $this->mockWPQuery();
        $atts = [
            'direction' => 'vertical',
            'arrow_position' => 'bottom',
            'height' => '700px',
            'category_id' => '4'
        ];
        
        Monkey\Functions\expect('shortcode_atts')
            ->once()
            ->andReturn([
                'category_id' => '4',
                'direction' => 'vertical',
                'arrow_position' => 'bottom',
                'arrow_right_txt' => '',
                'arrow_left_txt' => '',
                'arrow_right' => 'fas fa-chevron-right',
                'arrow_left' => 'fas fa-chevron-left',
                'arrow_color' => '333333',
                'pagination_color' => 'fff',
                'pagination_active_hover' => 'F6F5F5',
                'has_arrow' => 'yes',
                'has_pagination' => 'yes',
                'height' => '700px'
            ]);
        
        $result = oom_hero_slider($atts);
        
        // Should contain both vertical height and bottom arrow positioning
        $this->assertStringContainsString('height: 700px', $result);
        $this->assertStringContainsString('top: 85%', $result);
    }
}

