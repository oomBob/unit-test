<?php
/**
 * Bootstrap file for PHPUnit tests - For running from wp-content/themes/ folder
 *
 * This bootstrap is designed to work when tests are run from:
 * wp-content/themes/
 * ├── hello-elementor-child/    (theme files)
 * ├── tests/                    (test files)
 * ├── vendor/                   (dependencies)
 * └── phpunit-themes.xml        (PHPUnit config)
 *
 * @package HelloElementorChild\Tests
 */

// Autoload Composer dependencies (vendor is in same directory as tests)
require_once __DIR__ . '/../vendor/autoload.php';

// Define WordPress test constants (relative to themes folder)
// These paths assume WordPress is one level up from themes
if (!defined('ABSPATH')) {
    // wp-content/themes -> wp-content -> wordpress (typical structure)
    define('ABSPATH', __DIR__ . '/../../wordpress/');
}

if (!defined('WP_CONTENT_DIR')) {
    // wp-content/themes -> wp-content
    define('WP_CONTENT_DIR', __DIR__ . '/../');
}

// Define theme constants
if (!defined('OOM_THEME_VERSION')) {
    define('OOM_THEME_VERSION', '1.6.0');
}

// Load Brain Monkey
Brain\Monkey\setUp();

// Load WordPress test functions if available
if (file_exists(__DIR__ . '/../vendor/wp-phpunit/wp-phpunit/includes/functions.php')) {
    require_once __DIR__ . '/../vendor/wp-phpunit/wp-phpunit/includes/functions.php';
}

// Clean up after tests
// Note: Moved tearDown to individual test classes to ensure PHPUnit summary is printed

