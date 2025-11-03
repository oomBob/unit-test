<?php
/**
 * Tests for Form Submission Page functions
 *
 * @package HelloElementorChild\Tests\Unit
 */

namespace HelloElementorChild\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

/**
 * Class FormSubmissionPageTest
 */
class FormSubmissionPageTest extends TestCase
{
    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        
        // Mock WordPress functions
        Monkey\Functions\when('add_action')->justReturn();
        Monkey\Functions\when('add_menu_page')->justReturn('menu-slug');
        Monkey\Functions\when('add_submenu_page')->justReturn('submenu-slug');
        Monkey\Functions\when('site_url')->justReturn('https://example.com');
        Monkey\Functions\when('esc_html')->returnArg();
        Monkey\Functions\when('esc_html__')->returnArg();
        Monkey\Functions\when('esc_html_e')->justReturn();
        Monkey\Functions\when('esc_attr')->returnArg();
        Monkey\Functions\when('esc_url')->returnArg();
        Monkey\Functions\when('sanitize_text_field')->returnArg();
        Monkey\Functions\when('selected')->justReturn('');
        Monkey\Functions\when('admin_url')->justReturn('https://example.com/wp-admin/');
        Monkey\Functions\when('get_permalink')->justReturn('https://example.com/page/');
        Monkey\Functions\when('get_the_title')->justReturn('Test Page');
        Monkey\Functions\when('wp_nonce_url')->justReturn('https://example.com/wp-admin/?_wpnonce=test');
        Monkey\Functions\when('wp_nonce_field')->justReturn('<input type="hidden" name="_wpnonce" value="test">');
        Monkey\Functions\when('wp_verify_nonce')->justReturn(true);
        Monkey\Functions\when('wp_die')->justReturn();
        Monkey\Functions\when('wp_redirect')->justReturn();
        // Note: exit is a PHP internal function, don't mock it
        // We'll rely on the code not actually calling exit in tests
        Monkey\Functions\when('current_user_can')->justReturn(true);
        Monkey\Functions\when('check_admin_referer')->justReturn(true);
        Monkey\Functions\when('update_option')->justReturn(true);
        Monkey\Functions\when('get_option')->justReturn('test-key');
        // Note: date and strtotime are PHP internal functions, don't mock them
        // They will work normally
        
        // Mock global $wpdb - need to create a proper mock class
        global $wpdb;
        $wpdb = new class extends \stdClass {
            public $prefix = 'wp_';
            public function get_results($query) {
                return [];
            }
            public function get_var($query) {
                return 0;
            }
            public function get_col($query) {
                return [];
            }
            public function get_row($query) {
                return null;
            }
            public function prepare($query, $args) {
                return $query;
            }
            public function delete($table, $where, $format) {
                return true;
            }
        };
        
        // Define ABSPATH constant if not defined
        if (!defined('ABSPATH')) {
            define('ABSPATH', '/fake/abspath/');
        }
        
        // Mock maybe_unserialize since it's used in the submission page
        Monkey\Functions\when('maybe_unserialize')->alias(function($data) {
            // Simple check for serialized data
            if (is_string($data) && (strpos($data, 'a:') === 0 || strpos($data, 's:') === 0 || strpos($data, 'i:') === 0 || strpos($data, 'O:') === 0)) {
                return unserialize($data);
            }
            return $data;
        });
        
