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

/* Disable Divi's FitVids script */
function disable_divi_fitvids() {
    wp_dequeue_script('fitvids');
    wp_deregister_script('fitvids');
}
add_action('wp_enqueue_scripts', 'disable_divi_fitvids', 100);

/* Alternative method - disable via Divi filter */
add_filter('et_builder_enable_jquery_body', '__return_false');

/* Force Lyte to use lazy loading on ALL devices including mobile */
function force_lyte_on_mobile() {
    add_filter('lyte_mobile_override', '__return_true');
    add_filter('lyte_opt_mobile', function() { return 'lyte'; });
    
    /* Override wp_is_mobile for Lyte only */
    if (isset($_GET['doing_lyte']) || (function_exists('lyte_parse') && in_the_loop())) {
        add_filter('wp_is_mobile', '__return_false', 999);
    }
}
add_action('init', 'force_lyte_on_mobile');