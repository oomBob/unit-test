<?php
/**
 * Your Custom Shortcode 
 *
 * All custom shortcode
 * @author      oom_bb
 * @version     1.0.0
 */
 
add_shortcode('current_year', 'current_year_shortcode');
function current_year_shortcode() {
    ob_start();
    $year = date('Y');
    return $year;
    ob_get_clean();
}

// Ensure this only runs in WordPress context
if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('oom_rental_price_box', function() {
    if (!is_singular('rental')) return '';

    $post_id = get_the_ID();
    $rental_price = get_post_meta($post_id, 'rental_price', true);
    $rental_sale_price = get_post_meta($post_id, 'rental_sale_price', true);

    // Format numbers, drop trailing .00
    $format_price = function($price) {
        if (!is_numeric($price)) return $price;
        return (intval($price) == floatval($price)) 
            ? number_format($price, 0) 
            : number_format($price, 2);
    };

    $rental_price_display = $format_price($rental_price);
    $rental_sale_price_display = $format_price($rental_sale_price);

    if (!$rental_price) return '';

    ob_start();
    ?>
    <div class="oom-custom-pricing-box">
        <?php if (!$rental_sale_price): ?>
            <span class="highlight">$<?= esc_html($rental_price_display); ?></span>
        <?php else: ?>
            <span class="strike">$<?= esc_html($rental_price_display); ?></span>
            <span class="highlight">$<?= esc_html($rental_sale_price_display); ?></span>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
});

add_shortcode('rental_vehicle_type_swiper_pagi', function () {
    if (!is_singular('rental')) return '';

    $post_id = get_the_ID();

    ob_start();
    ?>
    <div class="swiper oomModelSwiper oomModelSwiperPagi" data-rental-id="<?php echo esc_attr($post_id); ?>">
        <div class="swiper-wrapper">
            <div class="swiper-slide">Loading models...</div>
        </div>
        <div class="swiper-oom-control">
            <div class="oom-swiper-button-prev"><</div>
            <div class="swiper-pagination"></div>
            <div class="oom-swiper-button-next">></div>
        </div>
    </div>
    <?php
    return ob_get_clean();
});



add_shortcode('rental_vehicle_type_swiper', function() {
    if (!is_singular('rental')) return '';

    $post_id = get_the_ID();

    ob_start();
    ?>
    <div class="swiper oomModelSwiper" data-rental-id="<?php echo esc_attr($post_id); ?>">
        <div class="swiper-wrapper">
            <div class="swiper-slide">Loading models...</div>
        </div>
        <div class="swiper-oom-control">
            <div class="oom-swiper-button-prev"><</div>
            <div class="swiper-pagination"></div>
            <div class="oom-swiper-button-next">></div>
        </div>
    </div>
    <?php
    return ob_get_clean();
});

add_action('wp_footer', function() {
    ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.oomModelSwiper').forEach(function(container) {
            const rentalId = container.getAttribute('data-rental-id');
            const swiperWrapper = container.querySelector('.swiper-wrapper');

            fetch('<?php echo esc_url(site_url('/wp-json/oom/v1/related-vehicles/')); ?>' + rentalId)
                .then(res => res.json())
                .then(data => {
                    swiperWrapper.innerHTML = '';
                    data.forEach(item => {
                        swiperWrapper.innerHTML += `
                            <div class="swiper-slide">
                                <img src="${item.img}" alt="${item.title}" loading="lazy" decoding="async">
                                <h3>${item.title}</h3>
                            </div>
                        `;
                    });

                    const isPagi = container.classList.contains('oomModelSwiperPagi');
                    const hasMultipleItems = data.length > 1;
                    let paginationEl = container.querySelector(".swiper-pagination");

                    // Create pagination element if it doesn't exist
                    if (!paginationEl) {
                        paginationEl = document.createElement('div');
                        paginationEl.className = 'swiper-pagination';
                        container.appendChild(paginationEl);
                    }

                    new Swiper(container, {
                        pagination: {
                            el: paginationEl,
                            type: isPagi ? "bullets" : "custom",
                            clickable: true,
							renderCustom: function (swiper, current, total) {
								return 'Available or similar models';
                            }
                        },
                        navigation: {
                            nextEl: container.querySelector(".oom-swiper-button-next"),
                            prevEl: container.querySelector(".oom-swiper-button-prev"),
                        },
                        loop: hasMultipleItems, // Only loop if there are multiple items
                        slidesPerView: 1,
                    });

                    // Ensure text shows up after Swiper initialization
                    setTimeout(() => {
                        if (paginationEl) {
                            paginationEl.innerHTML = 'Available or similar models';
                            // Override Swiper's pagination-lock for single items
                            if (!hasMultipleItems) {
                                paginationEl.classList.remove('swiper-pagination-lock');
                                paginationEl.style.display = 'block';
                            }
                        }
                    }, 100);
				
				
                })
                .catch(() => {
                    swiperWrapper.innerHTML = '<div class="swiper-slide">No related vehicles found.</div>';
                });
        });
    });
    </script>
    <?php
});



add_action('rest_api_init', function() {
    register_rest_route('oom/v1', '/related-vehicles/(?P<id>\d+)', [
        'methods' => 'GET',
        'callback' => function($data) {
            $post_id = intval($data['id']);
            $related_vehicle_types = [];

            // JetEngine Relations API endpoint
            $api_url = get_site_url() . '/wp-json/jet-rel/7/';
            $headers = [
                'User-Agent' => 'WordPress/' . get_bloginfo('version'),
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode('hello:Hello@oom!!123')
            ];

            $response = wp_remote_get($api_url, [
                'headers' => $headers,
                'timeout' => 30
            ]);

            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $relations_data = json_decode(wp_remote_retrieve_body($response), true);

                if (isset($relations_data[$post_id])) {
                    foreach ($relations_data[$post_id] as $relation) {
                        $child_id = intval($relation['child_object_id']);
                        $related_vehicle_types[] = [
                            'title' => get_the_title($child_id),
                            'img' => get_the_post_thumbnail_url($child_id, 'medium')
                        ];
                    }
                }
            }

            // If no relations found, fallback
            if (empty($related_vehicle_types)) {
                $vehicle_posts = get_posts([
                    'post_type' => 'vehicle-type',
                    'numberposts' => -1,
                    'post_status' => 'publish'
                ]);
                foreach ($vehicle_posts as $post) {
                    $related_vehicle_types[] = [
                        'title' => get_the_title($post->ID),
                        'img' => get_the_post_thumbnail_url($post->ID, 'medium')
                    ];
                }
            }

            return rest_ensure_response($related_vehicle_types);
        },
        'permission_callback' => '__return_true'
    ]);
});

// Ensure this only runs in WordPress context
if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('rental_vehicle_type_swiper_pagi_backup', function () {
    if (!is_singular('rental')) return '';

    $post_id = get_the_ID();
    $related_vehicle_types = [];

    // Fetch related posts from JetEngine Relations API
    $api_url = get_site_url() . '/wp-json/jet-rel/7/';
    $alternative_urls = [
        get_site_url() . '/wp-json/jet-rel/',
        get_site_url() . '/wp-json/jet-rel/7',
        get_site_url() . '/wp-json/jet-rel/relations/7/'
    ];

    $api_success = false;

    $headers = [
        'User-Agent' => 'WordPress/' . get_bloginfo('version'),
        'Accept' => 'application/json',
        'Authorization' => 'Basic ' . base64_encode('hello:Hello@oom!!123'),
    ];

    $response = wp_remote_get($api_url, [
        'headers' => $headers,
        'timeout' => 30
    ]);

    if (is_wp_error($response)) {
        foreach ($alternative_urls as $alt_url) {
            $response = wp_remote_get($alt_url, [
                'headers' => $headers,
                'timeout' => 30
            ]);

            if (!is_wp_error($response)) {
                if (wp_remote_retrieve_response_code($response) === 200) {
                    $api_url = $alt_url;
                    $api_success = true;
                    break;
                }
            }
        }
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code === 200) {
            $api_success = true;
            $response_body = wp_remote_retrieve_body($response);
            $relations_data = json_decode($response_body, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($relations_data[$post_id])) {
                $related_vehicle_types = array_map(function ($relation) {
                    return $relation['child_object_id'];
                }, $relations_data[$post_id]);
            }
        } elseif ($response_code === 401) {
            $response = wp_remote_get($api_url, ['timeout' => 30]);
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $api_success = true;
                $response_body = wp_remote_retrieve_body($response);
                $relations_data = json_decode($response_body, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($relations_data[$post_id])) {
                    $related_vehicle_types = array_map(function ($relation) {
                        return $relation['child_object_id'];
                    }, $relations_data[$post_id]);
                }
            }
        }
    }

    if (!$api_success) {
        error_log('All API attempts failed, using fallback');
    }

    if (empty($related_vehicle_types)) {
        $vehicle_posts = get_posts([
            'post_type' => 'vehicle-type',
            'numberposts' => -1,
            'post_status' => 'publish'
        ]);
        $related_vehicle_types = array_map(fn($post) => $post->ID, $vehicle_posts);
        error_log('Using fallback: found ' . count($related_vehicle_types) . ' vehicle-type posts');
    }

    if (empty($related_vehicle_types)) return '<p>No related vehicles.</p>';

    ob_start();
    ?>
    <div class="swiper oomModelSwiper">
        <div class="swiper-wrapper">
            <?php foreach ($related_vehicle_types as $vehicle_id): ?>
                <?php
                    $img = get_the_post_thumbnail_url($vehicle_id, 'medium');
                    $title = get_the_title($vehicle_id);
                ?>
                <div class="swiper-slide">
                    <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($title); ?>">
                </div>
            <?php endforeach; ?>
        </div>

        <div class="swiper-oom-control">
            <div class="oom-swiper-button-prev"><</div>
            <div class="swiper-pagination"></div>
            <div class="oom-swiper-button-next">></div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new Swiper(".oomModelSwiper", {
                pagination: {
                    el: ".swiper-pagination",
                    type: "bullets",
                    clickable: true,
                },
                navigation: {
                    nextEl: ".oom-swiper-button-next",
                    prevEl: ".oom-swiper-button-prev",
                },
                loop: false,
                slidesPerView: 1,
            });
        });
    </script>
    <?php
    return ob_get_clean();
});



function oom_custom_menu_shortcode($atts) {
    $atts = shortcode_atts([
        'id' => ''
    ], $atts, 'oom-custom-menu');

    if (empty($atts['id'])) return '';

    $menu = wp_nav_menu([
        'menu'            => intval($atts['id']),
        'container'       => false,
        'menu_class'      => 'oom-shortcode-menu',
        'echo'            => false,
        'fallback_cb'     => false
    ]);

    return $menu ? $menu : '';
}
add_shortcode('oom-custom-menu', 'oom_custom_menu_shortcode');