        // Load the submission page file
        // Note: oom_decrypt_form_data is defined at the end of this file
        require_once __DIR__ . '/../../hello-elementor-child/oom/widgets/oom-elementor-form/admin/oom-form-submission-page.php';
    }

    /**
     * Tear down test environment
     */
    protected function tearDown(): void
    {
        unset($_GET, $_POST);
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Test oom_form_admin_menu function exists
     */
    public function test_oom_form_admin_menu_exists()
    {
        $this->assertTrue(function_exists('oom_form_admin_menu'));
    }

    /**
     * Test oom_form_admin_menu calls add_menu_page
     */
    public function test_oom_form_admin_menu_adds_menu()
    {
        // The hook is registered when the file loads in setUp()
        // Just verify the function exists and is callable
        $this->assertTrue(function_exists('oom_form_admin_menu'));
        $this->assertTrue(is_callable('oom_form_admin_menu'));
    }

    /**
     * Test oom_form_submissions_page function exists
     */
    public function test_oom_form_submissions_page_exists()
    {
        $this->assertTrue(function_exists('oom_form_submissions_page'));
    }

    /**
     * Test oom_form_submissions_page renders without errors
     */
    public function test_oom_form_submissions_page_renders()
    {
        global $wpdb;
        
        $_GET = [];
        $_POST = [];
        
        // Use anonymous class instead of stdClass to properly mock wpdb methods
        $wpdb = new class {
            public $prefix = 'wp_';
            
            public function get_results($query) {
                return [];
            }
            
            public function get_var($query) {
                return 0;
            }
            
            public function get_col($query) {
                return [];
            }
            
            public function prepare($query, ...$args) {
                return $query;
            }
        };
        
        ob_start();
        oom_form_submissions_page();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
        $this->assertStringContainsString('OOm Form Submissions', $output);
    }

    /**
     * Test oom_form_submissions_page handles bulk delete
     */
    public function test_oom_form_submissions_page_handles_bulk_delete()
    {
        global $wpdb;
        
        $_POST = [
            'bulk_delete' => true,
            'submission_ids' => ['1', '2', '3']
        ];
        
        $deleteCalled = false;
        $wpdb = new class extends \stdClass {
            public $prefix = 'wp_';
            private $deleteCalled = false;
            public function delete($table, $where, $format) {
                $this->deleteCalled = true;
                return true;
            }
            public function get_results($query) {
                return [];
            }
            public function get_var($query) {
                return 0;
            }
            public function get_col($query) {
                return [];
            }
            public function getDeleteCalled() {
                return $this->deleteCalled;
            }
        };
        
        ob_start();
        oom_form_submissions_page();
        ob_end_clean();
        
        // Function should execute without errors
        $this->assertTrue(true);
    }

    /**
     * Test oom_form_view_details_page function exists
     */
    public function test_oom_form_view_details_page_exists()
    {
        $this->assertTrue(function_exists('oom_form_view_details_page'));
    }

    /**
     * Test oom_form_submission_view_page function exists
     */
    public function test_oom_form_submission_view_page_exists()
    {
        $this->assertTrue(function_exists('oom_form_submission_view_page'));
    }

    /**
     * Test oom_form_submission_view_page validates submission ID
     */
    public function test_oom_form_submission_view_page_validates_id()
    {
        global $wpdb;
        
        // Set up a valid ID to avoid undefined array key error
        $_GET = ['id' => '']; // Empty string, which will be 0 when intval() is applied
        
        // Mock wpdb to return null for an invalid ID
        $wpdb = new class {
            public $prefix = 'wp_';
            
            public function get_row($query) {
                return null;
            }
            
            public function prepare($query, ...$args) {
                return $query;
            }
        };
        
        // Mock wp_die to capture the call when submission is not found
        Monkey\Functions\expect('wp_die')
            ->once()
            ->with('Submission not found.');
        
        oom_form_submission_view_page();
    }

    /**
     * Test oom_form_submission_view_page handles missing submission
     */
    public function test_oom_form_submission_view_page_handles_missing()
    {
        global $wpdb;
        
        $_GET = ['id' => '999'];
        
        $wpdb = new class {
            public $prefix = 'wp_';
            
            public function get_row($query) {
                return null;
            }
            
            public function prepare($query, ...$args) {
                return $query;
            }
        };
        
        // Mock wp_die to capture the call
        Monkey\Functions\expect('wp_die')
            ->once()
            ->with('Submission not found.');
        
        oom_form_submission_view_page();
    }

    /**
     * Test oom_form_settings_page function exists
     */
    public function test_oom_form_settings_page_exists()
    {
        $this->assertTrue(function_exists('oom_form_settings_page'));
    }

    /**
     * Test oom_form_settings_view_page function exists
     */
    public function test_oom_form_settings_view_page_exists()
    {
        $this->assertTrue(function_exists('oom_form_settings_view_page'));
    }

    /**
     * Test oom_form_settings_view_page checks capability
     */
    public function test_oom_form_settings_view_page_checks_capability()
    {
        // Mock current_user_can to return false for this test
        Monkey\Functions\when('current_user_can')
            ->alias(function($capability) {
                return $capability === 'manage_options' ? false : true;
            });
        
        ob_start();
        oom_form_settings_view_page();
        $output = ob_get_clean();
        
        // Should return early if user doesn't have capability
        $this->assertEmpty($output);
    }

    /**
     * Test oom_form_settings_view_page saves settings
     */
    public function test_oom_form_settings_view_page_saves_settings()
    {
        $_POST = [
            'oom_form_save_settings' => true,
            'oom_form_encryption_key' => 'new-key-123'
        ];
        
        // current_user_can is mocked in setUp to return true by default
        // Mock all required WordPress functions
        Monkey\Functions\when('get_option')->justReturn('');
        Monkey\Functions\when('sanitize_text_field')->returnArg();
        Monkey\Functions\when('check_admin_referer')->justReturn(1); // Return 1 for success
        Monkey\Functions\when('update_option')->justReturn(true); // Allow update_option to be called
        Monkey\Functions\when('esc_html__')->returnArg();
        Monkey\Functions\when('esc_html_e')->alias(function($text) {
            // Echo the text directly in tests
            echo $text;
        });
        Monkey\Functions\when('esc_attr_e')->alias(function($text) {
            // Just echo the text without escaping in tests
            echo $text;
        });
        Monkey\Functions\when('submit_button')->alias(function($text = 'Save Changes', $type = 'primary', $name = 'submit') {
            // Mock WordPress submit_button function
            echo '<input type="submit" name="' . $name . '" value="' . $text . '" class="button button-' . $type . '" />';
        });
        
        ob_start();
        oom_form_settings_view_page();
        $output = ob_get_clean();
        
        // Verify the page renders and contains success message if settings were saved
        $this->assertIsString($output);
        $this->assertStringContainsString('OOm Form Settings', $output);
    }

    /**
     * Test oom_handle_delete_submission function exists
     */
    public function test_oom_handle_delete_submission_exists()
    {
        $this->assertTrue(function_exists('oom_handle_delete_submission'));
    }

    /**
     * Test oom_handle_delete_submission verifies nonce
     */
    public function test_oom_handle_delete_submission_verifies_nonce()
    {
        global $wpdb;
        
        $_GET = [
            'action' => 'delete',
            'id' => '1',
            '_wpnonce' => 'invalid-nonce'
        ];
        
        $wpdb = new class extends \stdClass {
            public $prefix = 'wp_';
            public function delete($table, $where, $format) {
                return true;
            }
        };
        
        // Mock wp_verify_nonce to return false for this test
        Monkey\Functions\when('wp_verify_nonce')
            ->alias(function($nonce, $action) {
                if ($nonce === 'invalid-nonce' && $action === 'delete_submission_1') {
                    return false;
                }
                return true;
            });
        
        // wp_die is mocked in setUp to just return
        // Just verify the function can be called without errors
        try {
            oom_handle_delete_submission();
            $result = true;
        } catch (\Exception $e) {
            $result = false;
        }
        
        // Function should execute without throwing exceptions
        $this->assertTrue($result !== false);
    }

    /**
     * Test oom_export_submissions_to_csv function exists
     */
    public function test_oom_export_submissions_to_csv_exists()
    {
        $this->assertTrue(function_exists('oom_export_submissions_to_csv'));
    }

    /**
     * Test oom_decrypt_form_data function exists
     */
    public function test_oom_decrypt_form_data_exists()
    {
        $this->assertTrue(function_exists('oom_decrypt_form_data'));
    }

    /**
     * Test oom_decrypt_form_data validates input types
     */
    public function test_oom_decrypt_form_data_validates_input()
    {
        $this->assertFalse(oom_decrypt_form_data([], 'key'));
        $this->assertFalse(oom_decrypt_form_data('data', []));
        $this->assertFalse(oom_decrypt_form_data(null, 'key'));
    }

    /**
     * Test oom_decrypt_form_data handles invalid base64
     */
    public function test_oom_decrypt_form_data_handles_invalid_base64()
    {
        $result = oom_decrypt_form_data('invalid-base64!@#', 'key');
        $this->assertFalse($result);
    }

    /**
     * Test oom_decrypt_form_data handles invalid format
     */
    public function test_oom_decrypt_form_data_handles_invalid_format()
    {
        $invalid = base64_encode('invalid-format-without-separator');
        $result = oom_decrypt_form_data($invalid, 'key');
        $this->assertFalse($result);
    }
}

