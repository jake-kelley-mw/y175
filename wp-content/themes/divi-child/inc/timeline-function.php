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
    
    // If featured is true, only show featured items
    if ($atts['featured']) {
        $args['meta_query'] = array(
            array(
                'key' => 'feature_this_timeline_entry_on_the_homepage',
                'value' => '1',
                'compare' => '='
            )
        );
    }
    
    $timeline_posts = get_posts($args);
    
    if (empty($timeline_posts)) {
        return '<p>No timeline entries found.</p>';
    }
    
    ob_start();
    ?>
    <div class="y175-timeline-wrapper">
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