// Daily Rental Tweaks
add_shortcode('oom_daily_rental_form', function () {
    ob_start();
    ?>
    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    
    <style>
    /* Google Places Autocomplete Styling */
    .pac-container {
        z-index: 9999 !important;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border: 1px solid #e0e0e0;
        font-family: inherit;
    }
    
    .pac-item {
        padding: 8px 12px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    
    .pac-item:hover {
        background-color: #f8f9fa;
    }
    
    .pac-item:last-child {
        border-bottom: none;
    }
    
    .pac-item-query {
        font-weight: 500;
        color: #333;
    }
    
    .pac-matched {
        font-weight: bold;
        color: #007bff;
    }
    
    .pac-secondary-text {
        color: #666;
        font-size: 0.9em;
    }
    
    /* Location input styling */
    input[id*="location-other"] {
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%23666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>');
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 16px;
        padding-right: 40px;
    }
    
    input[id*="location-other"]:focus {
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%23007bff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>');
    }
    
    /* User-friendly notification styling */
    .oom-notification {
        position: fixed;
        top: 50%;
        left: 50%;
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        padding: 20px;
        max-width: 400px;
        z-index: 10000;
        font-family: inherit;
        transform: translate(-50%, -50%) scale(0.8);
        transition: transform 0.3s ease;
        opacity: 0;
    }
    
    .oom-notification.show {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
    }
    
    .oom-notification-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .oom-notification-icon {
        width: 24px;
        height: 24px;
        margin-right: 12px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: bold;
    }
    
    .oom-notification-icon.info {
        background: #e3f2fd;
        color: #1976d2;
    }
    
    .oom-notification-icon.warning {
        background: #fff3e0;
        color: #f57c00;
    }
    
    .oom-notification-title {
        font-weight: 600;
        font-size: 16px;
        color: #333;
        margin: 0;
    }
    
    .oom-notification-message {
        color: #666;
        line-height: 1.5;
        margin: 0 0 15px 0;
		font-size: 14px;
    }
    
    .oom-notification-fields {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 15px;
    }
    
    .oom-notification-fields-title {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .oom-notification-fields-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .oom-notification-fields-list li {
        color: #666;
        padding: 4px 0;
        font-size: 14px;
        position: relative;
        padding-left: 20px;
    }
    
    .oom-notification-fields-list li:before {
        content: "•";
        color: #007bff;
        font-weight: bold;
        position: absolute;
        left: 0;
    }
    
    .oom-notification-close {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff!important;
		border-color: transparent!important;
    }
    
    .oom-notification-close:hover {
        background-color: #4caf50;
        color: #fff;
        box-shadow: 0 2px 8px rgba(97, 206, 112, 0.3);
		border-color: transparent!important;
    }
    
    /* Info icon styling */
    .oom-info-icon {
        cursor: pointer;
        transition: transform 0.2s ease, opacity 0.2s ease;
    }
    
    .oom-info-icon:hover {
        transform: scale(1.1);
        opacity: 0.8;
    }
    
    .oom-info-icon:active {
        transform: scale(0.95);
    }
    
    /* Disabled time select styling */
    select[id*="return-time"].disabled,
    select[id*="return-time"]:disabled {
        background-color: #f5f5f5 ;
        color: #2e2e2e !important;
        cursor: not-allowed !important;
        opacity:1;
        border-color: #ddd !important;
    }
    
    select[id*="return-time"].disabled option,
    select[id*="return-time"]:disabled option {
        color: #999 !important;
    }
    
    /* Visual feedback for disabled state */
    select[id*="return-time"].disabled:hover,
    select[id*="return-time"]:disabled:hover {
        background-color: #f5f5f5 !important;
        border-color: #ddd !important;
    }
    
    /* Styling for disabled options in same-day rental */
    select option.disabled-option {
        color: #999 !important;
        background-color: #f5f5f5 !important;
        font-style: italic;
    }
    
    select option.disabled-option:hover {
        background-color: #f5f5f5 !important;
    }
    
    /* Disabled time select styling for single rental form */
    select[id*="to-time"].disabled,
    select[id*="to-time"]:disabled {
        background-color: #f5f5f5 !important;
        color: #999 !important;
        cursor: not-allowed !important;
        opacity: 0.6;
        border-color: #ddd !important;
    }
    
    select[id*="to-time"].disabled option,
    select[id*="to-time"]:disabled option {
        color: #999 !important;
    }
    
    /* Visual feedback for disabled state */
    select[id*="to-time"].disabled:hover,
    select[id*="to-time"]:disabled:hover {
        background-color: #f5f5f5 !important;
        border-color: #ddd !important;
    }
    </style>
    
    <form class="oom-daily-rental-form">
        <div class="oom-daily-rental-container">
            <div class="oom-daily-rental-pickup-box">
                <div class="oom-daily-rental-pickup-box-top">
                    <label for="oom-daily-rental-pickup-date">Pick-Up Date</label>
                    <div class="oom-daily-rental-flex">
                        <input type="text" id="oom-daily-rental-pickup-date" name="oom-daily-rental-pickup-date" placeholder="Select Date" autocomplete="off">
                        <select name="oom-daily-rental-pickup-time" id="oom-daily-rental-pickup-time">
                            <option value="">Select Time</option>
                            <?php
                            $times = ['08:30', '09:30', '10:30', '11:30', '12:30', '13:30', '14:30', '15:30', '16:30', '17:30'];
							$readable_times = ['08:30am', '09:30am', '10:30am', '11:30am', '12:30pm', '01:30pm', '02:30pm', '03:30pm', '04:30pm', '05:30pm'];
                            foreach ($times as $i => $time) {
								$label = $readable_times[$i] ?? $time;
								echo "<option value=\"$time\">$label</option>";
							}
                            ?>
                        </select>
                    </div>
                    <p class="oom-daily-rental-info-text">*Bookings must be made at least <?php echo esc_html(get_option('oom_advanced_booking_days', '2')); ?> day<?php echo get_option('oom_advanced_booking_days', '2') == '1' ? '' : 's'; ?> in advance.</p>
                </div>
                <div class="oom-daily-rental-pickup-box-bottom">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/icon-info.png" alt="Info" class="oom-info-icon">
                    <p>Duration: <strong id="oom-daily-duration"> - </strong></p>
                </div>
            </div>

            <div class="oom-daily-rental-return-box">
                <label for="oom-daily-rental-return-date">Return Date</label>
                <div class="oom-daily-rental-flex">
                    <input type="text" id="oom-daily-rental-return-date" name="oom-daily-rental-return-date" placeholder="Select Date" autocomplete="off" disabled>
                    <select name="oom-daily-rental-return-time" id="oom-daily-rental-return-time">
                        <option value="">Select Time</option>
                        <?php
                        foreach ($times as $i => $time) {
							$label = $readable_times[$i] ?? $time;
							echo "<option value=\"$time\">$label</option>";
						}
                        ?>
                    </select>
                </div>
                <p class="oom-daily-rental-info-text" id="return-time-info" style="display: none; color: #666; font-style: italic;">*Return time will be enabled after selecting pickup time</p>
            </div>

            <div class="oom-daily-rental-location-box">
                <div class="oom-daily-rental-location-box-top">
                    <label for="oom-daily-rental-pickup-location">Pick-Up Location</label>
                    <select name="oom-daily-rental-pickup-location" id="oom-daily-rental-pickup-location">
                        <option value="">Select Location</option>
                        <option value="1">ComfortDelGro Rent-A-Car</option>
                        <option value="2">Other Location</option>
                    </select>
                    <div class="oom-daily-rental-pickup-other-location" style="display: none;">
                        <input type="text" id="oom-daily-rental-pickup-location-other" name="oom-daily-rental-pickup-location-other" placeholder="Address">
                    </div>
                    <p class="oom-daily-rental-pickup-location-text"></p>

                    <label for="oom-daily-rental-drop-off-location">Drop-Off Location</label>
                    <select name="oom-daily-rental-drop-off-location" id="oom-daily-rental-drop-off-location">
                        <option value="">Select Location</option>
                        <option value="1">ComfortDelGro Rent-A-Car</option>
                        <option value="2">Other Location</option>
                    </select>
                    <div class="oom-daily-rental-drop-off-other-location" style="display: none;">
                        <input type="text" id="oom-daily-rental-drop-off-location-other" name="oom-daily-rental-drop-off-location-other" placeholder="Address">
                    </div>
                    <p class="oom-daily-rental-drop-off-location-text"></p>
                </div>
                <p class="oom-daily-rental-info-text">Pick up and drop off services are available at $<?php echo esc_html(get_option('oom_pickup_dropoff_charge', '30.00')); ?> each. </p>
            </div>
        </div>
    </form>

    <!-- jQuery and jQuery UI -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    
    <!-- Google Places API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr(get_option('oom_google_place_api', 'AIzaSyAK5x3pdAgDPv-QsfX4SXHtmULjYBW0NIE')); ?>&libraries=places&loading=async"></script>
    
    <script>
    // Global function to update location text when Google Autocomplete fills a field
    window.updateOomLocationText = function(fieldId) {
        if (fieldId === 'oom-daily-rental-pickup-location-other' || fieldId === 'oom-daily-rental-drop-off-location-other') {
            // For daily rental form
            if (typeof toggleLocFields === 'function') {
                toggleLocFields();
            }
        } else if (fieldId === 'oom-single-rental-pickup-location-other' || fieldId === 'oom-single-rental-drop-off-location-other') {
            // For single rental form
            if (typeof toggleLocFields === 'function') {
                toggleLocFields();
            }
            if (typeof updatePricing === 'function') {
                updatePricing();
            }
            if (typeof updateLocationLabels === 'function') {
                updateLocationLabels();
            }
        }
    };
    
    // User-friendly notification function
    function showUserFriendlyNotification(emptyFields) {
        // Remove any existing notifications
        const existingNotification = document.querySelector('.oom-notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'oom-notification';
        
        // Create field list HTML
        const fieldsList = emptyFields.map(field => `<li>${field}</li>`).join('');
        
        // Set notification content
        notification.innerHTML = `
            <div class="oom-notification-header">
                <div class="oom-notification-icon warning">!</div>
                <h3 class="oom-notification-title">Almost there!</h3>
            </div>
            <p class="oom-notification-message">Please complete the following information to continue with your booking:</p>
            <div class="oom-notification-fields">
                <div class="oom-notification-fields-title">Required Information:</div>
                <ul class="oom-notification-fields-list">
                    ${fieldsList}
                </ul>
            </div>
            <button class="oom-notification-close oom-custom-btn oom-nav-btn oom-rfq-btn" onclick="this.parentElement.remove()"> <span style="color: white!important;font-size: clamp(0.875rem, 0.375rem + 0.625vw, 1.125rem) !important;">Got it, I'll complete these</span></button>
        `;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Show with animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        // Auto-hide after 8 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.classList.remove('show');
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 8000);
    }
    
    // Rental rate information notification function
    function showRentalRateInfo() {
        // Remove any existing notifications
        const existingNotification = document.querySelector('.oom-notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'oom-notification';
        
        // Set notification content
        notification.innerHTML = `
            <div class="oom-notification-header">
                <div class="oom-notification-icon info">i</div>
                <h3 class="oom-notification-title">Rental Rate Information</h3>
            </div>
            <p class="oom-notification-message">Rental rates are calculated based on a 24-hour window, with each full 24-hour period considered as 1 day of rental. Any additional hours beyond the 24-hour blocks will be charged as follows:</p>
            <div class="oom-notification-fields">
                <ul class="oom-notification-fields-list">
                    <li><strong>Additional 1 to 4 hours:</strong> Charges will be prorated based on the formula (base rate ÷ 5) × number of additional hours.</li>
                    <li><strong>Additional of 5 hours or more:</strong> A full day's rental charge will apply.</li>
                </ul>
            </div>
            <button class="oom-notification-close oom-custom-btn oom-nav-btn oom-rfq-btn" onclick="this.parentElement.remove()"> <span style="color: white!important;font-size: clamp(0.875rem, 0.375rem + 0.625vw, 1.125rem) !important;">Got it, I understand</span></button>
        `;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Show with animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        // Auto-hide after 12 seconds (longer for info)
        setTimeout(() => {
            if (notification.parentElement) {
                notification.classList.remove('show');
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 12000);
    }
    
    $(document).ready(function () {
        const pickupDate = $('#oom-daily-rental-pickup-date');
        const pickupTime = $('#oom-daily-rental-pickup-time');
        const returnDate = $('#oom-daily-rental-return-date');
        const returnTime = $('#oom-daily-rental-return-time');
        const pickupLoc = $('#oom-daily-rental-pickup-location');
        const dropoffLoc = $('#oom-daily-rental-drop-off-location');
        const pickupOther = $('#oom-daily-rental-pickup-location-other');
        const dropoffOther = $('#oom-daily-rental-drop-off-location-other');
        const pickupText = $('.oom-daily-rental-pickup-location-text');
        const dropoffText = $('.oom-daily-rental-drop-off-location-text');
        const durationDisplay = $('#oom-daily-duration');

        // Calculate minimum date (dynamic days ahead)
        const today = new Date();
        const advancedDays = <?php echo intval(get_option('oom_advanced_booking_days', '2')); ?>;
        today.setDate(today.getDate() + advancedDays);
        const minDate = today;

        // Get blockout dates from PHP
        const blockoutDates = <?php echo json_encode(oom_get_blockout_dates_for_datepicker()); ?>;
        
        // Initialize jQuery datepicker for pickup date
        pickupDate.datepicker({
            dateFormat: 'dd-mm-yy',
            minDate: minDate,
            changeMonth: true,
            changeYear: true,
            beforeShowDay: function(date) {
                // Disable weekends (0 = Sunday, 6 = Saturday)
                const day = date.getDay();
                const isWeekend = (day === 0 || day === 6);
                
                // Check if date is in blockout dates
                const dateString = $.datepicker.formatDate('dd-mm-yy', date);
                const isBlockedOut = blockoutDates.includes(dateString);
                
                // Return [isSelectable, cssClass]
                return [!isWeekend && !isBlockedOut, isBlockedOut ? 'blockout-date' : ''];
            },
            onSelect: function(dateText) {
                console.log('Pick-Up date selected:', dateText);
                // Enable return date picker and set its minimum date to the day after pickup
                returnDate.prop('disabled', false);
                
                // Set minimum date to the day after pickup date (minimum 1 day rental)
                const pickupDate = $.datepicker.parseDate('dd-mm-yy', dateText);
                const nextDay = new Date(pickupDate);
                nextDay.setDate(nextDay.getDate() + 1);
                const nextDayString = $.datepicker.formatDate('dd-mm-yy', nextDay);
                
                returnDate.datepicker('option', 'minDate', nextDayString);
                updateDuration();
                validateTimeSelection();
            }
        }).on('keydown', function(e) {
            // Prevent manual typing
            e.preventDefault();
            return false;
        });

        // Initialize jQuery datepicker for return date
        returnDate.datepicker({
            dateFormat: 'dd-mm-yy',
            changeMonth: true,
            changeYear: true,
            beforeShowDay: function(date) {
                // Disable weekends (0 = Sunday, 6 = Saturday)
                const day = date.getDay();
                const isWeekend = (day === 0 || day === 6);
                
                // Check if date is in blockout dates
                const dateString = $.datepicker.formatDate('dd-mm-yy', date);
                const isBlockedOut = blockoutDates.includes(dateString);
                
                // Return [isSelectable, cssClass]
                return [!isWeekend && !isBlockedOut, isBlockedOut ? 'blockout-date' : ''];
            },
            onSelect: function(dateText) {
                console.log('Return date selected:', dateText);
                updateDuration();
                validateTimeSelection();
            }
        }).on('keydown', function(e) {
            // Prevent manual typing
            e.preventDefault();
            return false;
        });

        function getLocText(selectEl, inputEl) {
            return selectEl.val() === "1" ? "<?php echo esc_js(get_option('oom_location', '205 Braddell Road Blk H Singapore 479401')); ?>" :
                   selectEl.val() === "2" ? inputEl.val().trim() : "";
        }

        function toggleLocFields() {
            $('.oom-daily-rental-pickup-other-location').toggle(pickupLoc.val() === "2");
            $('.oom-daily-rental-drop-off-other-location').toggle(dropoffLoc.val() === "2");
            pickupText.text(getLocText(pickupLoc, pickupOther));
            dropoffText.text(getLocText(dropoffLoc, dropoffOther));
        }

        function updateDuration() {
            const pickupDateStr = pickupDate.val();
            const pickupTimeStr = pickupTime.val();
            const returnDateStr = returnDate.val();
            const returnTimeStr = returnTime.val();
            
            if (pickupDateStr && pickupTimeStr && returnDateStr && returnTimeStr) {
                // Convert dd-mm-yyyy to yyyy-mm-dd for Date constructor
                const pickupParts = pickupDateStr.split('-');
                const returnParts = returnDateStr.split('-');
                
                if (pickupParts.length === 3 && returnParts.length === 3) {
                    const pickupFormatted = pickupParts[2] + '-' + pickupParts[1] + '-' + pickupParts[0];
                    const returnFormatted = returnParts[2] + '-' + returnParts[1] + '-' + returnParts[0];
                    
                    // Create Date objects with time
                    const pickupDateTime = new Date(pickupFormatted + 'T' + pickupTimeStr);
                    const returnDateTime = new Date(returnFormatted + 'T' + returnTimeStr);
                    
                    if (!isNaN(pickupDateTime) && !isNaN(returnDateTime) && returnDateTime >= pickupDateTime) {
                        // Calculate total duration in milliseconds
                        const durationMs = returnDateTime - pickupDateTime;
                        
                        // Convert to hours
                        const totalHours = Math.ceil(durationMs / (1000 * 60 * 60));
                        
                        // Calculate full days (24-hour periods)
                        const totalDays = Math.floor(totalHours / 24);
                        
                        // Calculate additional hours beyond full days
                        const additionalHours = totalHours % 24;
                        
                        // Build duration string
                        let durationString = '';
                        if (totalDays > 0) {
                            durationString += `${totalDays} day${totalDays > 1 ? 's' : ''}`;
                        }
                        if (additionalHours > 0) {
                            if (durationString) durationString += ' + ';
                            durationString += `${additionalHours} hour${additionalHours > 1 ? 's' : ''}`;
                        }
                        
                        durationDisplay.text(durationString || '0 days');
                    }
                }
            } else {
                durationDisplay.text('-');
            }
        }

        // Event listeners
        pickupLoc.on("change", toggleLocFields);
        dropoffLoc.on("change", toggleLocFields);
        pickupOther.on("input", toggleLocFields);
        dropoffOther.on("input", toggleLocFields);
        
        // Additional event listeners for Google Autocomplete compatibility
        pickupOther.on("blur focus change", toggleLocFields);
        dropoffOther.on("blur focus change", toggleLocFields);
        
        // Listen for Google Places Autocomplete place_changed event
        if (typeof google !== 'undefined' && google.maps && google.maps.places) {
            // Function to handle place selection
            function handlePlaceSelection(inputElement) {
                // Use setTimeout to ensure the input value is updated
                setTimeout(() => {
                    toggleLocFields();
                }, 100);
            }
            
            // Add place_changed listeners if autocomplete is initialized
            document.addEventListener('DOMContentLoaded', function() {
                // Check if autocomplete is already initialized on our fields
                if (pickupOther.length && pickupOther[0].autocomplete) {
                    pickupOther[0].autocomplete.addListener('place_changed', function() {
                        handlePlaceSelection(pickupOther[0]);
                    });
                }
                if (dropoffOther.length && dropoffOther[0].autocomplete) {
                    dropoffOther[0].autocomplete.addListener('place_changed', function() {
                        handlePlaceSelection(dropoffOther[0]);
                    });
                }
            });
        }
        
        // Add time change listeners for duration calculation and validation
        pickupTime.on("change", function() {
            updateDuration();
            validateTimeSelection();
            
            // If it's same day and return time is now invalid, reset it
            const pickupDateValue = pickupDate.val();
            const returnDateValue = returnDate.val();
            const pickupTimeValue = pickupTime.val();
            const returnTimeValue = returnTime.val();
            
            if (pickupDateValue && returnDateValue && pickupDateValue === returnDateValue && 
                pickupTimeValue && returnTimeValue) {
                const pickupTimeIndex = getTimeIndex(pickupTimeValue);
                const returnTimeIndex = getTimeIndex(returnTimeValue);
                
                if (pickupTimeIndex >= 0 && returnTimeIndex >= 0 && returnTimeIndex <= pickupTimeIndex) {
                    returnTime.val('');
                    $('#return-time-info').text('*Return time must be after pickup time for same-day rentals');
                    $('#return-time-info').show();
                }
            }
        });
        returnTime.on("change", function() {
            updateDuration();
            validateTimeSelection();
        });
        
        // Add return date change listener for same-day validation
        returnDate.on("change", function() {
            updateDuration();
            validateTimeSelection();
        });
        
        // Function to validate time selection
        function validateTimeSelection() {
            const pickupDateValue = pickupDate.val();
            const pickupTimeValue = pickupTime.val();
            const returnDateValue = returnDate.val();
            const returnTimeValue = returnTime.val();
            const returnTimeInfo = $('#return-time-info');
            
            // Return time should be disabled until both pickup date and pickup time are selected
            if (pickupDateValue && pickupTimeValue) {
                // Both pickup date and time are selected, enable return time
                returnTime.prop('disabled', false);
                returnTime.removeClass('disabled');
                returnTimeInfo.hide();
                
                // Since minimum rental is 1 day, all return time options are available
                // (no same-day rental restrictions needed)
                returnTime.find('option').each(function() {
                    $(this).prop('disabled', false);
                    $(this).removeClass('disabled-option');
                });
            } else {
                // Either pickup date or pickup time is missing, disable return time
                returnTime.prop('disabled', true);
                returnTime.addClass('disabled');
                if (pickupDateValue && !pickupTimeValue) {
                    returnTimeInfo.text('*Please select pickup time first');
                    returnTimeInfo.show();
                } else if (!pickupDateValue) {
                    returnTimeInfo.text('*Please select pickup date and time first');
                    returnTimeInfo.show();
                } else {
                    returnTimeInfo.hide();
                }
            }
        }
        
        // Helper function to get time index for comparison
        function getTimeIndex(timeValue) {
            const times = ['08:30', '09:30', '10:30', '11:30', '12:30', '13:30', '14:30', '15:30', '16:30', '17:30'];
            const index = times.indexOf(timeValue);
            return index >= 0 ? index : -1; // Return -1 if time not found
        }
        
        // Initialize location fields
        toggleLocFields();
        
        // Initialize time validation
        validateTimeSelection();
        
        // Add click event listener to info icon
        $(document).on('click', '.oom-info-icon', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showRentalRateInfo();
        });
        
        // Add MutationObserver to watch for programmatic changes to input values (Google Autocomplete)
        function createInputObserver(inputElement, callback) {
            if (!inputElement || !inputElement.length) return;
            
            const element = inputElement[0];
            // Store the initial value
            let lastValue = element.value;
            
            // Create observer to watch for attribute changes
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                        const newValue = element.value;
                        if (newValue !== lastValue) {
                            lastValue = newValue;
                            callback();
                        }
                    }
                });
            });
            
            // Start observing
            observer.observe(element, {
                attributes: true,
                attributeFilter: ['value']
            });
            
            // Also check for value changes periodically (fallback for Google Autocomplete)
            setInterval(function() {
                const currentValue = element.value;
                if (currentValue !== lastValue) {
                    lastValue = currentValue;
                    callback();
                }
            }, 200);
        }
        
        // Apply observers to location input fields
        createInputObserver(pickupOther, toggleLocFields);
        createInputObserver(dropoffOther, toggleLocFields);
        
        // Initialize Google Places Autocomplete for location fields
        function initializeGoogleAutocomplete() {
            if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                // Singapore-focused autocomplete options
                const singaporeBounds = new google.maps.LatLngBounds(
                    new google.maps.LatLng(1.2, 103.6), // Southwest corner
                    new google.maps.LatLng(1.5, 104.1)  // Northeast corner
                );
                
                const autocompleteOptions = {
                    types: ['establishment', 'geocode'],
                    componentRestrictions: { country: 'sg' },
                    bounds: singaporeBounds,
                    strictBounds: false
                };
                
                // Initialize autocomplete for pickup location
                if (pickupOther.length && pickupOther[0]) {
                    const pickupAutocomplete = new google.maps.places.Autocomplete(pickupOther[0], autocompleteOptions);
                    pickupAutocomplete.addListener('place_changed', function() {
                        const place = pickupAutocomplete.getPlace();
                        if (place.formatted_address) {
                            pickupOther.val(place.formatted_address);
                            toggleLocFields();
                            window.updateOomLocationText('oom-daily-rental-pickup-location-other');
                        }
                    });
                }
                
                // Initialize autocomplete for dropoff location
                if (dropoffOther.length && dropoffOther[0]) {
                    const dropoffAutocomplete = new google.maps.places.Autocomplete(dropoffOther[0], autocompleteOptions);
                    dropoffAutocomplete.addListener('place_changed', function() {
                        const place = dropoffAutocomplete.getPlace();
                        if (place.formatted_address) {
                            dropoffOther.val(place.formatted_address);
                            toggleLocFields();
                            window.updateOomLocationText('oom-daily-rental-drop-off-location-other');
                        }
                    });
                }
            }
        }
        
        // Initialize autocomplete when Google Maps API is loaded
        if (typeof google !== 'undefined' && google.maps && google.maps.places) {
            initializeGoogleAutocomplete();
        } else {
            // Wait for Google Maps API to load
            window.addEventListener('load', function() {
                if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                    initializeGoogleAutocomplete();
                }
            });
        }

        // Add some debugging to check if datepickers are initialized
        console.log('Datepicker initialization check:');
        console.log('Pick-Up date element:', pickupDate.length);
        console.log('Return date element:', returnDate.length);
        console.log('Pickup date has datepicker:', pickupDate.hasClass('hasDatepicker'));
        console.log('Return date has datepicker:', returnDate.hasClass('hasDatepicker'));

        $('[id^="daily-rental-btn-"]').on("click", function (e) {
            const data = {
                pickup_date: pickupDate.val().trim(),
                pickup_time: pickupTime.val().trim(),
                dropoff_date: returnDate.val().trim(),
                dropoff_time: returnTime.val().trim(),
                pickup_location: getLocText(pickupLoc, pickupOther),
                dropoff_location: getLocText(dropoffLoc, dropoffOther)
            };

            // Debug logging
            console.log('Form data:', data);
            console.log('Pick-Up date value:', pickupDate.val());
            console.log('Return date value:', returnDate.val());

            // Check if any required field is empty or contains only whitespace
            const requiredFields = {
                'Pickup Date': data.pickup_date,
                'Pickup Time': data.pickup_time,
                'Return Date': data.dropoff_date,
                'Return Time': data.dropoff_time,
                'Pickup Location': data.pickup_location,
                'Drop-off Location': data.dropoff_location
            };

            const emptyFields = [];
            for (const [fieldName, value] of Object.entries(requiredFields)) {
                if (!value || value === '' || value.trim() === '') {
                    emptyFields.push(fieldName);
                }
            }

            if (emptyFields.length > 0) {
                e.preventDefault();
                showUserFriendlyNotification(emptyFields);
                return;
            }

            sessionStorage.setItem('oom_daily_rental_data', JSON.stringify(data));
        });
    });
    </script>
    <?php
    return ob_get_clean();
});



