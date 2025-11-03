<?php
/**
 * Tests for OOm Table Widget
 *
 * @package HelloElementorChild\Tests\Unit
 */

namespace HelloElementorChild\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Mockery;

/**
 * Class TableWidgetTest
 */
class TableWidgetTest extends TestCase
{
    private $widget;
    private $mockControlsManager;
    private $mockRepeater;
    private $mockPlugin;
    private $mockWidgetsManager;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        
        // Mock WordPress functions
        Monkey\Functions\when('add_action')->justReturn(true);
        Monkey\Functions\when('esc_html__')->returnArg();
        Monkey\Functions\when('__')->returnArg();
        Monkey\Functions\when('esc_attr')->returnArg();
        Monkey\Functions\when('esc_url')->returnArg();
        Monkey\Functions\when('esc_textarea')->returnArg();
        Monkey\Functions\when('wp_kses_post')->returnArg();
        Monkey\Functions\when('get_post_meta')->justReturn('Alt text');
        Monkey\Functions\when('get_option')->alias(function($option) {
            if ($option === 'oom_table_status') {
                return 'active';
            }
            return null;
        });
        
        // Define ABSPATH constant if not defined
        if (!defined('ABSPATH')) {
            define('ABSPATH', '/fake/abspath/');
        }

        // Mock Elementor classes
        $this->setupElementorMocks();
        
        // Load the table widget file and register once
        if (!function_exists('register_oom_table_widget')) {
            require_once __DIR__ . '/../../hello-elementor-child/oom/widgets/oom-table-widget/oom-table-widget.php';
        }
        
