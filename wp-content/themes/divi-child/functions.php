<?php
function dt_enqueue_styles() {
    $parenthandle = 'divi-style'; 
    $theme = wp_get_theme();
    wp_enqueue_style( $parenthandle, get_template_directory_uri() . '/style.css', 
        array(), // if the parent theme code has a dependency, copy it to here
        $theme->parent()->get('Version')
    );
    wp_enqueue_style( 'child-style', get_stylesheet_uri(),
        array( $parenthandle ),
        $theme->get('Version') 
    );
}
add_action( 'wp_enqueue_scripts', 'dt_enqueue_styles' );

// Enqueue hamburger menu script
function custom_hamburger_menu_script() {
    wp_enqueue_script(
        'custom-hamburger-menu',
        get_stylesheet_directory_uri() . '/js/hamburger-menu.js',
        array(),
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'custom_hamburger_menu_script');

// Add custom hamburger menu to header
function custom_hamburger_menu_html() {
    $logo_url = home_url('/wp-content/uploads/2025/09/y-175-horizontal_4Web.webp');
    $home_url = home_url('/');
    ?>
    <div class="custom-hamburger-menu">
        <button class="hamburger-toggle" aria-label="Toggle menu" aria-expanded="false">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
        
        <nav class="fullscreen-menu">
            <div class="fullscreen-menu-logo">
                <a href="<?php echo esc_url($home_url); ?>">
                    <img src="<?php echo esc_url($logo_url); ?>" alt="YMCA 175 Logo">
                </a>
            </div>
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary-menu',
                'container' => false,
                'menu_class' => 'fullscreen-menu-list',
                'fallback_cb' => false
            ));
            ?>
        </nav>
    </div>
    <?php
}
add_action('wp_body_open', 'custom_hamburger_menu_html');

// Add header logo
function custom_header_logo() {
    $logo_url = home_url('/wp-content/uploads/2025/09/y-175-horizontal_K_4Web.webp');
    $home_url = home_url('/');
    ?>
    <div class="custom-header-logo">
        <a href="<?php echo esc_url($home_url); ?>">
            <img src="<?php echo esc_url($logo_url); ?>" alt="YMCA 175 Logo">
        </a>
    </div>
    <?php
}
add_action('wp_body_open', 'custom_header_logo');



// ---------- Video Optimizations ---------- 

/* Disable Divi's FitVids script */
function disable_divi_fitvids() {
    wp_dequeue_script('fitvids');
    wp_deregister_script('fitvids');
}
add_action('wp_enqueue_scripts', 'disable_divi_fitvids', 100);

/* Alternative method - disable via Divi filter */
add_filter('et_builder_enable_jquery_body', '__return_false');

/* Force WP YouTube Lyte to work on mobile devices */
add_filter('lyte_do_mobile', '__return_true');




// ---------- CACHING IMPLEMENTATION ---------- 

// Set cache headers for video files on Pantheon
add_filter('wp_headers', 'set_video_cache_headers', 10, 2);
function set_video_cache_headers($headers, $wp) {
    $request_uri = $_SERVER['REQUEST_URI'];
    
    // Check if this is a video file request
    if (preg_match('/\.(mp4|webm)$/i', $request_uri)) {
        $headers['Cache-Control'] = 'public, max-age=2592000, immutable';
        $headers['Expires'] = gmdate('D, d M Y H:i:s', time() + 2592000) . ' GMT';
        
        // Remove conflicting headers
        unset($headers['Pragma']);
    }
    
    return $headers;
}

function extend_acf_datepicker_year_range() {
    ?>
    <script type="text/javascript">
    (function($) {
        acf.add_filter('date_picker_args', function(args, field) {
            args.yearRange = '-200:+10';
            args.changeYear = true;
            return args;
        });
    })(jQuery);
    </script>
    <?php
}
add_action('acf/input/admin_footer', 'extend_acf_datepicker_year_range');


// ---------- YMCA 175 Timeline ---------- 

// Include timeline function
require_once get_stylesheet_directory() . '/inc/timeline-function.php';

// Enqueue timeline styles and scripts
function y175_timeline_assets() {
    // Always load the CSS
    wp_enqueue_style(
        'y175-timeline-styles',
        get_stylesheet_directory_uri() . '/css/timeline-styles.css',
        array(),
        '1.0.1'
    );
    
    // Add the sticky fix JavaScript
    wp_enqueue_script(
        'y175-timeline-sticky',
        get_stylesheet_directory_uri() . '/js/timeline-sticky.js',
        array('jquery'),
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'y175_timeline_assets');
