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
    ?>
    <div class="custom-hamburger-menu">
        <button class="hamburger-toggle" aria-label="Toggle menu" aria-expanded="false">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
        
        <nav class="fullscreen-menu">
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