<?php

/**
 * OOm Form Widgets
 * Custom Elementor Widgets
 * @version  		1.6.1
 * @widget_version	1.1.0
 * @author 			oom_cn
 */

use \Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

final class Oom_Form {

    /**
     * Widget Version
     *
     * @since 1.0.0
     *
     * @var string The widget version.
     */
    const VERSION = '1.0.0';

    /**
     * Minimum Elementor Version
     *
     * @since 1.0.0
     *
     * @var string Minimum Elementor version required to run the widget.
     */
    const MINIMUM_ELEMENTOR_VERSION = '2.5.11';

    /**
     * Minimum PHP Version
     *
     * @since 1.0.0
     *
     * @var string Minimum PHP version required to run the widget.
     */
    const MINIMUM_PHP_VERSION = '6.0';

    /**
     * Instance
     *
     * @since 1.0.0
     *
     * @access private
     * @static
     *
     * The single instance of the class.
     */
    protected static $instance = null;

    public static function get_instance() {
        if ( ! isset( static::$instance ) ) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Constructor
     *
     * @since 1.0.0
     *
     * @access public
     */
    protected function __construct() {

        // Check for required PHP version
        if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
            return;
        }

		require_once dirname( __FILE__ ). '/widgets/oom-form/oom-form.php';

        // Register Widget Styles
        add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'widget_styles' ] );

        // Register Widget Scripts
        add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'widget_scripts' ] );

        add_filter( 'script_loader_tag', [ $this, 'add_type_attribute'], 10, 3 );

        // Check Elementor and Elementor Pro after the plugin is already active.
        add_action( 'admin_init', [ $this, 'check_required_plugins' ] );
    }

    // Enqueue styles
    public function widget_styles() {
        // Enqueue the form styles
        wp_enqueue_style( 'oom-form-css', get_stylesheet_directory_uri() . '/oom/widgets/oom-elementor-form/widgets/oom-form/css/oom-form.css' );

        // Enqueue intlTelInput styles
        wp_enqueue_style( 'oom-form-intlTelInput', get_stylesheet_directory_uri() . '/oom/widgets/oom-elementor-form/widgets/oom-form/css/intlTelInput.css' );
    }

    // Enqueue scripts
    public function widget_scripts() {
        // Enqueue intlTelInput JS
        wp_enqueue_script( 'oom-form-intlTelInput', get_stylesheet_directory_uri() . '/oom/widgets/oom-elementor-form/widgets/oom-form/js/intlTelInput.min.js', array( 'jquery' ), null, true );

        // Enqueue the utility script
        wp_enqueue_script( 'oom-form-intlTelInput-utils', get_stylesheet_directory_uri() . '/oom/widgets/oom-elementor-form/widgets/oom-form/js/utils.js', array( 'oom-form-intlTelInput' ), null, true );

        // Enqueue any additional form scripts
        wp_enqueue_script( 'oom-form-js', get_stylesheet_directory_uri() . '/oom/widgets/oom-elementor-form/widgets/oom-form/js/oom-form.js', array( 'jquery' ), null, true );
    }

    public function add_type_attribute($tag, $handle, $src) {
        // if not your script, do nothing and return original $tag
        if ( 'oom-form-intlTelInput-utils' !== $handle ) {
            return $tag;
        }
        // change the script tag by adding type="module" and return it.
        $tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
        return $tag;
    }

    /**
     * Check if Elementor and Elementor Pro are activated after plugin activation.
     */
    public function check_required_plugins() {
        // Check if Elementor is installed and activated.
        if ( ! did_action( 'elementor/loaded' ) ) {
            // Elementor is not activated, so deactivate this plugin.
            add_action( 'admin_notices', [ $this, 'admin_notice_missing_elementor' ] );
        }

        // Check if Elementor Pro is required and is not activated.
        if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
            // Elementor Pro is not activated, so deactivate this plugin.
            add_action( 'admin_notices', [ $this, 'admin_notice_missing_elementor_pro' ] );
        }
    }

    /**
     * Display admin notice if Elementor is missing.
     */
    public function admin_notice_missing_elementor() {
        $message = sprintf(
            esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'oom-elementor-form' ),
            '<strong>' . esc_html__( 'OOm Form', 'oom-elementor-form' ) . '</strong>',
            '<strong>' . esc_html__( 'Elementor', 'oom-elementor-form' ) . '</strong>'
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
    }

    /**
     * Display admin notice if Elementor Pro is missing.
     */
    public function admin_notice_missing_elementor_pro() {
        $message = sprintf(
            esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'oom-elementor-form' ),
            '<strong>' . esc_html__( 'OOm Form', 'oom-elementor-form' ) . '</strong>',
            '<strong>' . esc_html__( 'Elementor Pro', 'oom-elementor-form' ) . '</strong>'
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have a minimum required PHP version.
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function admin_notice_minimum_php_version() {

        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

        $message = sprintf(
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'oom-elementor-form' ),
            '<strong>' . esc_html__( 'OOm Form', 'oom-elementor-form' ) . '</strong>',
            '<strong>' . esc_html__( 'PHP', 'oom-elementor-form' ) . '</strong>',
            self::MINIMUM_PHP_VERSION
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
    }
}

$oom_form_status = get_option('oom_form_status');
if($oom_form_status == 'active'){
	// Include additional files
	require_once( __DIR__ . '/widgets/oom-form/inc/oom-form-functions.php' );
	require_once( __DIR__ . '/admin/oom-form-database.php' );
	require_once( __DIR__ . '/admin/oom-form-submission-page.php' );
	
	// Initialize the class directly in the child theme
	Oom_Form::get_instance();
}
