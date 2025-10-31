<?php
/**
 * The template for displaying the header
 *
 * This is the template that displays all of the <head> section, opens the <body> tag and adds the site's header.
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$viewport_content = apply_filters( 'hello_elementor_viewport_content', 'width=device-width, initial-scale=1' );
$enable_skip_link = apply_filters( 'hello_elementor_enable_skip_link', true );
$skip_link_url = apply_filters( 'hello_elementor_skip_link_url', '#content' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="<?php echo esc_attr( $viewport_content ); ?>">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>

    <?php if (get_option('oom_gtm_code')): ?>
        <!-- Google Tag Manager -->
        <script>
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','<?php echo esc_js(get_option('oom_gtm_code')); ?>');
        </script>
        <!-- End Google Tag Manager -->
    <?php endif; ?>
</head>
<body <?php body_class(); ?>>

<?php if (get_option('oom_gtm_code')): ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr(get_option('oom_gtm_code')); ?>"
        height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->
<?php endif; ?>

<!-- Start of Omnichat code -->
  <script>var a=document.createElement('a');a.setAttribute('href','javascript:;');a.setAttribute('id','easychat-floating-button');
    var span=document.createElement('span');span.setAttribute('id', 'easychat-unread-badge');span.setAttribute('style','display: none');var d1=document.createElement('div');d1.setAttribute('id','easychat-close-btn');d1.setAttribute('class','easychat-close-btn-close');var d2=document.createElement('div');d2.setAttribute('id','easychat-chat-dialog');d2.setAttribute('class','easychat-chat-dialog-close');var ifrm=document.createElement('iframe');ifrm.setAttribute('id','easychat-chat-dialog-iframe');
    ifrm.setAttribute('src','https://client-chat.easychat.co/?appkey=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ0ZWFtTmFtZSI6ImRkMjI4MjY0LWM2OWUtNDM5Ni05Y2VlLWVkZTMwYjU5OGQ1ZCJ9.fv2IutUFKEL-OBtJIr6Vx3eRpztj4sgE1aS3J7V32VM&lang=en');
    ifrm.style.width='100%';ifrm.style.height='100%';ifrm.style.frameborder='0';ifrm.style.scrolling='on';d2.appendChild(ifrm);
    if(!document.getElementById("easychat-floating-button")){
      document.body.appendChild(a);document.body.appendChild(span);document.body.appendChild(d1);document.body.appendChild(d2);
    }

    var scriptURL = 'https://chat-plugin.easychat.co/easychat.js';
    if(!document.getElementById("omnichat-plugin")) {
      var scriptTag = document.createElement('script');
      scriptTag.src = scriptURL;
      scriptTag.id = 'omnichat-plugin';
      document.body.appendChild(scriptTag);
    }
  </script>
<!-- End of Omnichat code -->

<?php wp_body_open(); ?>

<?php if ( $enable_skip_link ) { ?>
<a class="skip-link screen-reader-text" href="<?php echo esc_url( $skip_link_url ); ?>"><?php echo esc_html__( 'Skip to content', 'hello-elementor' ); ?></a>
<?php } ?>

<?php
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) {
	if ( hello_elementor_display_header_footer() ) {
		if ( did_action( 'elementor/loaded' ) && hello_header_footer_experiment_active() ) {
			get_template_part( 'template-parts/dynamic-header' );
		} else {
			get_template_part( 'template-parts/header' );
		}
	}
}
