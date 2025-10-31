<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'OOM_THEME_VERSION' ) ) {
  define( 'OOM_THEME_VERSION', '1.6.0' ); //DEFINED THEME VERSION.
}

/**
 * Load child theme css and optional scripts
 *
 * @return void
 */
function hello_elementor_child_enqueue_scripts() {
	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[
			'hello-elementor-theme-style',
		],
		OOM_THEME_VERSION
	);
	//Mmenu Light CSS
  	wp_enqueue_style('oom-mmenu', get_template_directory_uri() . '-child/assets/mmenu-light.css', array(), OOM_THEME_VERSION, 'all' );

	// jQuery UI base theme (needed for Datepicker visuals)
	wp_enqueue_style(
		'jquery-ui-base-style',
		'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css',
		array(),
		'1.13.2'
	);
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_scripts', 20 );


/*
 * oom_ss
 */
 
/**
  * Disable for posts, post types and widgets
  * @author oom_ss 
  * @version  1.0.0
 */
add_filter('use_block_editor_for_post', '__return_false', 10);
add_filter('use_block_editor_for_post_type', '__return_false', 10);
add_filter( 'use_widgets_block_editor', '__return_false' );


/**
  * Register Ajax Object, Style and Scripts
  * @author oom_ss 
  * @version  1.0.0
 */
add_action( 'wp_enqueue_scripts', 'enqueue_script', 100 );
function enqueue_script() {
    wp_enqueue_script( 'jquery' );
    // Ensure jQuery UI Datepicker is available
    wp_enqueue_script( 'jquery-ui-datepicker' );
    //Engueue Child Theme Main Js
    wp_enqueue_script(
    	'oom-main',
    	get_template_directory_uri() . '-child/assets/main.js',
    	array('jquery','jquery-ui-datepicker'),
    	rand(111,9999),
    	true
    );
	//Mmenu Light JS
    wp_enqueue_script('oom-mmenu-light', get_template_directory_uri() . '-child/assets/mmenu-light.js', array(), OOM_THEME_VERSION, 'all' );
    // Prepare blockout dates for JS (dd-mm-yyyy)
    $oom_blockout_dates_raw = get_option( 'oom_blockout_dates', '' );
    $oom_blockout_dates_arr = array();
    if ( ! empty( $oom_blockout_dates_raw ) ) {
        $dates_array = array_map( 'trim', explode( ',', $oom_blockout_dates_raw ) );
        foreach ( $dates_array as $date ) {
            if ( preg_match( '/^\d{2}-\d{2}-\d{4}$/', $date ) ) {
                $oom_blockout_dates_arr[] = $date;
            }
        }
    }

    // The wp_localize_script allows us to output the ajax_url path for our script to use.
	wp_localize_script(
		'oom-main',
		'oom_ajax_obj',
		array(
		    'site_url' => site_url(),
		    'rest_url' => esc_url_raw( rest_url() ),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'oom-nonce' ),
			'advanced_booking_days' => get_option( 'oom_advanced_booking_days', '2' ),
			'blockout_dates' => $oom_blockout_dates_arr
		)
	);
}

/**
  * Load Custom Functions
  * @author oom_ss 
  * @version  1.0.0
 */
require_once dirname( __FILE__ ). '/oom/oom-optimization-security.php';
require_once dirname( __FILE__ ). '/oom/oom-global-shortcode.php';
require_once dirname( __FILE__ ). '/oom/oom-custom-shortcode.php';
require_once dirname( __FILE__ ). '/oom/oom-rest-api-v1.php';
require_once dirname( __FILE__ ). '/oom/oom-theme-options.php';
require_once dirname( __FILE__ ). '/oom/widgets/oom-table-widget/oom-table-widget.php';
require_once dirname( __FILE__ ). '/oom/widgets/oom-elementor-form/oom-form.php';

/**
 * Conditionally enqueue Google Places (and jQuery UI core) only on pages containing rental shortcodes
 */
add_action('wp_enqueue_scripts', function() {
    if (is_admin()) return;
    if (!is_singular()) return;

    global $post;
    if (!$post) return;

    $content = $post->post_content ?? '';
    $has_daily = has_shortcode($content, 'oom_daily_rental_form');
    $has_single = has_shortcode($content, 'oom_single_rental_form');

    if ($has_daily || $has_single) {
        // Ensure jQuery UI core (for Datepicker which is already enqueued) is available
        wp_enqueue_script('jquery-ui-core');

        // Load Google Places once via wp_enqueue_scripts (shortcodes won’t inline it)
        $api_key = get_option('oom_google_place_api', '');
        if (!empty($api_key)) {
            wp_enqueue_script(
                'google-maps-places',
                'https://maps.googleapis.com/maps/api/js?key=' . rawurlencode($api_key) . '&libraries=places',
                array(),
                null,
                true
            );
        }

        // Load Swiper JS once if not already added by plugins (CSS is added in head). Optional.
        wp_enqueue_script(
            'oom-swiper',
            get_stylesheet_directory_uri() . '/assets/swiper-bundle.min.js',
            array(),
            OOM_THEME_VERSION,
            true
        );
    }
}, 110);

