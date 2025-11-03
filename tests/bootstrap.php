<?php
/**
 * Bootstrap file for PHPUnit tests
 *
 * @package HelloElementorChild\Tests
 */

// Autoload Composer dependencies
require_once __DIR__ . '/../vendor/autoload.php';

// Define WordPress test constants
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../wordpress/');
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', __DIR__ . '/../wp-content/');
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
// register_shutdown_function(function () {
//     Brain\Monkey\tearDown();
// });

