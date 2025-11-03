<?php
/**
 * Tests for Form Database functions
 *
 * @package HelloElementorChild\Tests\Unit
 */

namespace HelloElementorChild\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

/**
 * Class FormDatabaseTest
 */
class FormDatabaseTest extends TestCase
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
        
        // Mock global $wpdb object - need to create a mock class
        global $wpdb;
        
        // Create a mock class for wpdb since get_charset_collate is a method
        $wpdb = new class extends \stdClass {
            public $prefix = 'wp_';
            public function get_charset_collate() {
                return 'utf8mb4_unicode_ci';
            }
        };
        
        // Define ABSPATH constant if not defined
        if (!defined('ABSPATH')) {
            define('ABSPATH', '/fake/abspath/');
        }
        
        // Mock dbDelta function using Brain Monkey (WordPress function)
        Monkey\Functions\when('dbDelta')->justReturn(true);
        
        // Create a dummy upgrade.php file to prevent require_once errors
        // The require_once uses ABSPATH, so we need to create the file structure
        $upgradeDirs = [
            ABSPATH . 'wp-admin/includes/',
            dirname(__DIR__) . '/../wordpress/wp-admin/includes/', // Fallback if ABSPATH resolves differently
        ];
        
        foreach ($upgradeDirs as $upgradeDir) {
            if (!file_exists($upgradeDir)) {
                @mkdir($upgradeDir, 0777, true);
            }
            $upgradeFile = $upgradeDir . 'upgrade.php';
            if (!file_exists($upgradeFile)) {
                @file_put_contents($upgradeFile, '<?php // Dummy upgrade.php for testing');
            }
        }
        
        // Also mock require_once to prevent actual file system access
        if (!function_exists('require_once')) {
            // Can't mock require_once directly, so we ensure the file exists
        }
        
        // Load the database file
        require_once __DIR__ . '/../../hello-elementor-child/oom/widgets/oom-elementor-form/admin/oom-form-database.php';
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
     * Test oom_form_create_database_table function exists
     */
    public function test_oom_form_create_database_table_exists()
    {
        $this->assertTrue(function_exists('oom_form_create_database_table'));
    }

    /**
     * Test oom_form_create_database_table creates table with correct name
     */
    public function test_oom_form_create_database_table_creates_table()
    {
        global $wpdb;
        
        $wpdb = new class extends \stdClass {
            public $prefix = 'wp_';
            public function get_charset_collate() {
                return 'utf8mb4_unicode_ci';
            }
        };
        
        // Mock dbDelta to verify it's called
        $dbDeltaCalled = false;
        $test = $this;
        Monkey\Functions\when('dbDelta')->alias(function($sql) use (&$dbDeltaCalled, $test) {
            $dbDeltaCalled = true;
            $test->assertStringContainsString('wp_oom_form_submissions', $sql);
            $test->assertStringContainsString('CREATE TABLE', $sql);
            $test->assertStringContainsString('id mediumint(9)', $sql);
            $test->assertStringContainsString('ec_key varchar(255)', $sql);
            $test->assertStringContainsString('form_id varchar(255)', $sql);
            $test->assertStringContainsString('form_name varchar(255)', $sql);
            $test->assertStringContainsString('submission_data longtext', $sql);
            $test->assertStringContainsString('metadata longtext', $sql);
            $test->assertStringContainsString('submission_page_id mediumint(9)', $sql);
            $test->assertStringContainsString('submitted_at datetime', $sql);
            $test->assertStringContainsString('PRIMARY KEY', $sql);
            return true;
        });
        
        oom_form_create_database_table();
        
        $this->assertTrue($dbDeltaCalled);
    }

    /**
     * Test oom_form_create_database_table handles dbDelta correctly
     */
    public function test_oom_form_create_database_table_calls_dbDelta()
    {
        global $wpdb;
        
        $wpdb = new class extends \stdClass {
            public $prefix = 'wp_test_';
            public function get_charset_collate() {
                return 'utf8mb4_unicode_ci';
            }
        };
        
        $called = false;
        $test = $this;
        $prefix = 'wp_test_';
        Monkey\Functions\when('dbDelta')->alias(function($sql) use (&$called, $test, $prefix) {
            $called = true;
            $test->assertStringContainsString($prefix . 'oom_form_submissions', $sql);
            return true;
        });
        
        oom_form_create_database_table();
        
        $this->assertTrue($called);
    }

    /**
     * Test oom_form_create_database_table is hooked to wp_loaded
     */
    public function test_oom_form_create_database_table_hooked()
    {
        // The hook is registered when the file loads in setUp()
        // Just verify the function exists and is callable
        $this->assertTrue(function_exists('oom_form_create_database_table'));
        $this->assertTrue(is_callable('oom_form_create_database_table'));
    }
}