//Single Rental Form Tweaks
add_shortcode('oom_daily_rental_hidden_fields', function () {
    ob_start();
    ?>
    <div id="oom-daily-rental-hidden-fields"></div>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const data = JSON.parse(sessionStorage.getItem('oom_daily_rental_data') || '{}');
        const hiddenFieldContainer = document.getElementById('oom-daily-rental-hidden-fields');

        if (!Object.keys(data).length) return;

        const fields = {
            'oom-daily-rental-pickup-date': data.pickup_date,
            'oom-daily-rental-pickup-time': data.pickup_time,
            'oom-daily-rental-drop-off-date': data.dropoff_date,
            'oom-daily-rental-drop-off-time': data.dropoff_time,
            'oom-daily-rental-pickup-location': data.pickup_location,
            'oom-daily-rental-drop-off-location': data.dropoff_location
        };

        for (const [name, value] of Object.entries(fields)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            hiddenFieldContainer.appendChild(input);
        }
    });
    </script>
    <?php
    return ob_get_clean();
});

add_shortcode('oom_single_rental_form', function () {
    ob_start();
    ?>
    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    
    <style>
    /* Google Places Autocomplete Styling */
    .pac-container {
        z-index: 9999 !important;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border: 1px solid #e0e0e0;
        font-family: inherit;
    }
    
    .pac-item {
        padding: 8px 12px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    
    .pac-item:hover {
        background-color: #f8f9fa;
    }
    
    .pac-item:last-child {
        border-bottom: none;
    }
    
    .pac-item-query {
        font-weight: 500;
        color: #333;
    }
    
    .pac-matched {
        font-weight: bold;
        color: #007bff;
    }
    
    .pac-secondary-text {
        color: #666;
        font-size: 0.9em;
    }
    
    /* Location input styling */
    input[id*="location-other"] {
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%23666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>');
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 16px;
        padding-right: 40px;
    }
    
    input[id*="location-other"]:focus {
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%23007bff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>');
    }
    
    /* Disabled time select styling for single rental form */
    select[id*="to-time"].disabled,
    select[id*="to-time"]:disabled {
        background-color: #f5f5f5 !important;
        color: #999 !important;
        cursor: not-allowed !important;
        opacity: 0.6;
        border-color: #ddd !important;
    }
    
    select[id*="to-time"].disabled option,
    select[id*="to-time"]:disabled option {
        color: #999 !important;
    }
    
    /* Visual feedback for disabled state */
    select[id*="to-time"].disabled:hover,
    select[id*="to-time"]:disabled:hover {
        background-color: #f5f5f5 !important;
        border-color: #ddd !important;
    }
    

    </style>
    
    <form action="" class="oom-single-rental-form">
        <div class="oom-single-rental-container">
            <?php
            // Start dynamic loop (add-on-features) - which is from JetEngine Metabox - for the single rental page 
            $post_id = get_the_ID();
            $add_on_features = get_post_meta($post_id, 'add-on-features', true);
            
            if (!empty($add_on_features) && is_array($add_on_features)) {
                echo '<div class="oom-single-rental-add-on-features">';
                foreach ($add_on_features as $feature) {
                    $feature_label = isset($feature['add-on-feature-label']) ? $feature['add-on-feature-label'] : '';
                    $feature_options = isset($feature['add-on-feature-options']) ? $feature['add-on-feature-options'] : '';
                    $feature_pricing = isset($feature['add-on-feature-pricing']) ? $feature['add-on-feature-pricing'] : '';
                    $feature_name = sanitize_title($feature_label);
                    
                    echo '<div class="oom-single-rental-add-on-features-item" data-feature-price="' . esc_attr($feature_pricing) . '" data-feature-name="' . esc_attr($feature_name) . '">';
                    
                    // Show label + price if pricing is not empty
                    if (!empty($feature_pricing)) {
                        echo '<label>' . esc_html($feature_label) . '* + SGD ' . esc_html($feature_pricing) . '</label>';
                    } else {
                        echo '<label>' . esc_html($feature_label) . '*</label>';
                    }
                    
                    // Radio button logic based on add-on-feature-options value
                    if ($feature_options == '1') {
                        // Show Yes and No radio buttons
                        echo '<div class="oom-radio-group">';
                        echo '<div class="oom-radio-group-item">';
                        echo '<input type="radio" id="' . $feature_name . '-yes" name="add-on-' . $feature_name . '" value="1" class="feature-radio" data-feature-name="' . esc_attr($feature_name) . '" data-feature-price="' . esc_attr($feature_pricing) . '">';
                        echo '<label for="' . $feature_name . '-yes">Yes</label>';
                        echo '</div>';
                        
                        echo '<div class="oom-radio-group-item">';
                        echo '<input type="radio" id="' . $feature_name . '-no" name="add-on-' . $feature_name . '" value="2" class="feature-radio" data-feature-name="' . esc_attr($feature_name) . '" data-feature-price="' . esc_attr($feature_pricing) . '">';
                        echo '<label for="' . $feature_name . '-no">No</label>';
                        echo '</div>';
                        echo '</div>';
                    } elseif ($feature_options == '2') {
                        // Show No (only) radio button
                        echo '<div class="oom-radio-group">';
                        echo '<div class="oom-radio-group-item">';
                        echo '<input type="radio" id="' . $feature_name . '-no" name="add-on-' . $feature_name . '" value="2" checked class="feature-radio" data-feature-name="' . esc_attr($feature_name) . '" data-feature-price="' . esc_attr($feature_pricing) . '">';
                        echo '<label for="' . $feature_name . '-no">No</label>';
                        echo '</div>';
                        echo '</div>';
                    } elseif ($feature_options == '3') {
                        // Show Yes (only) radio button
                        echo '<div class="oom-radio-group">';
                        echo '<div class="oom-radio-group-item">';
                        echo '<input type="radio" id="' . $feature_name . '-yes" name="add-on-' . $feature_name . '" value="1" checked class="feature-radio" data-feature-name="' . esc_attr($feature_name) . '" data-feature-price="' . esc_attr($feature_pricing) . '">';
                        echo '<label for="' . $feature_name . '-yes">Yes</label>';
                        echo '</div>';
                        echo '</div>';
                    }
                    
                    echo '</div>';
                }
                echo '</div>';
            }
            // End dynamic loop (add-on-features)
            ?>

            <div class="oom-single-rental-details">
               <label for="oom-single-rental-from-date">Rental From</label>
               <div class="oom-single-rental-flex">
                <div class="oom-date-input-wrapper">
                    <div class="oom-date-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                            <line x1="16" y1="2" x2="16" y2="6" stroke="currentColor" stroke-width="2"/>
                            <line x1="8" y1="2" x2="8" y2="6" stroke="currentColor" stroke-width="2"/>
                            <line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <input type="text" id="oom-single-rental-from-date" name="oom-single-rental-from-date" placeholder="Select Date" autocomplete="off">
                </div>
                <div class="oom-time-input-wrapper">
                    <div class="oom-time-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                            <polyline points="12,6 12,12 16,14" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <select name="oom-single-rental-from-time" id="oom-single-rental-from-time">
                        <option value="">Select Time</option>
                        <?php
                        $times = ['08:30', '09:30', '10:30', '11:30', '12:30', '13:30', '14:30', '15:30', '16:30', '17:30'];
                        $readable_times = ['08:30am', '09:30am', '10:30am', '11:30am', '12:30pm', '01:30pm', '02:30pm', '03:30pm', '04:30pm', '05:30pm'];
                        foreach ($times as $i => $time) {
                            $label = $readable_times[$i] ?? $time;
                            echo "<option value=\"$time\">$label</option>";
                        }
                        ?>
                    </select>
                </div>
               </div>
            </div>

            <div class="oom-single-rental-details">
               <label for="oom-single-rental-to-date">Rental To</label>
               <div class="oom-single-rental-flex">
                <div class="oom-date-input-wrapper">
                    <div class="oom-date-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                            <line x1="16" y1="2" x2="16" y2="6" stroke="currentColor" stroke-width="2"/>
                            <line x1="8" y1="2" x2="8" y2="6" stroke="currentColor" stroke-width="2"/>
                            <line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <input type="text" id="oom-single-rental-to-date" name="oom-single-rental-to-date" placeholder="Select Date" autocomplete="off" disabled>
                </div>
                <div class="oom-time-input-wrapper">
                    <div class="oom-time-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                            <polyline points="12,6 12,12 16,14" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <select name="oom-single-rental-to-time" id="oom-single-rental-to-time" disabled>
                        <option value="">Select Time</option>
                        <?php
                        foreach ($times as $i => $time) {
                            $label = $readable_times[$i] ?? $time;
                            echo "<option value=\"$time\">$label</option>";
                        }
                        ?>
                    </select>
                </div>
               </div>
            </div>

            <div class="oom-single-rental-details">
                <label for="oom-single-rental-pickup-point" id="oom-single-rental-pickup-point-label">Pick-Up Point</label>
                <select name="oom-single-rental-pickup-point" id="oom-single-rental-pickup-point" data-surcharge="<?php echo esc_attr(get_option('oom_pickup_dropoff_charge', '30.00')); ?>">
                    <option value="">Select Location</option>
                    <option value="1">ComfortDelGro Rent-A-Car</option>
                    <option value="2">Other Location</option>
                </select>
                <div class="oom-single-rental-pickup-other-location" style="display: none;">
                    <input type="text" id="oom-single-rental-pickup-location-other" name="oom-single-rental-pickup-location-other" placeholder="Address">
                </div>
                <p class="oom-single-rental-pickup-location-text"></p>
            </div>

            <div class="oom-single-rental-details">
                <label for="oom-single-rental-drop-off-point" id="oom-single-rental-drop-off-point-label">Drop-Off Point</label>
                <select name="oom-single-rental-drop-off-point" id="oom-single-rental-drop-off-point" data-surcharge="<?php echo esc_attr(get_option('oom_pickup_dropoff_charge', '30.00')); ?>">
                    <option value="">Select Location</option>
                    <option value="1">ComfortDelGro Rent-A-Car</option>
                    <option value="2">Other Location</option>
                </select>
                <div class="oom-single-rental-drop-off-other-location" style="display: none;">
                    <input type="text" id="oom-single-rental-drop-off-location-other" name="oom-single-rental-drop-off-location-other" placeholder="Address">
                </div>
                <p class="oom-single-rental-drop-off-location-text"></p>
            </div>

            <div class="oom-single-rental-information">
                
                <?php
                $minimum_days_for_discount = get_post_meta($post_id, 'minimum_days_for_discount', true);
                $daily_discount_amount = get_post_meta($post_id, 'daily_discount_amount', true);
                
                if (!empty($minimum_days_for_discount) && !empty($daily_discount_amount)) {
                    
                }
                ?>
                <div id="oom-price-summary" style="display: none;">
                    <p>
                        <b>Price Summary:</b><br/>
                    </p>
                    <p>
                        <b>Rental Cost: </b> SGD <span id="oom-summary-rental-cost">0.00</span> = <span id="oom-summary-days">0</span> days × Daily Rate (SGD <span id="oom-summary-daily-rate">0.00</span>)<span id="oom-summary-discount-section" style="display: none;"> - Discount (SGD <span id="oom-summary-discount">0.00</span>)</span><span id="oom-summary-hours-section" style="display: none;"> + <span id="oom-summary-hours">0</span> hours × Additional Hourly Rate (SGD <span id="oom-summary-hourly-rate">0.00</span>)</span><span id="oom-summary-pickup-section" style="display: none;"> + Pick-up Fee (SGD <span id="oom-summary-pickup-fee">0.00</span>)</span><span id="oom-summary-dropoff-section" style="display: none;"> + Drop-off Fee (SGD <span id="oom-summary-dropoff-fee">0.00</span>)</span>
                    </p>

                    <p>
                    <hr>
                    </p>
                </div>
                
               
                
                <div id="oom-rental-duration" style="margin-top: 10px; padding: 0px; background-color: transparent; border-radius: 5px; display: none; margin-bottom: 10px; padding-bottom: 10px;">
                    <p style="margin: 0; font-weight: bold;">Rental Duration: <span id="oom-duration-text">-</span></p>
                </div>
				<div style="display:initial">
					<div id="oom-feature-breakdown" style="display: none; margin-top: 10px; padding: 0px; background-color: #f8f9fa; border-radius: 5px;">
						<div id="oom-feature-breakdown-list"></div>
					</div>
				</div>

                <p><strong>Security Deposit:</strong> + SGD <?php echo esc_html(get_option('oom_security_deposit', '500.00')); ?> (Refund after 14 days of vehicle return)</p>
                
            </div>

        </div>
    </form>

    <!-- jQuery and jQuery UI -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    
    <!-- Google Places API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr(get_option('oom_google_place_api', 'AIzaSyAK5x3pdAgDPv-QsfX4SXHtmULjYBW0NIE')); ?>&libraries=places&loading=async"></script>
    
    <script>
    // Global function to update location text when Google Autocomplete fills a field
    window.updateOomSingleRentalLocationText = function(fieldId) {
        if (fieldId === 'oom-single-rental-pickup-location-other' || fieldId === 'oom-single-rental-drop-off-location-other') {
            // For single rental form
            if (typeof toggleLocFields === 'function') {
                toggleLocFields();
            }
            if (typeof updatePricing === 'function') {
                updatePricing();
            }
            if (typeof updateLocationLabels === 'function') {
                updateLocationLabels();
            }
        }
    };
    
    document.addEventListener("DOMContentLoaded", function () {
        // Get blockout dates from PHP
        const blockoutDates = <?php echo json_encode(oom_get_blockout_dates_for_datepicker()); ?>;
        
        // Get data from sessionStorage (mapping from oom_daily_rental_hidden_fields)
        const data = JSON.parse(sessionStorage.getItem('oom_daily_rental_data') || '{}');
        
        // Location elements
        const pickupLoc = document.querySelector('#oom-single-rental-pickup-point');
        const dropoffLoc = document.querySelector('#oom-single-rental-drop-off-point');
        const pickupOther = document.querySelector('#oom-single-rental-pickup-location-other');
        const dropoffOther = document.querySelector('#oom-single-rental-drop-off-location-other');
        const pickupText = document.querySelector('.oom-single-rental-pickup-location-text');
        const dropoffText = document.querySelector('.oom-single-rental-drop-off-location-text');
        
        // Field mapping for dates and times
        const fieldMappings = {
            'oom-single-rental-from-date': data.pickup_date,
            'oom-single-rental-from-time': data.pickup_time,
            'oom-single-rental-to-date': data.dropoff_date,
            'oom-single-rental-to-time': data.dropoff_time
        };

        // Apply mapped values for dates and times
        for (const [fieldId, mappedValue] of Object.entries(fieldMappings)) {
            const field = document.getElementById(fieldId);
            if (field && mappedValue) {
                field.value = mappedValue;
            }
        }

        // Check and enable to date field if from date is already set
        setTimeout(function() {
            checkAndEnableToDate();
        }, 100);

        // Handle location mapping from daily rental form
        function mapLocationFromDailyRental(locationText) {
            const comfortDelGroLocation = "<?php echo esc_js(get_option('oom_location', '205 Braddell Road Blk H Singapore 479401')); ?>";
            
            if (locationText === comfortDelGroLocation) {
                return "1"; // ComfortDelGro Rent-A-Car
            } else if (locationText && locationText.trim() !== "") {
                return "2"; // Other Location
            }
            return ""; // No selection
        }

        // Apply location data from daily rental form if available
        if (data.pickup_location) {
            const pickupValue = mapLocationFromDailyRental(data.pickup_location);
            if (pickupValue === "1") {
                pickupLoc.value = "1";
            } else if (pickupValue === "2") {
                pickupLoc.value = "2";
                pickupOther.value = data.pickup_location;
            }
        }

        if (data.dropoff_location) {
            const dropoffValue = mapLocationFromDailyRental(data.dropoff_location);
            if (dropoffValue === "1") {
                dropoffLoc.value = "1";
            } else if (dropoffValue === "2") {
                dropoffLoc.value = "2";
                dropoffOther.value = data.dropoff_location;
            }
        }
        
        // Initialize location fields after setting values from daily rental form
        toggleLocFields();
        updateLocationLabels();
        
        // Add MutationObserver to watch for programmatic changes to input values
        function createInputObserver(inputElement, callback) {
            if (!inputElement) return;
            
            // Store the initial value
            let lastValue = inputElement.value;
            
            // Create observer to watch for attribute changes
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                        const newValue = inputElement.value;
                        if (newValue !== lastValue) {
                            lastValue = newValue;
                            callback();
                        }
                    }
                });
            });
            
            // Start observing
            observer.observe(inputElement, {
                attributes: true,
                attributeFilter: ['value']
            });
            
            // Also check for value changes periodically (fallback for Google Autocomplete)
            setInterval(function() {
                const currentValue = inputElement.value;
                if (currentValue !== lastValue) {
                    lastValue = currentValue;
                    callback();
                }
            }, 200);
        }
        
        // Apply observers to location input fields
        createInputObserver(pickupOther, function() {
            toggleLocFields();
            updatePricing();
            updateLocationLabels();
        });
        
        createInputObserver(dropoffOther, function() {
            toggleLocFields();
            updatePricing();
            updateLocationLabels();
        });
        
        // Initialize Google Places Autocomplete for location fields
        function initializeGoogleAutocomplete() {
            if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                // Singapore-focused autocomplete options
                const singaporeBounds = new google.maps.LatLngBounds(
                    new google.maps.LatLng(1.2, 103.6), // Southwest corner
                    new google.maps.LatLng(1.5, 104.1)  // Northeast corner
                );
                
                const autocompleteOptions = {
                    types: ['establishment', 'geocode'],
                    componentRestrictions: { country: 'sg' },
                    bounds: singaporeBounds,
                    strictBounds: false
                };
                
                // Initialize autocomplete for pickup location
                if (pickupOther) {
                    const pickupAutocomplete = new google.maps.places.Autocomplete(pickupOther, autocompleteOptions);
                    pickupAutocomplete.addListener('place_changed', function() {
                        const place = pickupAutocomplete.getPlace();
                        if (place.formatted_address) {
                            pickupOther.value = place.formatted_address;
                            toggleLocFields();
                            updatePricing();
                            updateLocationLabels();
                            window.updateOomSingleRentalLocationText('oom-single-rental-pickup-location-other');
                        }
                    });
                }
                
                // Initialize autocomplete for dropoff location
                if (dropoffOther) {
                    const dropoffAutocomplete = new google.maps.places.Autocomplete(dropoffOther, autocompleteOptions);
                    dropoffAutocomplete.addListener('place_changed', function() {
                        const place = dropoffAutocomplete.getPlace();
                        if (place.formatted_address) {
                            dropoffOther.value = place.formatted_address;
                            toggleLocFields();
                            updatePricing();
                            updateLocationLabels();
                            window.updateOomSingleRentalLocationText('oom-single-rental-drop-off-location-other');
                        }
                    });
                }
            }
        }
        
        // Initialize autocomplete when Google Maps API is loaded
        if (typeof google !== 'undefined' && google.maps && google.maps.places) {
            initializeGoogleAutocomplete();
        } else {
            // Wait for Google Maps API to load
            window.addEventListener('load', function() {
                if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                    initializeGoogleAutocomplete();
                }
            });
        }

        function getLocText(selectEl, inputEl) {
            return selectEl.value === "1" ? "<?php echo esc_js(get_option('oom_location', '205 Braddell Road Blk H Singapore 479401')); ?>" :
                   selectEl.value === "2" ? inputEl.value.trim() : "";
        }

        function toggleLocFields() {
            document.querySelector('.oom-single-rental-pickup-other-location').style.display = pickupLoc.value === "2" ? "block" : "none";
            document.querySelector('.oom-single-rental-drop-off-other-location').style.display = dropoffLoc.value === "2" ? "block" : "none";
            
            // Show/hide location text based on selection
            if (pickupLoc.value === "1") {
                pickupText.textContent = getLocText(pickupLoc, pickupOther);
                pickupText.style.display = "block";
            } else {
                pickupText.style.display = "none";
            }
            
            if (dropoffLoc.value === "1") {
                dropoffText.textContent = getLocText(dropoffLoc, dropoffOther);
                dropoffText.style.display = "block";
            } else {
                dropoffText.style.display = "none";
            }
        }

        // Initialize jQuery datepicker for rental from date
        const fromDateField = $('#oom-single-rental-from-date');
        if (fromDateField.length) {
            // Calculate minimum date (dynamic days ahead)
            const today = new Date();
            const advancedDays = <?php echo intval(get_option('oom_advanced_booking_days', '2')); ?>;
            today.setDate(today.getDate() + advancedDays);
            const minDate = today;

            fromDateField.datepicker({
                dateFormat: 'dd-mm-yy',
                minDate: minDate,
                changeMonth: true,
                changeYear: true,
                beforeShowDay: function(date) {
                    // Disable weekends (0 = Sunday, 6 = Saturday)
                    const day = date.getDay();
                    const isWeekend = (day === 0 || day === 6);
                    
                    // Check if date is in blockout dates
                    const dateString = $.datepicker.formatDate('dd-mm-yy', date);
                    const isBlockedOut = blockoutDates.includes(dateString);
                    
                    // Return [isSelectable, cssClass]
                    return [!isWeekend && !isBlockedOut, isBlockedOut ? 'blockout-date' : ''];
                },
                onSelect: function(dateText) {
                    console.log('From date selected:', dateText);
                    // Enable return date picker and set its minimum date
                    checkAndEnableToDate();
                    updatePricing();
                    validateTimeSelection();
                }
            }).on('keydown', function(e) {
                // Prevent manual typing
                e.preventDefault();
                return false;
            });
        }

        // Initialize jQuery datepicker for rental to date
        const toDateField = $('#oom-single-rental-to-date');
        if (toDateField.length) {
            toDateField.datepicker({
                dateFormat: 'dd-mm-yy',
                changeMonth: true,
                changeYear: true,
                beforeShowDay: function(date) {
                    // Disable weekends (0 = Sunday, 6 = Saturday)
                    const day = date.getDay();
                    const isWeekend = (day === 0 || day === 6);
                    
                    // Check if date is in blockout dates
                    const dateString = $.datepicker.formatDate('dd-mm-yy', date);
                    const isBlockedOut = blockoutDates.includes(dateString);
                    
                    // Return [isSelectable, cssClass]
                    return [!isWeekend && !isBlockedOut, isBlockedOut ? 'blockout-date' : ''];
                },
                onSelect: function(dateText) {
                    console.log('To date selected:', dateText);
                    updatePricing();
                    validateTimeSelection();
                }
            }).on('keydown', function(e) {
                // Prevent manual typing
                e.preventDefault();
                return false;
            });
        }

        // Add event listeners for date and time fields to update pricing
        const fromTimeField = document.getElementById('oom-single-rental-from-time');
        const toTimeField = document.getElementById('oom-single-rental-to-time');
        
        if (fromTimeField) {
            fromTimeField.addEventListener('change', function() {
                updatePricing();
                validateTimeSelection();
                
                // Since minimum rental is 1 day, same-day rentals are not allowed
                // No need to validate same-day time conflicts
            });
        }
        if (toTimeField) {
            toTimeField.addEventListener('change', function() {
                updatePricing();
                validateTimeSelection();
            });
        }
        
        // Function to check and enable to date field
        function checkAndEnableToDate() {
            const fromDateValue = $('#oom-single-rental-from-date').val();
            if (fromDateValue && fromDateValue.trim() !== '') {
                $('#oom-single-rental-to-date').prop('disabled', false);
                
                // Set minimum date to the day after pickup date (minimum 1 day rental)
                const fromDate = $.datepicker.parseDate('dd-mm-yy', fromDateValue);
                const nextDay = new Date(fromDate);
                nextDay.setDate(nextDay.getDate() + 1);
                const nextDayString = $.datepicker.formatDate('dd-mm-yy', nextDay);
                
                $('#oom-single-rental-to-date').datepicker('option', 'minDate', nextDayString);
            } else {
                $('#oom-single-rental-to-date').prop('disabled', true);
                $('#oom-single-rental-to-date').val('');
            }
        }

        // Function to validate time selection
        function validateTimeSelection() {
            const fromDateValue = $('#oom-single-rental-from-date').val();
            const toDateValue = $('#oom-single-rental-to-date').val();
            const fromTimeValue = fromTimeField.value;
            const toTimeValue = toTimeField.value;
            
            // Ensure to date field is enabled if from date is selected
            checkAndEnableToDate();
            
            // Return time should be disabled until pickup time is selected
            if (fromTimeValue && fromTimeValue.trim() !== '') {
                // Pickup time is selected, enable return time
                toTimeField.disabled = false;
                toTimeField.classList.remove('disabled');
                
                // Since minimum rental is 1 day, all return time options are available
                // (no same-day rental restrictions needed)
                $(toTimeField).find('option').each(function() {
                    $(this).prop('disabled', false);
                    $(this).removeClass('disabled-option');
                });
            } else {
                // Pickup time is not selected, disable return time
                toTimeField.disabled = true;
                toTimeField.classList.add('disabled');
            }
        }
        
        // Helper function to get time index for single rental form
        function getSingleRentalTimeIndex(timeValue) {
            const times = ['08:30', '09:30', '10:30', '11:30', '12:30', '13:30', '14:30', '15:30', '16:30', '17:30'];
            const index = times.indexOf(timeValue);
            return index >= 0 ? index : -1; // Return -1 if time not found
        }

        // Add event listener to from date field to ensure to date field is enabled
        fromDateField.on('change', function() {
            checkAndEnableToDate();
        });

        // Location change event listeners
        pickupLoc.addEventListener('change', function() {
            toggleLocFields();
            updatePricing();
            updateLocationLabels();
        });
        dropoffLoc.addEventListener('change', function() {
            toggleLocFields();
            updatePricing();
            updateLocationLabels();
        });
        pickupOther.addEventListener('input', toggleLocFields);
        dropoffOther.addEventListener('input', toggleLocFields);
        
        // Additional event listeners for Google Autocomplete compatibility
        pickupOther.addEventListener('blur', function() {
            toggleLocFields();
            updatePricing();
            updateLocationLabels();
        });
        pickupOther.addEventListener('focus', function() {
            toggleLocFields();
            updatePricing();
            updateLocationLabels();
        });
        dropoffOther.addEventListener('blur', function() {
            toggleLocFields();
            updatePricing();
            updateLocationLabels();
        });
        dropoffOther.addEventListener('focus', function() {
            toggleLocFields();
            updatePricing();
            updateLocationLabels();
        });
        
        // Listen for Google Places Autocomplete place_changed event
        if (typeof google !== 'undefined' && google.maps && google.maps.places) {
            // Function to handle place selection
            function handlePlaceSelection(inputElement) {
                // Use setTimeout to ensure the input value is updated
                setTimeout(() => {
                    toggleLocFields();
                    updatePricing();
                    updateLocationLabels();
                }, 100);
            }
            
            // Add place_changed listeners if autocomplete is initialized
            document.addEventListener('DOMContentLoaded', function() {
                // Check if autocomplete is already initialized on our fields
                if (pickupOther.autocomplete) {
                    pickupOther.autocomplete.addListener('place_changed', function() {
                        handlePlaceSelection(pickupOther);
                    });
                }
                if (dropoffOther.autocomplete) {
                    dropoffOther.autocomplete.addListener('place_changed', function() {
                        handlePlaceSelection(dropoffOther);
                    });
                }
            });
        }

        // Function to update location labels dynamically
        function updateLocationLabels() {
            const pickupLabel = document.getElementById('oom-single-rental-pickup-point-label');
            const dropoffLabel = document.getElementById('oom-single-rental-drop-off-point-label');
            
            if (pickupLabel) {
                const pickupSurcharge = parseFloat(pickupLoc.dataset.surcharge || '0');
                if (pickupLoc.value === '2' && !isNaN(pickupSurcharge) && pickupSurcharge > 0) {
                    pickupLabel.textContent = `Pick-Up Point + SGD ${formatNumberWithCommas(pickupSurcharge)}`;
                } else {
                    pickupLabel.textContent = 'Pick-Up Point';
                }
            }
            
            if (dropoffLabel) {
                const dropoffSurcharge = parseFloat(dropoffLoc.dataset.surcharge || '0');
                if (dropoffLoc.value === '2' && !isNaN(dropoffSurcharge) && dropoffSurcharge > 0) {
                    dropoffLabel.textContent = `Drop-Off Point + SGD ${formatNumberWithCommas(dropoffSurcharge)}`;
                } else {
                    dropoffLabel.textContent = 'Drop-Off Point';
                }
            }
        }

        // Calculate rental duration and pricing
        function calculateRentalDuration() {
            const fromDate = $('#oom-single-rental-from-date').val();
            const fromTime = document.getElementById('oom-single-rental-from-time').value;
            const toDate = $('#oom-single-rental-to-date').val();
            const toTime = document.getElementById('oom-single-rental-to-time').value;

            if (!fromDate || !fromTime || !toDate || !toTime) {
                return { totalDays: 0, totalHours: 0, additionalHours: 0 };
            }

            // Convert dd-mm-yyyy to yyyy-mm-dd for Date constructor
            const fromDateParts = fromDate.split('-');
            const toDateParts = toDate.split('-');
            
            if (fromDateParts.length !== 3 || toDateParts.length !== 3) {
                return { totalDays: 0, totalHours: 0, additionalHours: 0 };
            }
            
            const fromDateFormatted = fromDateParts[2] + '-' + fromDateParts[1] + '-' + fromDateParts[0];
            const toDateFormatted = toDateParts[2] + '-' + toDateParts[1] + '-' + toDateParts[0];

            // Create Date objects
            const fromDateTime = new Date(fromDateFormatted + 'T' + fromTime);
            const toDateTime = new Date(toDateFormatted + 'T' + toTime);

            // Calculate total duration in milliseconds
            const durationMs = toDateTime - fromDateTime;
            
            if (durationMs <= 0) {
                return { totalDays: 0, totalHours: 0, additionalHours: 0 };
            }

            // Convert to hours
            const totalHours = Math.ceil(durationMs / (1000 * 60 * 60));
            
            // Calculate full days (24-hour periods)
            const totalDays = Math.floor(totalHours / 24);
            
            // Calculate additional hours beyond full days
            const additionalHours = totalHours % 24;

            return { totalDays, totalHours, additionalHours };
        }

        // Format number with commas every 3 digits
        function formatNumberWithCommas(number) {
            return number.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Price recalculation functionality
        function updatePricing() {
            // Ensure to date field is enabled if from date is selected
            checkAndEnableToDate();
            
            const pricingElement = document.querySelector('.oom-single-rental-pricing .amount');
            const pricingContainer = document.querySelector('.oom-single-rental-pricing');
            if (!pricingElement || !pricingContainer) return;

            // Get base price from the data attribute
            let basePrice = parseFloat(pricingContainer.dataset.basePrice || '0');
            if (isNaN(basePrice)) {
                // Fallback: try to parse from the current displayed price
                basePrice = parseFloat(pricingElement.textContent.trim().replace(/[^\d.]/g, ''));
            }

            // Get discount information from data attributes
            const minimumDaysForDiscount = parseInt(pricingContainer.dataset.minimumDaysForDiscount || '0');
            const dailyDiscountAmount = parseFloat(pricingContainer.dataset.dailyDiscountAmount || '0');

            // Calculate rental duration
            const duration = calculateRentalDuration();
            
            // Calculate add-on features daily cost
            const selectedFeatures = {};
            let dailyAddonCost = 0;
            
            // Get all add-on radio buttons
            const featureRadios = document.querySelectorAll('.feature-radio');
            console.log('Found add-on radios:', featureRadios.length);
            
            featureRadios.forEach(radio => {
                console.log('Radio:', radio.name, 'checked:', radio.checked, 'value:', radio.value);
                if (radio.checked && radio.value === '1') {
                    const featureName = radio.dataset.featureName;
                    const featurePrice = parseFloat(radio.dataset.featurePrice || '0');
                    
                    console.log('Adding add-on:', featureName, 'price:', featurePrice);
                    if (!isNaN(featurePrice) && featurePrice > 0) {
                        selectedFeatures[featureName] = featurePrice;
                        dailyAddonCost += featurePrice;
                    }
                }
            });
            
            // Calculate effective daily rate (base price + add-ons)
            const effectiveDailyRate = basePrice + dailyAddonCost;
            
            // Calculate base rental cost
            let rentalCost = 0;
            let discountApplied = false;
            let totalDiscount = 0;
            
            if (duration.totalDays > 0) {
                // Check if discount should be applied (rental duration >= minimum days for discount)
                if (minimumDaysForDiscount > 0 && dailyDiscountAmount > 0 && duration.totalDays >= minimumDaysForDiscount) {
                    // Apply discount to all days
                    const discountedPricePerDay = effectiveDailyRate - dailyDiscountAmount;
                    rentalCost = discountedPricePerDay * duration.totalDays;
                    discountApplied = true;
                    totalDiscount = dailyDiscountAmount * duration.totalDays;
                } else {
                    // No discount applied
                    rentalCost = effectiveDailyRate * duration.totalDays;
                }
            }
            
            // Apply additional hours pricing logic
            if (duration.additionalHours > 0) {
                // For partial hours (< 5), add-ons must NOT be prorated. Only base price is prorated.
                // For >= 5 hours, it's treated as a full extra day, including add-ons.
                let hourlyBaseRate;
                if (discountApplied) {
                    // Prorate only the discounted base portion (exclude add-ons)
                    hourlyBaseRate = Math.max(basePrice - dailyDiscountAmount, 0) / 5;
                } else {
                    // Prorate only the base portion (exclude add-ons)
                    hourlyBaseRate = basePrice / 5;
                }
                
                if (duration.additionalHours >= 1 && duration.additionalHours <= 4) {
                    // Additional 1 to 4 hours: prorated based on (base daily rate ÷ 5) × number of additional hours
                    rentalCost += hourlyBaseRate * duration.additionalHours;
                } else if (duration.additionalHours >= 5) {
                    // Additional 5 hours or more: full day's rental charge (includes add-ons)
                    if (discountApplied) {
                        rentalCost += (effectiveDailyRate - dailyDiscountAmount);
                        totalDiscount += dailyDiscountAmount;
                    } else {
                        rentalCost += effectiveDailyRate;
                    }
                }
            }

            let totalPrice = rentalCost;

            // Add location surcharges (these are one-time fees, not daily)
            const pickupSurcharge = parseFloat(pickupLoc.dataset.surcharge || '0');
            const dropoffSurcharge = parseFloat(dropoffLoc.dataset.surcharge || '0');
            
            // Apply pickup surcharge if "Other Location" is selected
            if (pickupLoc.value === '2' && !isNaN(pickupSurcharge) && pickupSurcharge > 0) {
                totalPrice += pickupSurcharge;
                selectedFeatures['pickup_point_surcharge'] = pickupSurcharge;
            }
            
            // Apply drop-off surcharge if "Other Location" is selected
            if (dropoffLoc.value === '2' && !isNaN(dropoffSurcharge) && dropoffSurcharge > 0) {
                totalPrice += dropoffSurcharge;
                selectedFeatures['drop_off_point_surcharge'] = dropoffSurcharge;
            }

            // Update the displayed price
            pricingElement.textContent = ' ' + formatNumberWithCommas(totalPrice) + ' ';
            
            // Update duration display
            updateDurationDisplay(duration);
            
            // Update discount notice visibility
            updateDiscountNotice(duration, minimumDaysForDiscount);
            
            // Update feature breakdown
            updateFeatureBreakdown(selectedFeatures, duration, rentalCost, basePrice, effectiveDailyRate, discountApplied, totalDiscount, dailyDiscountAmount, dailyAddonCost);
            
            // Store the selected add-ons for form submission
            window.selectedFeatures = selectedFeatures;
            window.rentalDuration = duration;
            
            // Update hidden fields for Elementor form
            updateElementorFormFields();
        }

        // Update duration display
        function updateDurationDisplay(duration) {
            const durationContainer = document.getElementById('oom-rental-duration');
            const durationText = document.getElementById('oom-duration-text');
            
            if (!durationContainer || !durationText) return;
            
            if (duration && (duration.totalDays > 0 || duration.additionalHours > 0)) {
                let durationString = '';
                if (duration.totalDays > 0) {
                    durationString += `${duration.totalDays} day${duration.totalDays > 1 ? 's' : ''}`;
                }
                if (duration.additionalHours > 0) {
                    if (durationString) durationString += ' + ';
                    durationString += `${duration.additionalHours} hour${duration.additionalHours > 1 ? 's' : ''}`;
                }
                durationText.textContent = durationString;
                durationContainer.style.display = 'block';
            } else {
                durationContainer.style.display = 'none';
            }
        }

        // Update discount notice visibility
        function updateDiscountNotice(duration, minimumDaysForDiscount) {
            const discountNotice = document.getElementById('oom-discount-notice');
            
            if (!discountNotice) return;
            
            // Show discount notice only when user has selected enough days to qualify for discount
            if (duration && duration.totalDays > 0 && minimumDaysForDiscount > 0 && duration.totalDays >= minimumDaysForDiscount) {
                discountNotice.style.display = 'block';
            } else {
                discountNotice.style.display = 'none';
            }
        }

        // Update feature breakdown display
        function updateFeatureBreakdown(selectedFeatures, duration, rentalCost, basePrice, effectiveDailyRate, discountApplied, totalDiscount, dailyDiscountAmount, dailyAddonCost) {
            const breakdownContainer = document.getElementById('oom-feature-breakdown');
            const breakdownList = document.getElementById('oom-feature-breakdown-list');
            
            if (!breakdownContainer || !breakdownList) return;
            
            breakdownList.innerHTML = '';
            
            // Daily Rate breakdown
            if (duration && (duration.totalDays > 0 || duration.additionalHours > 0)) {
                const dailyRateItem = document.createElement('p');
                
                if (dailyAddonCost > 0) {
                    dailyRateItem.innerHTML = `<b>Daily Rate:</b> SGD ${formatNumberWithCommas(basePrice)} (base rate) + SGD ${formatNumberWithCommas(dailyAddonCost)} (add-ons) = SGD ${formatNumberWithCommas(effectiveDailyRate)}`;
                } else {
                    dailyRateItem.innerHTML = `<b>Daily Rate:</b> SGD ${formatNumberWithCommas(basePrice)} (base rate)`;
                }
                
                breakdownList.appendChild(dailyRateItem);
                
                // Add-ons list
                if (dailyAddonCost > 0) {
                    const addonsList = Object.keys(selectedFeatures)
                        .filter(name => name !== 'pickup_point_surcharge' && name !== 'drop_off_point_surcharge')
                        .map(name => name.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase()));
                    
                    if (addonsList.length > 0) {
                        const addonsItem = document.createElement('p');
                        addonsItem.innerHTML = `<i>*Add-ons: ${addonsList.join(', ')}</i>`;
                        breakdownList.appendChild(addonsItem);
                    }
                }
                
                // Discount breakdown
                if (discountApplied && duration.totalDays > 0) {
                    const discountItem = document.createElement('p');
                    discountItem.innerHTML = `<b>Discount:</b> SGD ${formatNumberWithCommas(totalDiscount)} (${duration.totalDays} day${duration.totalDays > 1 ? 's' : ''} × SGD ${formatNumberWithCommas(dailyDiscountAmount)})`;
                    breakdownList.appendChild(discountItem);
                    
                    const discountNote = document.createElement('p');
                    discountNote.innerHTML = `<i>* Discount applies for rentals of ${duration.totalDays} day${duration.totalDays > 1 ? 's' : ''} or longer (SGD ${formatNumberWithCommas(dailyDiscountAmount)} off per day).</i>`;
                    breakdownList.appendChild(discountNote);
                }
                
                // Additional Hourly Rate
                if (duration.additionalHours > 0) {
                    const hourlyItem = document.createElement('p');
                    let hourlyRate = 0;
                    
                    if (duration.additionalHours >= 1 && duration.additionalHours <= 4) {
                        // Prorate using base only (exclude add-ons)
                        hourlyRate = basePrice / 5;
                    } else if (duration.additionalHours >= 5) {
                        hourlyRate = effectiveDailyRate;
                    }
                    
                    hourlyItem.innerHTML = `<b>Additional Hourly Rate:</b> SGD ${formatNumberWithCommas(hourlyRate)}`;
                    breakdownList.appendChild(hourlyItem);
                    
                    const hourlyNote = document.createElement('p');
                    hourlyNote.innerHTML = `<i>* Additional hours are charged prorated (base rate + 5) for 1-4 hours, or as a full day for 5 hours or more.</i>`;
                    breakdownList.appendChild(hourlyNote);
                }
            }
            
            // Handle one-time fees (location surcharges)
            const oneTimeFees = {};
            
            Object.entries(selectedFeatures).forEach(([featureName, price]) => {
                if (featureName === 'pickup_point_surcharge' || featureName === 'drop_off_point_surcharge') {
                    if (price > 0) {
                        oneTimeFees[featureName] = price;
                    }
                }
            });
            
            // Display one-time fees
            if (Object.keys(oneTimeFees).length > 0) {
                Object.entries(oneTimeFees).forEach(([featureName, price]) => {
                    const featureItem = document.createElement('div');
                    
                    let displayName = '';
                    if (featureName === 'pickup_point_surcharge') {
                        displayName = 'Pick-up Fee';
                    } else if (featureName === 'drop_off_point_surcharge') {
                        displayName = 'Drop-off Fee';
                    }
                    
                    featureItem.innerHTML = `<b>${displayName}:</b> SGD ${formatNumberWithCommas(price)}`;
                    breakdownList.appendChild(featureItem);
                });
            }
            
            breakdownContainer.style.display = 'block';
            
            // Update price summary
            updatePriceSummary(selectedFeatures, duration, rentalCost, basePrice, effectiveDailyRate, discountApplied, totalDiscount, dailyDiscountAmount, dailyAddonCost);
        }
        
        // Update price summary display
        function updatePriceSummary(selectedFeatures, duration, rentalCost, basePrice, effectiveDailyRate, discountApplied, totalDiscount, dailyDiscountAmount, dailyAddonCost) {
            const summaryContainer = document.getElementById('oom-price-summary');
            if (!summaryContainer) return;
            
            if (duration && (duration.totalDays > 0 || duration.additionalHours > 0)) {
                // Update rental cost
                const rentalCostElement = document.getElementById('oom-summary-rental-cost');
                if (rentalCostElement) {
                    rentalCostElement.textContent = formatNumberWithCommas(rentalCost);
                }
                
                // Update days
                const daysElement = document.getElementById('oom-summary-days');
                if (daysElement) {
                    daysElement.textContent = duration.totalDays || 0;
                }
                
                // Update daily rate
                const dailyRateElement = document.getElementById('oom-summary-daily-rate');
                if (dailyRateElement) {
                    dailyRateElement.textContent = formatNumberWithCommas(effectiveDailyRate);
                }
                
                // Update discount section - only show if there's a discount
                const discountSection = document.getElementById('oom-summary-discount-section');
                const discountElement = document.getElementById('oom-summary-discount');
                if (discountSection && discountElement) {
                    if (discountApplied && totalDiscount > 0) {
                        discountElement.textContent = formatNumberWithCommas(totalDiscount);
                        discountSection.style.display = 'inline';
                    } else {
                        discountSection.style.display = 'none';
                    }
                }
                
                // Update hours section - only show if there are additional hours
                const hoursSection = document.getElementById('oom-summary-hours-section');
                const hoursElement = document.getElementById('oom-summary-hours');
                const hourlyRateElement = document.getElementById('oom-summary-hourly-rate');
                if (hoursSection && hoursElement && hourlyRateElement) {
                    if (duration.additionalHours > 0) {
                        hoursElement.textContent = duration.additionalHours;
                        
                        let hourlyRate = 0;
                        if (duration.additionalHours >= 1 && duration.additionalHours <= 4) {
                            hourlyRate = basePrice / 5;
                        } else if (duration.additionalHours >= 5) {
                            hourlyRate = effectiveDailyRate;
                        }
                        hourlyRateElement.textContent = formatNumberWithCommas(hourlyRate);
                        
                        hoursSection.style.display = 'inline';
                    } else {
                        hoursSection.style.display = 'none';
                    }
                }
                
                // Update pickup fee section - only show if there's a pickup fee
                const pickupSection = document.getElementById('oom-summary-pickup-section');
                const pickupFeeElement = document.getElementById('oom-summary-pickup-fee');
                if (pickupSection && pickupFeeElement) {
                    const pickupFee = selectedFeatures['pickup_point_surcharge'] || 0;
                    if (pickupFee > 0) {
                        pickupFeeElement.textContent = formatNumberWithCommas(pickupFee);
                        pickupSection.style.display = 'inline';
                    } else {
                        pickupSection.style.display = 'none';
                    }
                }
                
                // Update dropoff fee section - only show if there's a dropoff fee
                const dropoffSection = document.getElementById('oom-summary-dropoff-section');
                const dropoffFeeElement = document.getElementById('oom-summary-dropoff-fee');
                if (dropoffSection && dropoffFeeElement) {
                    const dropoffFee = selectedFeatures['drop_off_point_surcharge'] || 0;
                    if (dropoffFee > 0) {
                        dropoffFeeElement.textContent = formatNumberWithCommas(dropoffFee);
                        dropoffSection.style.display = 'inline';
                    } else {
                        dropoffSection.style.display = 'none';
                    }
                }
                
                summaryContainer.style.display = 'block';
            } else {
                summaryContainer.style.display = 'none';
            }
        }

        // Add event listeners to all add-on radio buttons
        document.querySelectorAll('.feature-radio').forEach(radio => {
            radio.addEventListener('change', updatePricing);
        });
        
        // Add calendar functionality for date inputs and icons
        function initializeCalendarFunctionality() {
            // Handle calendar icon clicks for date fields
            const dateIcons = document.querySelectorAll('.oom-date-icon');
            dateIcons.forEach(icon => {
                // Remove existing listeners to prevent duplicates
                icon.removeEventListener('click', handleDateIconClick);
                icon.addEventListener('click', handleDateIconClick);
                
                // Add cursor pointer for better UX
                icon.style.cursor = 'pointer';
            });
            
            // Handle time icon clicks for time select fields
            const timeIcons = document.querySelectorAll('.oom-time-icon');
            timeIcons.forEach(icon => {
                // Remove existing listeners to prevent duplicates
                icon.removeEventListener('click', handleTimeIconClick);
                icon.addEventListener('click', handleTimeIconClick);
                
                // Add cursor pointer for better UX
                icon.style.cursor = 'pointer';
            });
        }
        
        function handleDateIconClick(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Find the associated date input
            const dateInput = this.parentElement.querySelector('input[type="text"]');
            
            if (dateInput && !dateInput.disabled) {
                // Add visual feedback
                this.style.transform = 'translateY(-50%) scale(0.95)';
                setTimeout(() => {
                    this.style.transform = 'translateY(-50%) scale(1.05)';
                    setTimeout(() => {
                        this.style.transform = 'translateY(-50%)';
                    }, 150);
                }, 100);
                
                // Trigger jQuery datepicker
                $(dateInput).datepicker('show');
            }
        }
        
        function handleTimeIconClick(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Find the associated time select
            const timeSelect = this.parentElement.querySelector('select');
            
            if (timeSelect && !timeSelect.disabled) {
                // Add visual feedback
                this.style.transform = 'translateY(-50%) scale(0.95)';
                setTimeout(() => {
                    this.style.transform = 'translateY(-50%) scale(1.05)';
                    setTimeout(() => {
                        this.style.transform = 'translateY(-50%)';
                    }, 150);
                }, 100);
                
                // Focus the select first
                timeSelect.focus();
                
                // Try multiple methods to open the dropdown
                try {
                    // Method 1: Direct click
                    timeSelect.click();
                    
                    // Method 2: Dispatch mousedown event (works better in some browsers)
                    setTimeout(() => {
                        const mouseDownEvent = new MouseEvent('mousedown', {
                            bubbles: true,
                            cancelable: true,
                            view: window
                        });
                        timeSelect.dispatchEvent(mouseDownEvent);
                    }, 50);
                    
                    // Method 3: Dispatch keydown event for space/enter (fallback)
                    setTimeout(() => {
                        const keyDownEvent = new KeyboardEvent('keydown', {
                            key: ' ',
                            code: 'Space',
                            bubbles: true,
                            cancelable: true
                        });
                        timeSelect.dispatchEvent(keyDownEvent);
                    }, 100);
                    
                    // Method 4: Try to simulate arrow key press (works in some mobile browsers)
                    setTimeout(() => {
                        const arrowDownEvent = new KeyboardEvent('keydown', {
                            key: 'ArrowDown',
                            code: 'ArrowDown',
                            bubbles: true,
                            cancelable: true
                        });
                        timeSelect.dispatchEvent(arrowDownEvent);
                    }, 150);
                    
                } catch (error) {
                    console.log('Error opening time select:', error);
                    // Fallback: just focus and let user interact
                    timeSelect.focus();
                }
                
                // For better UX, also trigger the change event to update any dependent fields
                setTimeout(() => {
                    if (timeSelect.value) {
                        const changeEvent = new Event('change', { bubbles: true });
                        timeSelect.dispatchEvent(changeEvent);
                    }
                }, 200);
            }
        }
        
        // Initialize calendar functionality when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeCalendarFunctionality);
        } else {
            initializeCalendarFunctionality();
        }
        
        // Re-initialize calendar functionality when new content is added (for dynamic content)
        const calendarObserver = new MutationObserver(function(mutations) {
            let shouldReinitialize = false;
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) {
                            // Check if new text inputs, time selects, or icons were added
                            if (node.querySelector && (
                                node.querySelector('input[type="text"]') || 
                                node.querySelector('select') ||
                                node.querySelector('.oom-date-icon') ||
                                node.querySelector('.oom-time-icon') ||
                                node.matches('input[type="text"]') ||
                                node.matches('select') ||
                                node.matches('.oom-date-icon') ||
                                node.matches('.oom-time-icon')
                            )) {
                                shouldReinitialize = true;
                            }
                        }
                    });
                }
            });
            
            if (shouldReinitialize) {
                setTimeout(initializeCalendarFunctionality, 100);
            }
        });
        
        // Start observing for new calendar elements
        calendarObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Also re-initialize when the page becomes visible (for better compatibility)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                setTimeout(initializeCalendarFunctionality, 100);
            }
        });
        
        // Add event listeners to update Elementor form fields when rental form changes
        $('#oom-single-rental-from-date').on('change', updateElementorFormFields);
        document.getElementById('oom-single-rental-from-time').addEventListener('change', updateElementorFormFields);
        $('#oom-single-rental-to-date').on('change', updateElementorFormFields);
        document.getElementById('oom-single-rental-to-time').addEventListener('change', updateElementorFormFields);
        pickupLoc.addEventListener('change', updateElementorFormFields);
        dropoffLoc.addEventListener('change', updateElementorFormFields);
        pickupOther.addEventListener('input', updateElementorFormFields);
        dropoffOther.addEventListener('input', updateElementorFormFields);

        // Initialize pricing on page load (only if pricing element exists)
        if (document.querySelector('.oom-single-rental-pricing .amount')) {
            updatePricing();
        }
        
        // Initialize time validation on page load
        validateTimeSelection();
        
        // Set up mutation observer to watch for form changes
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    // Check if any forms were added
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1 && (node.classList.contains('oom-elementor-form') || node.classList.contains('elementor-form'))) {
                            console.log('Form detected, updating fields');
                            setTimeout(updateElementorFormFields, 100);
                        }
                    });
                }
            });
        });
        
        // Start observing
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Force update fields after a short delay to ensure forms are loaded
        setTimeout(function() {
            updateElementorFormFields();
            console.log('Initial field update completed');
        }, 500);
        
        // Add form submission listener to ensure fields are updated
        document.addEventListener('submit', function(e) {
            // Check if this is an Elementor form submission
            if (e.target.classList.contains('oom-elementor-form') ||
                e.target.classList.contains('elementor-form') || 
                e.target.hasAttribute('data-elementor-type') ||
                e.target.querySelector('.elementor-form') ||
                e.target.querySelector('.oom-elementor-form')) {
                // Update all fields before submission
                updateElementorFormFields();
                console.log('Form submitted - fields updated for form:', e.target);
            }
        });
        
        // Function to update Elementor form hidden fields
        function updateElementorFormFields() {
            const fromDate = $('#oom-single-rental-from-date').val();
            const fromTime = document.getElementById('oom-single-rental-from-time').value;
            const toDate = $('#oom-single-rental-to-date').val();
            const toTime = document.getElementById('oom-single-rental-to-time').value;
            
            // Update hidden fields with the Elementor field naming convention
            updateHiddenField('field_c_rental_from_date', fromDate);
            updateHiddenField('field_c_rental_to_date', toDate);
            updateHiddenField('field_c_single_rental_from_time', fromTime);
            updateHiddenField('field_c_single_rental_to_time', toTime);
            
            // Alternative field names removed to prevent duplicates
            
            // Update location fields
            const pickupLocation = getLocText(pickupLoc, pickupOther);
            const dropoffLocation = getLocText(dropoffLoc, dropoffOther);
            updateHiddenField('field_c_single_rental_pickup_location', pickupLocation);
            updateHiddenField('field_c_single_rental_drop_off_location', dropoffLocation);
            
            // Update add-ons with prices (separate daily add-ons from one-time fees)
            const dailyAddons = [];
            const oneTimeFees = [];
            
            Object.entries(selectedFeatures).forEach(([name, price]) => {
                let displayName = name.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                
                if (name === 'pickup_point_surcharge') {
                    displayName = 'Pickup Point Surcharge (Other Location)';
                    oneTimeFees.push(displayName + ' (+SGD ' + price.toFixed(2) + ')');
                } else if (name === 'drop_off_point_surcharge') {
                    displayName = 'Drop-Off Point Surcharge (Other Location)';
                    oneTimeFees.push(displayName + ' (+SGD ' + price.toFixed(2) + ')');
                } else {
                    // Daily add-ons are included in the daily rate
                    dailyAddons.push(displayName + ' (included in daily rate: +SGD ' + price.toFixed(2) + '/day)');
                }
            });
            
            const featuresList = [];
            if (dailyAddons.length > 0) {
                featuresList.push('Daily Add-ons: ' + dailyAddons.join(', '));
            }
            if (oneTimeFees.length > 0) {
                featuresList.push('One-time Fees: ' + oneTimeFees.join(', '));
            }
            
            console.log('Selected add-ons:', selectedFeatures);
            console.log('Add-ons list:', featuresList.join('; '));
            updateHiddenField('field_c_selected_addons', featuresList.join('; '));
            
            // Update duration
            const durationText = document.getElementById('oom-duration-text').textContent;
            updateHiddenField('field_c_rental_duration', durationText);
            
            // Update price
            const priceElement = document.querySelector('.oom-single-rental-pricing .amount');
            const totalPrice = priceElement ? priceElement.textContent.trim() : '';
            updateHiddenField('field_c_total_price', totalPrice);
            
            // Update discount info
            const pricingContainer = document.querySelector('.oom-single-rental-pricing');
            const minimumDaysForDiscount = parseInt(pricingContainer.dataset.minimumDaysForDiscount || '0');
            const dailyDiscountAmount = parseFloat(pricingContainer.dataset.dailyDiscountAmount || '0');
            const discountApplied = minimumDaysForDiscount > 0 && dailyDiscountAmount > 0 && 
                                   window.rentalDuration && window.rentalDuration.totalDays >= minimumDaysForDiscount;
            updateHiddenField('field_c_discount_applied', discountApplied ? 'Yes' : 'No');
            
            // Update summary display
            updateRentalSummary();
        }
        
        // Helper function to update hidden fields
        function updateHiddenField(fieldName, value) {
            let field = document.querySelector(`input[name="${fieldName}"]`);
            console.log(`Looking for field: ${fieldName}, found:`, field);
            
            if (!field) {
                // Create hidden field if it doesn't exist
                field = document.createElement('input');
                field.type = 'hidden';
                field.name = fieldName;
                field.id = fieldName;
                
                // Try to find the form and append the field
                let elementorForm = document.querySelector('.oom-elementor-form');
                if (!elementorForm) {
                    elementorForm = document.querySelector('.elementor-form');
                }
                if (!elementorForm) {
                    elementorForm = document.querySelector('form[data-elementor-type="form"]');
                }
                if (!elementorForm) {
                    elementorForm = document.querySelector('form');
                }
                
                if (elementorForm) {
                    elementorForm.appendChild(field);
                    console.log(`Added field ${fieldName} to form:`, elementorForm);
                } else {
                    // Fallback: append to body
                    document.body.appendChild(field);
                    console.log(`Added field ${fieldName} to body (no form found)`);
                }
                console.log(`Created hidden field: ${fieldName}`);
            }
            
            field.value = value;
            console.log(`Updated ${fieldName}: ${value}`);
        }
        
        // Function to update rental summary display
        function updateRentalSummary() {
            const summaryContainer = document.getElementById('oom-rental-summary');
            if (!summaryContainer) return;
            
            const fromDate = $('#oom-single-rental-from-date').val();
            const fromTime = document.getElementById('oom-single-rental-from-time').value;
            const toDate = $('#oom-single-rental-to-date').val();
            const toTime = document.getElementById('oom-single-rental-to-time').value;
            
            if (fromDate && fromTime && toDate && toTime) {
                // Format dates for display
                const formatDate = (dateStr) => {
                    // Convert dd-mm-yyyy to yyyy-mm-dd for Date constructor
                    const dateParts = dateStr.split('-');
                    if (dateParts.length === 3) {
                        const date = new Date(dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0]);
                        return date.toLocaleDateString('en-US', { 
                            weekday: 'long', 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric' 
                        });
                    }
                    return dateStr; // Return original if parsing fails
                };
                
                const formatTime = (timeStr) => {
                    const [hours, minutes] = timeStr.split(':');
                    const hour = parseInt(hours);
                    const ampm = hour >= 12 ? 'PM' : 'AM';
                    const displayHour = hour % 12 || 12;
                    return `${displayHour}:${minutes} ${ampm}`;
                };
                
                // Update summary fields
                document.getElementById('oom-summary-period').textContent = 
                    `${formatDate(fromDate)} at ${formatTime(fromTime)} to ${formatDate(toDate)} at ${formatTime(toTime)}`;
                
                document.getElementById('oom-summary-duration').textContent = 
                    document.getElementById('oom-duration-text').textContent;
                
                document.getElementById('oom-summary-pickup').textContent = 
                    getLocText(pickupLoc, pickupOther) || 'Not selected';
                
                document.getElementById('oom-summary-dropoff').textContent = 
                    getLocText(dropoffLoc, dropoffOther) || 'Not selected';
                
                // Update add-ons (separate daily add-ons from one-time fees)
                const featuresContainer = document.getElementById('oom-summary-features');
                const featuresList = document.getElementById('oom-summary-features-list');
                if (Object.keys(window.selectedFeatures).length > 0) {
                    featuresList.innerHTML = '';
                    
                    // Separate daily add-ons from one-time fees
                    const dailyAddons = [];
                    const oneTimeFees = [];
                    
                    Object.entries(window.selectedFeatures).forEach(([featureName, price]) => {
                        let displayName = featureName.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        
                        if (featureName === 'pickup_point_surcharge') {
                            displayName = 'Pickup Point Surcharge (Other Location)';
                            oneTimeFees.push({ name: displayName, price: price });
                        } else if (featureName === 'drop_off_point_surcharge') {
                            displayName = 'Drop-Off Point Surcharge (Other Location)';
                            oneTimeFees.push({ name: displayName, price: price });
                        } else {
                            // Daily add-ons are included in the daily rate
                            dailyAddons.push({ name: displayName, price: price });
                        }
                    });
                    
                    // Display daily add-ons
                    if (dailyAddons.length > 0) {
                        const dailyHeader = document.createElement('li');
                        dailyHeader.innerHTML = '<strong>Daily Add-ons (included in daily rate):</strong>';
                        dailyHeader.style.marginTop = '10px';
                        featuresList.appendChild(dailyHeader);
                        
                        dailyAddons.forEach(addon => {
                            const li = document.createElement('li');
                            li.textContent = `${addon.name} (+SGD ${addon.price.toFixed(2)}/day)`;
                            li.style.marginLeft = '20px';
                            featuresList.appendChild(li);
                        });
                    }
                    
                    // Display one-time fees
                    if (oneTimeFees.length > 0) {
                        const oneTimeHeader = document.createElement('li');
                        oneTimeHeader.innerHTML = '<strong>One-time Fees:</strong>';
                        oneTimeHeader.style.marginTop = '10px';
                        featuresList.appendChild(oneTimeHeader);
                        
                        oneTimeFees.forEach(fee => {
                            const li = document.createElement('li');
                            li.textContent = `${fee.name} (+SGD ${fee.price.toFixed(2)})`;
                            li.style.marginLeft = '20px';
                            featuresList.appendChild(li);
                        });
                    }
                    
                    featuresContainer.style.display = 'block';
                } else {
                    featuresContainer.style.display = 'none';
                }
                
                // Update price
                const priceElement = document.querySelector('.oom-single-rental-pricing .amount');
                document.getElementById('oom-summary-price').textContent = 
                    priceElement ? `SGD ${priceElement.textContent.trim()}` : 'Not calculated';
                
                // Update discount
                const discountContainer = document.getElementById('oom-summary-discount');
                const pricingContainer = document.querySelector('.oom-single-rental-pricing');
                const minimumDaysForDiscount = parseInt(pricingContainer.dataset.minimumDaysForDiscount || '0');
                const dailyDiscountAmount = parseFloat(pricingContainer.dataset.dailyDiscountAmount || '0');
                
                if (minimumDaysForDiscount > 0 && dailyDiscountAmount > 0 && 
                    window.rentalDuration && window.rentalDuration.totalDays >= minimumDaysForDiscount) {
                    document.getElementById('oom-summary-discount-text').textContent = 
                        `Yes - SGD ${dailyDiscountAmount} per day for ${window.rentalDuration.totalDays} days`;
                    discountContainer.style.display = 'block';
                } else {
                    discountContainer.style.display = 'none';
                }
                
                summaryContainer.style.display = 'block';
            } else {
                summaryContainer.style.display = 'none';
            }
        }
        
        // Add-ons validation for the Book Now button
        function validateAddOns() {
            console.log('validateAddOns function called');
            const addOnFeatures = document.querySelectorAll('.oom-single-rental-add-on-features-item');
            
            // Find the error element in the Elementor form that contains the Book Now button
            let errorElement = null;
            const bookNowButton = document.querySelector('#oom-custom-form-btn');
            console.log('Book Now button found:', bookNowButton);
            
            if (bookNowButton) {
                const elementorForm = bookNowButton.closest('.elementor-form');
                console.log('Elementor form found:', elementorForm);
                if (elementorForm) {
                    errorElement = elementorForm.querySelector('.error-addons');
                    console.log('Error element found:', errorElement);
                }
            }
            
            console.log('Found add-on features:', addOnFeatures.length);
            console.log('Found error element:', errorElement);
            
            // Clear any existing error message first
            if (errorElement) {
                errorElement.style.display = 'none';
                errorElement.textContent = '';
            }
            
            if (addOnFeatures.length === 0) {
                // No add-ons to validate
                console.log('No add-ons to validate, returning true');
                return true;
            }
            
            let allSelected = true;
            let unselectedFeatures = [];
            
            addOnFeatures.forEach(feature => {
                const radioButtons = feature.querySelectorAll('input[type="radio"]');
                const featureLabel = feature.querySelector('label').textContent.replace('*', '').trim();
                
                console.log('Checking feature:', featureLabel, 'with', radioButtons.length, 'radio buttons');
                
                // Check if any radio button is selected for this feature
                let featureSelected = false;
                radioButtons.forEach(radio => {
                    if (radio.checked) {
                        featureSelected = true;
                        console.log('Radio button selected for:', featureLabel);
                    }
                });
                
                if (!featureSelected) {
                    allSelected = false;
                    unselectedFeatures.push(featureLabel);
                    console.log('Feature not selected:', featureLabel);
                }
            });
            
            if (!allSelected) {
                // Show error message
                if (errorElement) {
                    errorElement.textContent = `Please select the add-ons: ${unselectedFeatures.join(', ')}`;
                    errorElement.style.display = 'block';
                }
                console.log('Validation failed. Unselected features:', unselectedFeatures);
                return false;
            } else {
                // Hide error message
                if (errorElement) {
                    errorElement.style.display = 'none';
                }
                console.log('Validation passed. All features selected.');
                return true;
            }
        }
        
        // Make the validation function globally available for Elementor forms
        window.validateOomAddOns = validateAddOns;
        console.log('Global validation function assigned:', typeof window.validateOomAddOns);
        
        // Only apply validation if we're on a page with the single rental form
        if (document.querySelector('.oom-single-rental-form')) {
            // Add validation to Elementor forms that contain the Book Now button
            document.addEventListener('DOMContentLoaded', function() {
                // Find all Elementor forms and add validation
                const elementorForms = document.querySelectorAll('.elementor-form');
                elementorForms.forEach(form => {
                    const submitButton = form.querySelector('#oom-custom-form-btn');
                    if (submitButton) {
                        // This form has the Book Now button, add validation
                        form.addEventListener('submit', function(e) {
                            if (!validateAddOns()) {
                                e.preventDefault();
                                e.stopPropagation();
                                return false;
                            }
                        });
                    }
                });
            });
        }
    });
    </script>
    <?php
    return ob_get_clean();
});

