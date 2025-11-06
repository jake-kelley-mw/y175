<?php
/**
 * YMCA 175 Timeline
 * File location: /code/wp-content/themes/divi-child/includes/timeline-function.php
 */

// Register timeline shortcode
add_shortcode('y175_timeline', 'y175_render_timeline');

function y175_render_timeline($atts = array()) {
    // Parse shortcode attributes
    $atts = shortcode_atts(array(
        'featured' => false,
        'class' => '',      // New: custom CSS class
        'century' => '',    // New: filter by century (e.g., "1800")
    ), $atts);
    
    // Query timeline posts ordered by date
    $args = array(
        'post_type' => 'timeline-story',
        'posts_per_page' => -1,
        'orderby' => 'meta_value',
        'meta_key' => 'y175-timeline-date',
        'order' => 'ASC',
        'meta_type' => 'DATE'
    );
    
    // Build meta query array
    $meta_query = array();
    
    // If featured is true, only show featured items
    if ($atts['featured']) {
        $meta_query[] = array(
            'key' => 'feature_this_timeline_entry_on_the_homepage',
            'value' => '1',
            'compare' => '='
        );
    }
    
    // Add meta query if we have any conditions
    if (!empty($meta_query)) {
        $args['meta_query'] = $meta_query;
    }
    
    // Get posts with featured filter applied
    $timeline_posts = get_posts($args);
    
    // If century is specified, filter the results by century
    if (!empty($atts['century']) && !empty($timeline_posts)) {
        $filtered_posts = array();
        foreach ($timeline_posts as $post) {
            $date = get_field('y175-timeline-date', $post->ID);
            if ($date) {
                // Convert "November 6, 2025" to year
                $year = date('Y', strtotime($date));
                $post_century = substr($year, 0, 2); // Get first 2 digits (18, 19, 20, etc.)
                
                // Check if this post belongs to the requested century
                if ($post_century == substr($atts['century'], 0, 2)) {
                    $filtered_posts[] = $post;
                }
            }
        }
        
        if (empty($filtered_posts)) {
            return '<p>No timeline entries found for the ' . $atts['century'] . 's.</p>';
        }
        
        $timeline_posts = $filtered_posts;
    }
    
    if (empty($timeline_posts)) {
        return '<p>No timeline entries found.</p>';
    }
    
    // Build wrapper classes
    $wrapper_classes = 'y175-timeline-wrapper';
    if (!empty($atts['class'])) {
        $wrapper_classes .= ' ' . esc_attr($atts['class']);
    }
    
    ob_start();
    ?>
    <div class="<?php echo $wrapper_classes; ?>">
        <div class="y175-timeline-container">
            <?php 
            foreach ($timeline_posts as $post) : 
                setup_postdata($post);
                
                // Get ACF fields
                $date = get_field('y175-timeline-date', $post->ID);
                $copy = get_field('y175-timeline-copy', $post->ID);
                $image = get_field('y175-timeline-image', $post->ID);
                $design = get_field('select_entry_design', $post->ID);
                
                // Extract year from date
                $year = date('Y', strtotime($date));
                
                // Set entry class based on design type
                $entry_class = 'y175-timeline-entry';
                if ($design === 'Small Entry') {
                    $entry_class .= ' y175-entry-small';
                } elseif ($design === 'Featured Entry') {
                    $entry_class .= ' y175-entry-featured';
                } else {
                    $entry_class .= ' y175-entry-regular';
                }
                ?>
                
                <div class="<?php echo esc_attr($entry_class); ?>">
                    <div class="y175-timeline-grid">
                        <!-- Left Column - Sticky Date/Title (Title hidden on mobile) -->
                        <div class="y175-timeline-left">
                            <div class="y175-sticky-content">
                                <div class="y175-date"><?php echo esc_html($year); ?></div>
                                <h3 class="y175-title y175-title-desktop"><?php echo esc_html(get_the_title($post->ID)); ?></h3>
                            </div>
                        </div>
                        
                        <!-- Center Timeline Line -->
                        <div class="y175-timeline-center">
                            <div class="y175-timeline-line"></div>
                            <div class="y175-timeline-dot"></div>
                        </div>
                        
                        <!-- Right Column - Content -->
                        <div class="y175-timeline-right">
                            <div class="y175-content-wrapper">
                                <!-- Mobile title - shows only on mobile -->
                                <h3 class="y175-title y175-title-mobile"><?php echo esc_html(get_the_title($post->ID)); ?></h3>
                                
                                <?php 
                                // For Regular entries, image and text are side by side inside wrapper
                                if ($design === 'Regular Entry') : ?>
                                    <?php 
                                    $image = get_field('y175-timeline-image', $post->ID);
                                    if ($image) : 
                                        // Handle different ACF return formats
                                        $image_id = null;
                                        
                                        if (is_array($image)) {
                                            // Image array - get ID
                                            $image_id = $image['ID'];
                                        } elseif (is_numeric($image)) {
                                            // Direct ID
                                            $image_id = $image;
                                        } elseif (is_string($image)) {
                                            // URL - try to get ID from URL
                                            $image_id = attachment_url_to_postid($image);
                                        }
                                        
                                        if ($image_id) : ?>
                                            <div class="y175-images">
                                                <?php echo wp_get_attachment_image($image_id, 'large', false, array(
                                                    'class' => 'y175-timeline-image',
                                                    'loading' => 'lazy'
                                                )); ?>
                                            </div>
                                        <?php endif;
                                    endif; ?>
                                    
                                    <?php if ($copy) : ?>
                                        <div class="y175-copy"><?php echo wp_kses_post($copy); ?></div>
                                    <?php endif; ?>
                                    
                                <?php else : 
                                    // For Featured and Small entries, standard stacked layout ?>
                                    
                                    <?php 
                                    $image = get_field('y175-timeline-image', $post->ID);
                                    if ($image) : 
                                        // Handle different ACF return formats
                                        $image_id = null;
                                        
                                        if (is_array($image)) {
                                            // Image array - get ID
                                            $image_id = $image['ID'];
                                        } elseif (is_numeric($image)) {
                                            // Direct ID
                                            $image_id = $image;
                                        } elseif (is_string($image)) {
                                            // URL - try to get ID from URL
                                            $image_id = attachment_url_to_postid($image);
                                        }
                                        
                                        if ($image_id) : ?>
                                            <div class="y175-images">
                                                <?php echo wp_get_attachment_image($image_id, 'large', false, array(
                                                    'class' => 'y175-timeline-image',
                                                    'loading' => 'lazy'
                                                )); ?>
                                            </div>
                                        <?php endif;
                                    endif; ?>
                                    
                                    <?php if ($copy) : ?>
                                        <div class="y175-copy"><?php echo wp_kses_post($copy); ?></div>
                                    <?php endif; ?>
                                    
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php endforeach; 
            wp_reset_postdata();
            ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}