/**
  * Disable Page Title of Hello Theme
  * @author oom_ss 
  * @version  1.0.0
 */
add_filter( 'hello_elementor_page_title', '__return_false' );



/**
  * Hook in Header
  * @author oom_ss 
  * @version  1.0.0
 */
add_action ( 'wp_head', 'oom_custom_head' );
function oom_custom_head() {
	?>
    <!-- Custom CSS -->
	<link rel="stylesheet" href="<?= site_url() ?>/wp-content/themes/hello-elementor-child/assets/custom.css"/>

	<!-- Swiper CSS/JS -->
    <link rel="stylesheet" href="<?= site_url() ?>/wp-content/themes/hello-elementor-child/assets/swiper-bundle.min.css"/>
	<?php
}


/**
  * Hook on Footer
  * @author oom_ss 
  * @version  1.0.0
 */
add_action ( 'wp_footer', 'oom_custom_footer' );
function oom_custom_footer() {
	 wp_nav_menu( [
		'theme_location' => 'oom-mobile-slide-menu',
		'menu_id' => 'oom-mobile-panel-menu',
		'container' => 'nav',
		'container_id' => 'oom-mobile-menu',
	] );
    ?>
    <script>
    /** Hide Nitropack **/    
	jQuery(document).ready(function($){
	setTimeout(function(){
		 //var tag_new = jQuery("template").eq(38).attr("id");
		 var tag_new = jQuery("template").last().attr("id");
		 jQuery("#" + tag_new).css("display", "none");
		 jQuery("#" + tag_new).next().next().css("display", "none");
	}, 100);
	});
	</script>
    <?php
}

/**
* Register Slide Menu
* @author oom_ss 
* @since  1.1.0
*/
add_action( 'init', 'oom_slide_menu_location' );
function oom_slide_menu_location() {
  register_nav_menu('oom-mobile-slide-menu',__( 'Mobile Slide Menu' ));
}


add_filter( 'auto_update_core', '__return_false' );
add_filter( 'auto_update_theme', '__return_false' );
add_filter( 'auto_update_plugin', '__return_false' );

/* track post views */

function wpb_set_post_views($postID) {
    $count_key = 'wpb_post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    
    if($count == ''){
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '1');
    } else {
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}

function wpb_get_post_views($postID){
    $count_key = 'wpb_post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count == ''){
        return "Views";
    }
    return $count . ' Views';
}

// Count views on single posts
function wpb_track_post_views ($post_id) {
    if ( !is_single() ) return;
    if ( empty ( $post_id ) ) {
        global $post;
        $post_id = $post->ID;    
    }
    wpb_set_post_views($post_id);
}
add_action( 'wp_head', 'wpb_track_post_views');

function wpb_post_views_shortcode() {
    global $post;
    return wpb_get_post_views($post->ID);
}
add_shortcode('post-views', 'wpb_post_views_shortcode');


// function oom_custom_security_headers() {
//     header("Content-Security-Policy: 
//         default-src 'self';
//         script-src 'self' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net;
//         style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com;
//         font-src 'self' https://fonts.gstatic.com;
//         img-src 'self' data:;
//         object-src 'none';
//         frame-ancestors 'self';
//     ");

//     // Prevent MIME-type sniffing
//     header("X-Content-Type-Options: nosniff");

//     // Restrict Adobe Flash and other plugins
//     header("X-Permitted-Cross-Domain-Policies: none");

//     // Control referrer information
//     header("Referrer-Policy: strict-origin-when-cross-origin");

//     // Restrict device permissions
//     header("Permissions-Policy: microphone=(), camera=()");
// }
// add_action('send_headers', 'oom_custom_security_headers');



function oom_custom_security_headers() {
    // Check if headers have already been sent (for testing environments)
    if (headers_sent()) {
        return;
    }
    
    // Content-Security-Policy
	header("Content-Security-Policy: frame-ancestors *; object-src 'none'; script-src 'self' https://maps.googleapis.com https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com https://ipapi.co https://connect.facebook.net https://snap.licdn.com https://chat-plugin.easychat.co https://cdn.trustindex.io 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; font-src 'self' data: https:;", true);

    // X-Frame-Options for legacy support
    header("X-Frame-Options: SAMEORIGIN", true);

    // Prevent MIME-type sniffing
    header("X-Content-Type-Options: nosniff", true);

    // Restrict Adobe Flash and other plugins
    header("X-Permitted-Cross-Domain-Policies: none", true);

    // Control referrer information
    header("Referrer-Policy: strict-origin-when-cross-origin", true);

	// Restrict browser tracking (disable Google FLoC)
	header("Permissions-Policy: interest-cohort=(), microphone=(), camera=()", true);
	
	// Remove X-Powered-By header
	remove_action('wp_head', 'wp_generator'); // removes WordPress version
	header_remove('X-Powered-By'); // removes PHP version
}
add_action('send_headers', 'oom_custom_security_headers');