// Single Rental Pricing Shortcode
add_shortcode('oom_single_rental_pricing', function () {
    if (!is_singular('rental')) return '';

    $post_id = get_the_ID();
    $rental_price = get_post_meta($post_id, 'rental_price', true);
    $rental_sale_price = get_post_meta($post_id, 'rental_sale_price', true);
    
    // Use sale price if it exists, otherwise use regular price
    $display_price = !empty($rental_sale_price) ? $rental_sale_price : $rental_price;
    
    // Format price to 2 decimal places
    $formatted_price = number_format((float)$display_price, 2);
    
    // Get discount information
    $minimum_days_for_discount = get_post_meta($post_id, 'minimum_days_for_discount', true);
    $daily_discount_amount = get_post_meta($post_id, 'daily_discount_amount', true);
    
    ob_start();
    ?>
    <div class="oom-single-rental-pricing" 
         data-base-price="<?php echo esc_attr($formatted_price); ?>" 
         data-daily-rate="<?php echo esc_attr($formatted_price); ?>"
         data-minimum-days-for-discount="<?php echo esc_attr($minimum_days_for_discount); ?>"
         data-daily-discount-amount="<?php echo esc_attr($daily_discount_amount); ?>">
        <span class="prefix">SGD</span><span class="amount"> <?php echo esc_html($formatted_price); ?> </span>
    </div>
    <?php
    return ob_get_clean();
});

/**
 * Custom Shortcodes
 * @author oom_cn 
 * @since  1.5.0
 */

// Helper function to get blockout dates and convert them to jQuery datepicker format
function oom_get_blockout_dates_for_datepicker() {
    $blockout_dates = get_option('oom_blockout_dates', '');
    if (empty($blockout_dates)) {
        return array();
    }
    
    $dates_array = array_map('trim', explode(',', $blockout_dates));
    $blockout_dates_array = array();
    
    foreach ($dates_array as $date) {
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
            // Keep the dd-mm-yyyy format for the new datepicker
            $blockout_dates_array[] = $date;
        }
    }
    
    return $blockout_dates_array;
}