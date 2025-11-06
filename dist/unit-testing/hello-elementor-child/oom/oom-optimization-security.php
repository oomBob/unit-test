<?php
/**
 * Page Optimization and Security
 *
 * This functions will modify remove unnecessary assets on frontend to optimize the page performance and change default error login message and block wp enum
 * @author      oom_ss
 * @version     1.0.0
 */


/**
 * Page Optimization 
*/

//Google Page speed performance
//Eliminate render-blocking resources
// Don't load Gutenberg-related stylesheets.
add_action( 'wp_enqueue_scripts', 'remove_block_css', 100 );
function remove_block_css() {
    wp_dequeue_style( 'wp-block-library' ); // Wordpress core
    wp_dequeue_style( 'wp-block-library-theme' ); // Wordpress core

    if( is_front_page() ) {
		wp_dequeue_style( 'woocommerce-layout' ); 
		wp_dequeue_style( 'woocommerce-general' ); 
		wp_dequeue_style( 'woocommerce-smallscreen' ); 
		wp_dequeue_style( 'swiper' ); 
		wp_dequeue_style( 'mycred-front' ); 
		wp_dequeue_style( 'mycred-social-share-icons' ); 
		wp_dequeue_style( 'mycred-social-share-style' ); 
		wp_dequeue_style( 'mycred-notifications' ); 
		wp_dequeue_script( 'mycred-notifications-js-extra' ); 
		wp_dequeue_script( 'mycred-video-points' );
	}
}

//Disable Emoji
add_action('init', 'disable_emojis');

function disable_emojis() {
     remove_action('wp_head', 'print_emoji_detection_script', 7);
     remove_action('admin_print_scripts', 'print_emoji_detection_script');
     remove_action('wp_print_styles', 'print_emoji_styles');
     remove_action('admin_print_styles', 'print_emoji_styles');  
     remove_filter('the_content_feed', 'wp_staticize_emoji');
     remove_filter('comment_text_rss', 'wp_staticize_emoji');    
     remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
     add_filter('tiny_mce_plugins', 'disable_emojis_tinymce');
     add_filter('wp_resource_hints', 'disable_emojis_dns_prefetch', 10, 2);
     add_filter('emoji_svg_url', '__return_false');
}
function disable_emojis_tinymce($plugins) {
     if(is_array($plugins)) {
         return array_diff($plugins, array('wpemoji'));
     } else {
         return array();
     }
}
function disable_emojis_dns_prefetch( $urls, $relation_type ) {
     if('dns-prefetch' == $relation_type) {
         $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2.2.1/svg/');
         $urls = array_diff($urls, array($emoji_svg_url));
     }
     return $urls;
}

//Remove Manifest, RSD and Shortlinks
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action ('template_redirect', 'wp_shortlink_header', 11, 0);
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action ('template_redirect', 'wp_shortlink_header', 11, 0);

//Disable Pingbacks
add_action('pre_ping', 'disable_self_pingbacks');
function disable_self_pingbacks(&$links) {
     $home = get_option('home');
     foreach($links as $l => $link) {
         if(strpos($link, $home) === 0) {
             unset($links[$l]);
         }
     }
 }

 // Changed default hint message 
// Redirect to Thank you message if incorrect username or email in lost password page
// define the login_errors callback
add_filter( 'login_errors', 'filter_login_errors' );
function filter_login_errors(){
  $error_message ='Invalid Username or Password!';
  return $error_message;
}

// Block WP enum scans 
if (!is_admin()) {
	// default URL format
    if (isset($_SERVER['QUERY_STRING'])) {
        if (preg_match('/author=([0-9]*)/i', $_SERVER['QUERY_STRING'])) die();
        add_filter('redirect_canonical', 'shapeSpace_check_enum', 10, 2);
    }
}

function shapeSpace_check_enum($redirect, $request) {
    // permalink URL format
    if (preg_match('/\?author=([0-9]*)(\/*)/i', $request)) die();
    else return $redirect;
}

/*
 * Disable /users rest routes
 */
add_filter('rest_endpoints', function( $endpoints ) {
    if ( isset( $endpoints['/wp/v2/users'] ) ) {
        unset( $endpoints['/wp/v2/users'] );
    }
    if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
        unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
    }
    return $endpoints;
});


/*
 * Disable Plugin and Themes for other admin
 */
add_action( 'admin_init', 'deny_theme_editor_and_plugins_access' );
function deny_theme_editor_and_plugins_access() {
    global $current_user;
    $current_user = wp_get_current_user();
    $user_email = $current_user->user_email;
    $current_user_id = get_current_user_id();
    $current_page = basename( $_SERVER['PHP_SELF'] );
    if ( is_user_logged_in() && current_user_can( 'activate_plugins' ) && $current_user_id != 1 && ( $current_page == 'theme-editor.php' || $current_page == 'plugin-editor.php') ) {
      wp_die( 'Access denied.' );
    }
    if ( $user_email != 'project@oom.com.sg' ) { 
		remove_submenu_page( 'plugins.php', 'plugin-editor.php' );
		remove_submenu_page( 'themes.php', 'theme-editor.php' );
        remove_submenu_page( 'themes.php', 'theme_options' );
    }
}
  