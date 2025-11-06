<?php
/**
 * Global Shortcode 
 *
 * All custom shortcode
 * @author      oom_ss
 * @version     1.0.0
 */
 

/**
  * Custom Display Ratings/Stars
  * @author oom_ss 
  * @version  1.0.0
  */
add_shortcode('oom_ratings', 'oom_ratings'); 
function oom_ratings($atts) {
    ob_start();
    $att = shortcode_atts( array(
		 'display' => '5',
	 ), $atts );
	
     // Validate and sanitize the stars value
     $stars = intval($att['display']);
     // Ensure stars is between 0 and 5
     $stars = max(0, min(5, $stars));
     
     echo '<div class="oom-star-rating">';
         for ($s = 1; $s <= $stars; $s++) {
             echo '<span class="fa fa-star checked"></span>';
         }
 
         for ($x = 1; $x <= 5 - $stars; $x++) {
             echo '<span class="fa fa-star"></span>';
         }
     echo '</div>';
	return ob_get_clean();
} 

 
/**
  * Hero Slider
  * @author oom_ss 
  * @version  1.0.6
  */
  add_shortcode('oom_hero_slider', 'oom_hero_slider'); 
  function oom_hero_slider($atts) {
      ob_start();
      $att = shortcode_atts( array(
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
     ), $atts );
    
    $query_args = array(
      'posts_per_page' => -1,
      'post_status' => 'publish',
      'post_type' => 'oom_hero_slider',
      'meta_key'       => 'display_order',
      'orderby'        => 'meta_value_num',
      'order'          => 'ASC',
      'tax_query' => array(
        array(
          'taxonomy' => 'oom_hero_slider_cat',
          'field'    => 'term_id',
          'terms'    => array( $att['category_id'] ),
          'operator' => 'IN'
        ),
      ),
    );
    $post_query = new WP_Query( $query_args );
    ?>
    <style>
      <?php if($att['direction'] === 'vertical') {
        echo '#oom-hero-slider' . $att['category_id'] . '{
          height: ' . $att['height'] . ';
        }';
      } ?>

      <?php if($att['arrow_position'] === 'bottom') { ?>
      #oom-hero-slider .swiper-button-next, #oom-hero-slider .swiper-button-prev {
        top: 85%;
      }
      <?php } ?>
      
      #oom-hero-slider .swiper-pagination-bullet {
        background: #<?php echo $att['pagination_color'] ?>;
      }
      #oom-hero-slider .swiper-pagination-bullet.swiper-pagination-bullet-active {
        background: #<?php echo $att['pagination_active_hover'] ?>!important;
      }
      
      #oom-hero-slider .swiper-button-prev ,
      #oom-hero-slider .swiper-button-next {
        color:  #<?php echo $att['arrow_color'] ?>!important;
      }
      
      #oom-hero-slider .swiper-button-prev i,
      #oom-hero-slider .swiper-button-next i{
        color:  #<?php echo $att['arrow_color'] ?>!important;
      }
      
      #oom-hero-slider .swiper-button-prev i {
        padding-right: 5px;
      }
      
      #oom-hero-slider .swiper-button-next i {
        padding-left: 5px;
      }
    </style>
      <!-- Swiper OOm Hero Slider -->
        <div id="oom-hero-slider" class="swiper swiper-oom-hero-slider">
          <div class="swiper-wrapper">
      <?php 
          if ( $post_query->have_posts() ) {
             while ( $post_query->have_posts() ) : $post_query->the_post();
          ?>
            
            <div class="swiper-slide oom-hero-slider">
            <?php echo \Elementor\Plugin::$instance->frontend->get_builder_content( $post_query->post->ID, true ); ?>
            </div>
        
            <?php 
          endwhile;
        }
        wp_reset_query();
          ?>
        </div>
          
          <?php if($att['has_arrow'] === 'yes') { ?>
              <!-- Arrows -->
              <div class="swiper-button-next swiper-button-next<?php echo $att['category_id'] ?>"><?php echo $att['arrow_right_txt'] ?> <i aria-hidden="true" class="<?php echo $att['arrow_right'] ?>"></i></div>
              <div class="swiper-button-prev swiper-button-prev<?php echo $att['category_id'] ?>"><i aria-hidden="true" class="<?php echo $att['arrow_left'] ?>"></i> <?php echo $att['arrow_left_txt'] ?></div>
          <?php } ?>
          
         <?php if($att['has_pagination'] === 'yes') { ?>
              <!-- Pagination -->
              <div class="swiper-pagination swiper-pagination<?php echo $att['category_id'] ?>"></div>
         <?php } ?>
        </div>
  
        <!-- Initialize Swiper -->
        <script>
          const swiperOOmHeroSlider<?php echo $att['category_id'] ?> = new Swiper(".swiper-oom-hero-slider", {
            //loop: true,
            slidesPerView: 1,
            pagination: {
              el: ".swiper-pagination.swiper-pagination<?php echo $att['category_id'] ?>",
              clickable: true,
            },
            navigation: {
              nextEl: ".swiper-button-next.swiper-button-next<?php echo $att['category_id'] ?>",
              prevEl: ".swiper-button-prev.swiper-button-prev<?php echo $att['category_id'] ?>",
            },
          });
        </script>
  
  
    <?php
    return ob_get_clean();
} 