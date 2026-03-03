<?php
/**
 * Feed Display Handler
 * 
 * Renders the Instagram feed as a responsive grid.
 * Outputs minimal, semantic HTML for performance.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class YMCA_IG_Feed_Display {

    /**
     * Plugin settings
     */
    private $settings;

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = get_option( 'ymca_ig_feed_settings', array() );
    }

    /**
     * Render the feed grid
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render( $atts = array() ) {
        // Determine post count
        $count = ! empty( $atts['count'] ) 
            ? intval( $atts['count'] ) 
            : ( isset( $this->settings['post_count'] ) ? intval( $this->settings['post_count'] ) : 12 );

        // Determine column count
        $columns = ! empty( $atts['columns'] ) 
            ? intval( $atts['columns'] ) 
            : ( isset( $this->settings['columns'] ) ? intval( $this->settings['columns'] ) : 4 );

        // Heading params
        $heading = isset( $atts['heading'] ) ? sanitize_text_field( $atts['heading'] ) : '';
        $heading_color = isset( $atts['heading_color'] ) ? sanitize_hex_color( $atts['heading_color'] ) : '#2dc6c9';

        // Get cached posts
        $cache = new YMCA_IG_Feed_Cache();
        $posts = $cache->get_posts( $count );

        // Handle empty state
        if ( empty( $posts ) ) {
            return $this->render_empty_state();
        }

        // Build output with optional header
        $output = '<div class="ymca-ig-feed-wrapper">';

        if ( ! empty( $heading ) ) {
            $output .= $this->render_header( $heading, $heading_color );
        }

        $output .= $this->render_grid( $posts, $columns );
        $output .= '</div>';

        return $output;
    }

    /**
     * Render feed header with Instagram icon
     *
     * @param string $heading Heading text
     * @param string $color Hex color for text and icon
     * @return string HTML
     */
    private function render_header( $heading, $color ) {
        $style = sprintf( 'color: %s;', esc_attr( $color ) );

        $output  = '<div class="ymca-ig-feed-header">';
        $output .= '<div class="ymca-ig-feed-header__inner">';
        $output .= sprintf(
            '<svg class="ymca-ig-feed-header__icon" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="%s" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>',
            esc_attr( $color )
        );
        $output .= sprintf(
            '<h2 class="ymca-ig-feed-header__title" style="%s">%s</h2>',
            esc_attr( $style ),
            esc_html( $heading )
        );
        $output .= '</div>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Render the grid HTML
     * 
     * @param array $posts Array of post data
     * @param int $columns Number of columns
     * @return string HTML
     */
    private function render_grid( $posts, $columns ) {
        $output = sprintf(
            '<div class="ymca-ig-feed" style="--ymca-ig-columns: %d;">',
            esc_attr( $columns )
        );

        foreach ( $posts as $post ) {
            $output .= $this->render_post( $post );
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Render a single post
     * 
     * @param array $post Post data from API
     * @return string HTML
     */
    private function render_post( $post ) {
        $permalink = isset( $post['permalink'] ) ? esc_url( $post['permalink'] ) : '#';
        $media_type = isset( $post['media_type'] ) ? $post['media_type'] : 'IMAGE';
        
        // Get appropriate image URL
        $image_url = $this->get_image_url( $post );
        
        // Get caption for alt text (truncated)
        $caption = isset( $post['caption'] ) ? $post['caption'] : '';
        $alt_text = $this->truncate_caption( $caption, 100 );

        // Determine if this is a video (for overlay icon)
        $is_video = in_array( $media_type, array( 'VIDEO', 'REELS' ), true );
        $is_carousel = $media_type === 'CAROUSEL_ALBUM';

        $output = sprintf(
            '<a href="%s" class="ymca-ig-feed__item" target="_blank" rel="noopener noreferrer">',
            $permalink
        );

        // Image with lazy loading
        $output .= sprintf(
            '<img src="%s" alt="%s" class="ymca-ig-feed__image" loading="lazy" decoding="async">',
            esc_url( $image_url ),
            esc_attr( $alt_text )
        );

        // Overlay for videos/carousels
        if ( $is_video || $is_carousel ) {
            $icon = $is_video ? $this->get_video_icon() : $this->get_carousel_icon();
            $output .= '<span class="ymca-ig-feed__icon">' . $icon . '</span>';
        }

        // Hover overlay with caption preview
        if ( ! empty( $caption ) && $this->show_caption_overlay() ) {
            $output .= sprintf(
                '<span class="ymca-ig-feed__overlay"><span class="ymca-ig-feed__caption">%s</span></span>',
                esc_html( $this->truncate_caption( $caption, 150 ) )
            );
        }

        $output .= '</a>';

        return $output;
    }

    /**
     * Get the appropriate image URL for a post
     * 
     * @param array $post Post data
     * @return string Image URL
     */
    private function get_image_url( $post ) {
        $media_type = isset( $post['media_type'] ) ? $post['media_type'] : 'IMAGE';

        // Videos use thumbnail_url
        if ( in_array( $media_type, array( 'VIDEO', 'REELS' ), true ) ) {
            return isset( $post['thumbnail_url'] ) ? $post['thumbnail_url'] : '';
        }

        // Images and carousels use media_url
        return isset( $post['media_url'] ) ? $post['media_url'] : '';
    }

    /**
     * Truncate caption for display
     * 
     * @param string $caption Full caption
     * @param int $length Max length
     * @return string Truncated caption
     */
    private function truncate_caption( $caption, $length = 100 ) {
        $caption = wp_strip_all_tags( $caption );
        
        if ( strlen( $caption ) <= $length ) {
            return $caption;
        }

        return substr( $caption, 0, $length ) . '...';
    }

    /**
     * Check if caption overlay should be shown
     * 
     * @return bool
     */
    private function show_caption_overlay() {
        return isset( $this->settings['show_captions'] ) 
            ? (bool) $this->settings['show_captions'] 
            : true;
    }

    /**
     * Render empty state message
     * 
     * @return string HTML
     */
    private function render_empty_state() {
        // Check if API is configured
        $api = new YMCA_IG_Feed_API();
        
        if ( ! $api->is_configured() ) {
            if ( current_user_can( 'manage_options' ) ) {
                return '<p class="ymca-ig-feed__empty">' . 
                    __( 'Instagram feed not configured. Please add API credentials in Settings.', 'ymca-instagram-feed' ) . 
                    '</p>';
            }
            return '';
        }

        $hashtags = isset( $this->settings['hashtags'] ) ? $this->settings['hashtags'] : '';
        
        if ( ! empty( $hashtags ) && current_user_can( 'manage_options' ) ) {
            return '<p class="ymca-ig-feed__empty">' . 
                sprintf( 
                    __( 'No posts found with hashtags: %s', 'ymca-instagram-feed' ),
                    esc_html( $hashtags )
                ) . 
                '</p>';
        }

        return '<p class="ymca-ig-feed__empty">' . 
            __( 'No Instagram posts to display.', 'ymca-instagram-feed' ) . 
            '</p>';
    }

    /**
     * SVG icon for video posts
     * 
     * @return string SVG markup
     */
    private function get_video_icon() {
        return '<svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24"><polygon points="5,3 19,12 5,21"></polygon></svg>';
    }

    /**
     * SVG icon for carousel posts
     * 
     * @return string SVG markup
     */
    private function get_carousel_icon() {
        return '<svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24"><rect x="3" y="3" width="14" height="14" rx="2"></rect><rect x="7" y="7" width="14" height="14" rx="2"></rect></svg>';
    }
}