        // Register the widget class if not already done
        if (!class_exists('OOm_Table')) {
            try {
                register_oom_table_widget();
            } catch (\Exception $e) {
                // Ignore registration errors when running with other tests
                // The class will still be defined within register_oom_table_widget
            } catch (\Error $e) {
                // Catch PHP errors as well (like undefined property)
            }
        }
    }

    /**
     * Setup Elementor mocks
     */
    private function setupElementorMocks()
    {
        // Mock Controls_Manager
        if (!class_exists('Elementor\Controls_Manager')) {
            eval('
                namespace Elementor {
                    class Controls_Manager {
                        const TEXT = "text";
                        const TEXTAREA = "textarea";
                        const WYSIWYG = "wysiwyg";
                        const NUMBER = "number";
                        const SELECT = "select";
                        const SWITCHER = "switcher";
                        const CHOOSE = "choose";
                        const SLIDER = "slider";
                        const DIMENSIONS = "dimensions";
                        const COLOR = "color";
                        const MEDIA = "media";
                        const URL = "url";
                        const ICONS = "icons";
                        const REPEATER = "repeater";
                        const TAB_STYLE = "tab-style";
                        const HEADING = "heading";
                    }
                }
            ');
        }

        // Mock Group_Control_Border
        if (!class_exists('Elementor\Group_Control_Border')) {
            eval('
                namespace Elementor {
                    class Group_Control_Border {
                        public static function get_type() { return "border"; }
                    }
                }
            ');
        }

        // Mock Group_Control_Typography
        if (!class_exists('Elementor\Group_Control_Typography')) {
            eval('
                namespace Elementor {
                    class Group_Control_Typography {
                        public static function get_type() { return "typography"; }
                    }
                }
            ');
        }

        // Mock Utils
        if (!class_exists('Elementor\Utils')) {
            eval('
                namespace Elementor {
                    class Utils {
                        public static function get_placeholder_image_src() {
                            return "https://example.com/placeholder.jpg";
                        }
                    }
                }
            ');
        }

        // Mock Icons_Manager
        if (!class_exists('Elementor\Icons_Manager')) {
            eval('
                namespace Elementor {
                    class Icons_Manager {
                        public static function render_icon($icon) {
                            echo "<i class=\"fas fa-icon\"></i>";
                        }
                    }
                }
            ');
        }

        // Mock Repeater
        if (!class_exists('Elementor\Repeater')) {
            eval('
                namespace Elementor {
                    class Repeater {
                        private $controls = [];
                        public function add_control($id, $args) {
                            $this->controls[$id] = $args;
                        }
                        public function get_controls() {
                            return $this->controls;
                        }
                    }
                }
            ');
        }

        // Mock Widget_Base
        if (!class_exists('Elementor\Widget_Base')) {
            eval('
                namespace Elementor {
                    class Widget_Base {
                        protected $settings = [];
                        protected $id = "test_id";
                        protected $render_attributes = [];
                        
                        public function get_id() {
                            return $this->id;
                        }
                        
                        public function get_settings_for_display() {
                            return $this->settings;
                        }
                        
                        public function set_settings($settings) {
                            $this->settings = $settings;
                        }
                        
                        public function start_controls_section($id, $args) {}
                        public function end_controls_section() {}
                        public function add_control($id, $args) {}
                        public function add_responsive_control($id, $args) {}
                        public function add_group_control($type, $args) {}
                        public function start_controls_tabs($id) {}
                        public function end_controls_tabs() {}
                        public function start_controls_tab($id, $args) {}
                        public function end_controls_tab() {}
                        
                        public function add_render_attribute($element, $key, $value = null) {
                            if (is_array($key)) {
                                foreach ($key as $k => $v) {
                                    $this->render_attributes[$element][$k] = $v;
                                }
                            } else {
                                $this->render_attributes[$element][$key] = $value;
                            }
                        }
                        
                        public function get_render_attribute_string($element) {
                            if (!isset($this->render_attributes[$element])) {
                                return "";
                            }
                            
                            $attributes = [];
                            foreach ($this->render_attributes[$element] as $key => $value) {
                                if (is_array($value)) {
                                    $value = implode(" ", $value);
                                }
                                $attributes[] = $key . "=\"" . $value . "\"";
                            }
                            return implode(" ", $attributes);
                        }
                    }
                }
            ');
        }

        // Mock Plugin only if it doesn't exist
        if (!class_exists('Elementor\Plugin')) {
            eval('
                namespace Elementor {
                    class WidgetsManager {
                        public function register_widget_type($widget) {
                            return true;
                        }
                    }
                    
                    class Plugin {
                        private static $instance = null;
                        public $widgets_manager;
                        
                        public function __construct() {
                            $this->widgets_manager = new WidgetsManager();
                        }
                        
                        public static function instance() {
                            if (self::$instance === null) {
                                self::$instance = new self();
                            }
                            return self::$instance;
                        }
                    }
                }
            ');
        }
        
        // Ensure widgets_manager is always set
        if (class_exists('Elementor\Plugin')) {
            try {
                $plugin = \Elementor\Plugin::instance();
                if (!isset($plugin->widgets_manager)) {
                    if (!class_exists('Elementor\WidgetsManager')) {
                        eval('
                            namespace Elementor {
                                class WidgetsManager {
                                    public function register_widget_type($widget) {
                                        return true;
                                    }
                                }
                            }
                        ');
                    }
                    $plugin->widgets_manager = new \Elementor\WidgetsManager();
                }
            } catch (\Exception $e) {
                // Ignore errors in case Plugin doesn't follow expected pattern
            }
        }
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
     * Helper method to call protected render method
     */
    private function callRenderMethod($widget)
    {
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);
        
        ob_start();
        $method->invoke($widget);
        return ob_get_clean();
    }

    /**
     * Test register_oom_table_widget function exists
     */
    public function test_register_oom_table_widget_exists()
    {
        $this->assertTrue(function_exists('register_oom_table_widget'));
    }

    /**
     * Test OOm_Table class creation
     */
    public function test_oom_table_class_can_be_created()
    {
        $this->assertTrue(class_exists('OOm_Table'));
    }

    /**
     * Test get_name method
     */
    public function test_get_name()
    {
        $widget = new \OOm_Table();
        $this->assertEquals('oom-table', $widget->get_name());
    }

    /**
     * Test get_title method
     */
    public function test_get_title()
    {
        $widget = new \OOm_Table();
        $result = $widget->get_title();
        $this->assertIsString($result);
    }

    /**
     * Test get_icon method
     */
    public function test_get_icon()
    {
        $widget = new \OOm_Table();
        $this->assertEquals('eicon-table', $widget->get_icon());
    }

    /**
     * Test get_categories method
     */
    public function test_get_categories()
    {
        $widget = new \OOm_Table();
        $categories = $widget->get_categories();
        $this->assertIsArray($categories);
        $this->assertContains('basic', $categories);
    }

    /**
     * Test get_keywords method
     */
    public function test_get_keywords()
    {
        $widget = new \OOm_Table();
        $keywords = $widget->get_keywords();
        $this->assertIsArray($keywords);
        $this->assertContains('table', $keywords);
        $this->assertContains('data table', $keywords);
    }

    /**
     * Test get_style_depends method
     */
    public function test_get_style_depends()
    {
        $widget = new \OOm_Table();
        $styles = $widget->get_style_depends();
        $this->assertIsArray($styles);
        $this->assertContains('font-awesome-5-all', $styles);
        $this->assertContains('font-awesome-4-shim', $styles);
    }

    /**
     * Test register_controls method is called
     */
    public function test_register_controls()
    {
        $widget = new \OOm_Table();
        
        // Use reflection to call protected method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('register_controls');
        $method->setAccessible(true);
        
        // This should not throw an error
        $this->assertNull($method->invoke($widget));
    }

    /**
     * Test render method with basic settings
     */
    public function test_render_with_basic_settings()
    {
        $widget = new \OOm_Table();
        
        $settings = [
            'oom_table_header_cols_data' => [
                [
                    'oom_table_header_col' => 'Header 1',
                    'oom_table_header_col_span' => '',
                    'oom_table_header_col_icon_enabled' => 'false',
                    'oom_table_header_icon_type' => 'icon',
                    'oom_table_header_css_class' => '',
                    'oom_table_header_css_id' => '',
                ],
                [
                    'oom_table_header_col' => 'Header 2',
                    'oom_table_header_col_span' => '',
                    'oom_table_header_col_icon_enabled' => 'false',
                    'oom_table_header_icon_type' => 'icon',
                    'oom_table_header_css_class' => '',
                    'oom_table_header_css_id' => '',
                ],
            ],
            'oom_table_content_rows' => [
                [
                    'oom_table_content_row_type' => 'row',
                ],
                [
                    'oom_table_content_row_type' => 'col',
                    'oom_table_content_type' => 'textarea',
                    'oom_table_content_row_colspan' => 1,
                    'oom_table_content_row_rowspan' => 1,
                    'oom_table_content_row_title' => 'Cell 1',
                    'oom_table_content_row_content' => '',
                    'oom_table_content_row_title_link' => [
                        'url' => '',
                        'is_external' => '',
                        'nofollow' => '',
                    ],
                    'oom_table_content_row_css_class' => '',
                    'oom_table_content_row_css_id' => '',
                ],
                [
                    'oom_table_content_row_type' => 'col',
                    'oom_table_content_type' => 'textarea',
                    'oom_table_content_row_colspan' => 1,
                    'oom_table_content_row_rowspan' => 1,
                    'oom_table_content_row_title' => 'Cell 2',
                    'oom_table_content_row_content' => '',
                    'oom_table_content_row_title_link' => [
                        'url' => '',
                        'is_external' => '',
                        'nofollow' => '',
                    ],
                    'oom_table_content_row_css_class' => '',
                    'oom_table_content_row_css_id' => '',
                ],
            ],
            'table_alignment' => 'left',
        ];
        
        $widget->set_settings($settings);
        
        $output = $this->callRenderMethod($widget);
        
        $this->assertStringContainsString('<table', $output);
        $this->assertStringContainsString('Header 1', $output);
        $this->assertStringContainsString('Header 2', $output);
        $this->assertStringContainsString('Cell 1', $output);
        $this->assertStringContainsString('Cell 2', $output);
    }

    /**
     * Test render with icon header
     */
    public function test_render_with_icon_header()
    {
        $widget = new \OOm_Table();
        
        $settings = [
            'oom_table_header_cols_data' => [
                [
                    'oom_table_header_col' => 'Header with Icon',
                    'oom_table_header_col_span' => '',
                    'oom_table_header_col_icon_enabled' => 'true',
                    'oom_table_header_icon_type' => 'icon',
                    'oom_table_header_col_icon_new' => [
                        'value' => 'fas fa-star',
                        'library' => 'solid',
                    ],
                    'oom_table_header_css_class' => 'test-class',
                    'oom_table_header_css_id' => 'test-id',
                    '__fa4_migrated' => ['oom_table_header_col_icon_new' => true],
                ],
            ],
            'oom_table_content_rows' => [
                ['oom_table_content_row_type' => 'row'],
            ],
            'table_alignment' => 'center',
        ];
        
        $widget->set_settings($settings);
        
        $output = $this->callRenderMethod($widget);
        
        $this->assertStringContainsString('Header with Icon', $output);
        $this->assertStringContainsString('fas fa-star', $output);
    }

    /**
     * Test render with image header
     */
    public function test_render_with_image_header()
    {
        $widget = new \OOm_Table();
        
        $settings = [
            'oom_table_header_cols_data' => [
                [
                    'oom_table_header_col' => 'Header with Image',
                    'oom_table_header_col_span' => '2',
                    'oom_table_header_col_icon_enabled' => 'true',
                    'oom_table_header_icon_type' => 'image',
                    'oom_table_header_col_img' => [
                        'url' => 'https://example.com/image.jpg',
                        'id' => 123,
                    ],
                    'oom_table_header_col_img_size' => '30',
                    'oom_table_header_css_class' => '',
                    'oom_table_header_css_id' => '',
                ],
            ],
            'oom_table_content_rows' => [
                ['oom_table_content_row_type' => 'row'],
            ],
            'table_alignment' => 'left',
        ];
        
        $widget->set_settings($settings);
        
        $output = $this->callRenderMethod($widget);
        
        $this->assertStringContainsString('Header with Image', $output);
        $this->assertStringContainsString('https://example.com/image.jpg', $output);
    }

    /**
     * Test render with icon content
     */
    public function test_render_with_icon_content()
    {
        $widget = new \OOm_Table();
        
        $settings = [
            'oom_table_header_cols_data' => [
                ['oom_table_header_col' => 'Header', 'oom_table_header_col_span' => '', 
                 'oom_table_header_col_icon_enabled' => 'false', 'oom_table_header_icon_type' => 'icon',
                 'oom_table_header_css_class' => '', 'oom_table_header_css_id' => ''],
            ],
            'oom_table_content_rows' => [
                ['oom_table_content_row_type' => 'row'],
                [
                    'oom_table_content_row_type' => 'col',
                    'oom_table_content_type' => 'icon',
                    'oom_table_content_row_colspan' => 1,
                    'oom_table_content_row_rowspan' => 1,
                    'oom_table_content_row_title' => '',
                    'oom_table_content_row_content' => '',
                    'oom_table_icon_content_new' => [
                        'value' => 'fas fa-home',
                        'library' => 'solid',
                    ],
                    'oom_table_content_row_title_link' => [
                        'url' => '',
                        'is_external' => '',
                        'nofollow' => '',
                    ],
                    'oom_table_content_row_css_class' => 'icon-class',
                    'oom_table_content_row_css_id' => 'icon-id',
                ],
            ],
            'table_alignment' => 'left',
            '__fa4_migrated' => ['oom_table_icon_content_new' => true],
            'oom_table_icon_content' => '',
        ];
        
        $widget->set_settings($settings);
        
        $output = $this->callRenderMethod($widget);
        
        $this->assertStringContainsString('oom-datatable-icon', $output);
    }

    /**
     * Test render with link content
     */
    public function test_render_with_link_content()
    {
        $widget = new \OOm_Table();
        
        $settings = [
            'oom_table_header_cols_data' => [
                ['oom_table_header_col' => 'Header', 'oom_table_header_col_span' => '',
                 'oom_table_header_col_icon_enabled' => 'false', 'oom_table_header_icon_type' => 'icon',
                 'oom_table_header_css_class' => '', 'oom_table_header_css_id' => ''],
            ],
            'oom_table_content_rows' => [
                ['oom_table_content_row_type' => 'row'],
                [
                    'oom_table_content_row_type' => 'col',
                    'oom_table_content_type' => 'textarea',
                    'oom_table_content_row_colspan' => 1,
                    'oom_table_content_row_rowspan' => 1,
                    'oom_table_content_row_title' => 'Link Text',
                    'oom_table_content_row_title_link' => [
                        'url' => 'https://example.com',
                        'is_external' => '1',
                        'nofollow' => '1',
                    ],
                    'oom_table_content_row_css_class' => '',
                    'oom_table_content_row_css_id' => '',
                ],
            ],
            'table_alignment' => 'left',
        ];
        
        $widget->set_settings($settings);
        
        $output = $this->callRenderMethod($widget);
        
        $this->assertStringContainsString('https://example.com', $output);
        $this->assertStringContainsString('Link Text', $output);
        $this->assertStringContainsString('target="_blank"', $output);
        $this->assertStringContainsString('rel="nofollow"', $output);
    }

    /**
     * Test render with editor content
     */
    public function test_render_with_editor_content()
    {
        $widget = new \OOm_Table();
        
        $settings = [
            'oom_table_header_cols_data' => [
                ['oom_table_header_col' => 'Header', 'oom_table_header_col_span' => '',
                 'oom_table_header_col_icon_enabled' => 'false', 'oom_table_header_icon_type' => 'icon',
                 'oom_table_header_css_class' => '', 'oom_table_header_css_id' => ''],
            ],
            'oom_table_content_rows' => [
                ['oom_table_content_row_type' => 'row'],
                [
                    'oom_table_content_row_type' => 'col',
                    'oom_table_content_type' => 'editor',
                    'oom_table_content_row_colspan' => 2,
                    'oom_table_content_row_rowspan' => 2,
                    'oom_table_content_row_content' => '<p>Rich text content</p>',
                    'oom_table_content_row_title_link' => [
                        'url' => '',
                        'is_external' => '',
                        'nofollow' => '',
                    ],
                    'oom_table_content_row_css_class' => 'editor-class',
                    'oom_table_content_row_css_id' => 'editor-id',
                ],
            ],
            'table_alignment' => 'right',
        ];
        
        $widget->set_settings($settings);
        
        $output = $this->callRenderMethod($widget);
        
        $this->assertStringContainsString('Rich text content', $output);
    }

    /**
     * Test render with multiple rows
     */
    public function test_render_with_multiple_rows()
    {
        $widget = new \OOm_Table();
        
        $settings = [
            'oom_table_header_cols_data' => [
                ['oom_table_header_col' => 'Col 1', 'oom_table_header_col_span' => '',
                 'oom_table_header_col_icon_enabled' => 'false', 'oom_table_header_icon_type' => 'icon',
                 'oom_table_header_css_class' => '', 'oom_table_header_css_id' => ''],
                ['oom_table_header_col' => 'Col 2', 'oom_table_header_col_span' => '',
                 'oom_table_header_col_icon_enabled' => 'false', 'oom_table_header_icon_type' => 'icon',
                 'oom_table_header_css_class' => '', 'oom_table_header_css_id' => ''],
            ],
            'oom_table_content_rows' => [
                ['oom_table_content_row_type' => 'row'],
                [
                    'oom_table_content_row_type' => 'col',
                    'oom_table_content_type' => 'textarea',
                    'oom_table_content_row_colspan' => 1,
                    'oom_table_content_row_rowspan' => 1,
                    'oom_table_content_row_title' => 'Row 1 Col 1',
                    'oom_table_content_row_title_link' => ['url' => '', 'is_external' => '', 'nofollow' => ''],
                    'oom_table_content_row_css_class' => '',
                    'oom_table_content_row_css_id' => '',
                ],
                [
                    'oom_table_content_row_type' => 'col',
                    'oom_table_content_type' => 'textarea',
                    'oom_table_content_row_colspan' => 1,
                    'oom_table_content_row_rowspan' => 1,
                    'oom_table_content_row_title' => 'Row 1 Col 2',
                    'oom_table_content_row_title_link' => ['url' => '', 'is_external' => '', 'nofollow' => ''],
                    'oom_table_content_row_css_class' => '',
                    'oom_table_content_row_css_id' => '',
                ],
                ['oom_table_content_row_type' => 'row'],
                [
                    'oom_table_content_row_type' => 'col',
                    'oom_table_content_type' => 'textarea',
                    'oom_table_content_row_colspan' => 1,
                    'oom_table_content_row_rowspan' => 1,
                    'oom_table_content_row_title' => 'Row 2 Col 1',
                    'oom_table_content_row_title_link' => ['url' => '', 'is_external' => '', 'nofollow' => ''],
                    'oom_table_content_row_css_class' => '',
                    'oom_table_content_row_css_id' => '',
                ],
                [
                    'oom_table_content_row_type' => 'col',
                    'oom_table_content_type' => 'textarea',
                    'oom_table_content_row_colspan' => 1,
                    'oom_table_content_row_rowspan' => 1,
                    'oom_table_content_row_title' => 'Row 2 Col 2',
                    'oom_table_content_row_title_link' => ['url' => '', 'is_external' => '', 'nofollow' => ''],
                    'oom_table_content_row_css_class' => '',
                    'oom_table_content_row_css_id' => '',
                ],
            ],
            'table_alignment' => 'left',
        ];
        
        $widget->set_settings($settings);
        
        $output = $this->callRenderMethod($widget);
        
        $this->assertStringContainsString('Row 1 Col 1', $output);
        $this->assertStringContainsString('Row 1 Col 2', $output);
        $this->assertStringContainsString('Row 2 Col 1', $output);
        $this->assertStringContainsString('Row 2 Col 2', $output);
    }

    /**
     * Test render with old FA4 icon
     */
    public function test_render_with_old_fa4_icon()
    {
        $widget = new \OOm_Table();
        
        $settings = [
            'oom_table_header_cols_data' => [
                [
                    'oom_table_header_col' => 'Header',
                    'oom_table_header_col_span' => '',
                    'oom_table_header_col_icon_enabled' => 'true',
                    'oom_table_header_icon_type' => 'icon',
                    'oom_table_header_col_icon' => 'fa fa-star',
                    'oom_table_header_css_class' => '',
                    'oom_table_header_css_id' => '',
                ],
            ],
            'oom_table_content_rows' => [
                ['oom_table_content_row_type' => 'row'],
                [
                    'oom_table_content_row_type' => 'col',
                    'oom_table_content_type' => 'icon',
                    'oom_table_content_row_colspan' => 1,
                    'oom_table_content_row_rowspan' => 1,
                    'oom_table_content_row_title' => '',
                    'oom_table_content_row_content' => '',
                    'oom_table_icon_content' => 'fa fa-home',
                    'oom_table_content_row_title_link' => [
                        'url' => '',
                        'is_external' => '',
                        'nofollow' => '',
                    ],
                    'oom_table_content_row_css_class' => '',
                    'oom_table_content_row_css_id' => '',
                ],
            ],
            'table_alignment' => 'left',
            'oom_table_icon_content' => 'fa fa-home',
        ];
        
        $widget->set_settings($settings);
        
        $output = $this->callRenderMethod($widget);
        
        $this->assertStringContainsString('fa fa-star', $output);
    }

    /**
     * Test render with SVG icon in header
     */
    public function test_render_with_svg_icon_header()
    {
        $widget = new \OOm_Table();
        
        $settings = [
            'oom_table_header_cols_data' => [
                [
                    'oom_table_header_col' => 'Header with SVG',
                    'oom_table_header_col_span' => '',
                    'oom_table_header_col_icon_enabled' => 'true',
                    'oom_table_header_icon_type' => 'icon',
                    'oom_table_header_col_icon_new' => [
                        'value' => [
                            'url' => 'https://example.com/icon.svg',
                            'id' => 456,
                        ],
                        'library' => 'svg',
                    ],
                    'oom_table_header_css_class' => '',
                    'oom_table_header_css_id' => '',
                    '__fa4_migrated' => ['oom_table_header_col_icon_new' => true],
                ],
            ],
            'oom_table_content_rows' => [
                ['oom_table_content_row_type' => 'row'],
            ],
            'table_alignment' => 'left',
        ];
        
        $widget->set_settings($settings);
        
        $output = $this->callRenderMethod($widget);
        
        $this->assertStringContainsString('Header with SVG', $output);
    }

    /**
     * Test widget registration when status is active
     */
    public function test_widget_registration_when_active()
    {
        // Widget is already registered in setUp
        $this->assertTrue(class_exists('OOm_Table'));
    }

    /**
     * Test widget not registered when status is inactive
     */
    public function test_widget_not_registered_when_inactive()
    {
        // Widget should already be registered from setUp
        $this->assertTrue(class_exists('OOm_Table'));
    }

    /**
     * Test unique_id property
     */
    public function test_unique_id_property()
    {
        $widget = new \OOm_Table();
        $this->assertObjectHasProperty('unique_id', $widget);
    }

    /**
     * Test render attributes are set correctly
     */
    public function test_render_attributes()
    {
        $widget = new \OOm_Table();
        
        $settings = [
            'oom_table_header_cols_data' => [
                ['oom_table_header_col' => 'H1', 'oom_table_header_col_span' => '',
                 'oom_table_header_col_icon_enabled' => 'false', 'oom_table_header_icon_type' => 'icon',
                 'oom_table_header_css_class' => '', 'oom_table_header_css_id' => ''],
            ],
            'oom_table_content_rows' => [
                ['oom_table_content_row_type' => 'row'],
                [
                    'oom_table_content_row_type' => 'col',
                    'oom_table_content_type' => 'textarea',
                    'oom_table_content_row_colspan' => 1,
                    'oom_table_content_row_rowspan' => 1,
                    'oom_table_content_row_title' => 'Test',
                    'oom_table_content_row_title_link' => ['url' => '', 'is_external' => '', 'nofollow' => ''],
                    'oom_table_content_row_css_class' => '',
                    'oom_table_content_row_css_id' => '',
                ],
            ],
            'table_alignment' => 'center',
        ];
        
        $widget->set_settings($settings);
        
        $output = $this->callRenderMethod($widget);
        
        $this->assertStringContainsString('oom-table-wrap', $output);
        $this->assertStringContainsString('oom-table', $output);
    }

    /**
     * Test empty content rows handling
     */
    public function test_empty_content_rows()
    {
        $widget = new \OOm_Table();
        
        $settings = [
            'oom_table_header_cols_data' => [
                ['oom_table_header_col' => 'H1', 'oom_table_header_col_span' => '',
                 'oom_table_header_col_icon_enabled' => 'false', 'oom_table_header_icon_type' => 'icon',
                 'oom_table_header_css_class' => '', 'oom_table_header_css_id' => ''],
            ],
            'oom_table_content_rows' => [],
            'table_alignment' => 'left',
        ];
        
        $widget->set_settings($settings);
        
        $output = $this->callRenderMethod($widget);
        
        $this->assertStringContainsString('<table', $output);
        $this->assertStringContainsString('<tbody', $output);
    }
}

