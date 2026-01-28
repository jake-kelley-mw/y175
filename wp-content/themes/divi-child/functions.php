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

/**
 * Auto-bust browser cache for stylesheets when files are modified
 */
function auto_version_css($src, $handle) {
    if (strpos($src, get_stylesheet_directory_uri()) !== false) {
        $file_path = str_replace(
            get_stylesheet_directory_uri(),
            get_stylesheet_directory(),
            preg_replace('/\?.*/', '', $src)
        );
        if (file_exists($file_path)) {
            $src = add_query_arg('ver', filemtime($file_path), $src);
        }
    }
    return $src;
}
add_filter('style_loader_src', 'auto_version_css', 10, 2);


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






// ---------- GOOGLE TRACKING IMPLEMENTATION ---------- 

function ymca_175_add_gtm_head() {
    ?>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-K5JH3B4L');</script>
    <!-- End Google Tag Manager -->
    <?php
}
add_action('wp_head', 'ymca_175_add_gtm_head', 10);

function ymca_175_add_gtm_body() {
    ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K5JH3B4L"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
}
add_action('wp_body_open', 'ymca_175_add_gtm_body', 10);

/**
 * Pushes events to dataLayer for GTM to process
 */
function ymca_175_event_tracking() {
    ?>
    <script>
    (function() {
        'use strict';
        
        // Helper function to push events to dataLayer
        function trackEvent(eventName, eventData) {
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'event': eventName,
                ...eventData
            });
        }
        
        function initTracking() {
            
            // ============================================
            // VIDEO TRACKING
            // ============================================
            
            // Track launch video and embedded videos
            var embeddedVideos = document.querySelectorAll('iframe[src*="gettyimages.com"], iframe[src*="youtube.com"], iframe[src*="vimeo.com"]');
            embeddedVideos.forEach(function(iframe) {
                var videoType = 'Unknown';
                if (iframe.src.includes('gettyimages')) videoType = 'Getty Images';
                if (iframe.src.includes('youtube')) videoType = 'YouTube';
                if (iframe.src.includes('vimeo')) videoType = 'Vimeo';
                
                trackEvent('video_loaded', {
                    'video_type': videoType,
                    'video_title': iframe.title || 'Embedded Video',
                    'page_location': window.location.pathname
                });
            });
            
            // Track native HTML5 videos
            var videos = document.querySelectorAll('video');
            videos.forEach(function(video) {
                var hasPlayed = false;
                var videoTitle = video.title || video.getAttribute('data-title') || 'Video';
                
                video.addEventListener('play', function() {
                    if (!hasPlayed) {
                        hasPlayed = true;
                        trackEvent('video_play', {
                            'video_title': videoTitle,
                            'page_location': window.location.pathname
                        });
                    }
                });
                
                video.addEventListener('ended', function() {
                    trackEvent('video_complete', {
                        'video_title': videoTitle,
                        'page_location': window.location.pathname
                    });
                });
                
                // Track viewing milestones
                var milestones = {25: false, 50: false, 75: false};
                video.addEventListener('timeupdate', function() {
                    var percentComplete = (video.currentTime / video.duration) * 100;
                    
                    if (percentComplete >= 25 && !milestones[25]) {
                        milestones[25] = true;
                        trackEvent('video_progress', {
                            'video_title': videoTitle,
                            'progress_percent': 25
                        });
                    }
                    if (percentComplete >= 50 && !milestones[50]) {
                        milestones[50] = true;
                        trackEvent('video_progress', {
                            'video_title': videoTitle,
                            'progress_percent': 50
                        });
                    }
                    if (percentComplete >= 75 && !milestones[75]) {
                        milestones[75] = true;
                        trackEvent('video_progress', {
                            'video_title': videoTitle,
                            'progress_percent': 75
                        });
                    }
                });
            });
            
            
            // ============================================
            // TIMELINE TRACKING
            // ============================================
            
            // Track timeline items coming into view (scroll-based)
            var timelineItems = document.querySelectorAll(
                '.timeline-item, .et_pb_timeline_item, [class*="timeline-entry"], .timeline-card'
            );
            
            if (timelineItems.length > 0 && 'IntersectionObserver' in window) {
                var timelineObserver = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            var item = entry.target;
                            var title = item.querySelector('h1, h2, h3, h4, .title, [class*="title"]');
                            var titleText = title ? title.textContent.trim() : 'Timeline Item';
                            var era = item.getAttribute('data-era') || item.getAttribute('data-year') || 'Unknown';
                            
                            trackEvent('timeline_item_viewed', {
                                'timeline_item': titleText,
                                'timeline_era': era
                            });
                            
                            // Stop observing this item once tracked
                            timelineObserver.unobserve(item);
                        }
                    });
                }, {
                    threshold: 0.5
                });
                
                timelineItems.forEach(function(item) {
                    timelineObserver.observe(item);
                });
            }
            
            // Track century anchor link clicks (#1800s, #1900s, #2000s)
            var centuryLinks = document.querySelectorAll('a[href="#1800s"], a[href="#1900s"], a[href="#2000s"]');
            centuryLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    var century = link.getAttribute('href').replace('#', '');
                    
                    trackEvent('timeline_century_navigation', {
                        'century': century,
                        'link_text': link.textContent.trim()
                    });
                });
            });
            
            
            // ============================================
            // FORM SUBMISSION TRACKING (Contact Form 7)
            // ============================================
            
            document.addEventListener('wpcf7mailsent', function(event) {
                trackEvent('form_submission', {
                    'form_type': 'Contact Form 7',
                    'form_id': event.detail.contactFormId,
                    'form_name': 'Story Submission'
                });
            });
            
            
            // ============================================
            // CTA BUTTON TRACKING
            // ============================================
            
            // Track "Explore 175 Years" / Timeline CTA
            var timelineCTA = document.querySelectorAll('a[href*="/175-years"]');
            timelineCTA.forEach(function(link) {
                link.addEventListener('click', function() {
                    trackEvent('cta_click', {
                        'cta_text': link.textContent.trim(),
                        'cta_destination': link.href,
                        'cta_type': 'Explore Timeline',
                        'page_location': window.location.pathname
                    });
                });
            });
            
            // Track "Share Your Story" CTA
            var storyCTA = document.querySelectorAll('a[href*="/share"]');
            storyCTA.forEach(function(link) {
                link.addEventListener('click', function() {
                    trackEvent('cta_click', {
                        'cta_text': link.textContent.trim(),
                        'cta_destination': link.href,
                        'cta_type': 'Share Story',
                        'page_location': window.location.pathname
                    });
                });
            });
            
            // Track "In the News" CTA
            var newsCTA = document.querySelectorAll('a[href*="/news"]');
            newsCTA.forEach(function(link) {
                link.addEventListener('click', function() {
                    trackEvent('cta_click', {
                        'cta_text': link.textContent.trim(),
                        'cta_destination': link.href,
                        'cta_type': 'News',
                        'page_location': window.location.pathname
                    });
                });
            });
            
            // Track link to main YMCA site (logo or footer links)
            var ymcaLinks = document.querySelectorAll('a[href*="ymca.org"]');
            ymcaLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    trackEvent('external_link_click', {
                        'link_text': link.textContent.trim() || 'YMCA Logo',
                        'link_url': link.href,
                        'link_destination': 'YMCA.org'
                    });
                });
            });
            
            
            // ============================================
            // SOCIAL MEDIA PROFILE LINK TRACKING
            // ============================================
            
            var socialLinks = document.querySelectorAll(
                'a[href*="facebook.com"], a[href*="instagram.com"], a[href*="twitter.com"], ' +
                'a[href*="linkedin.com"], a[href*="youtube.com"], a[href*="tiktok.com"]'
            );
            
            socialLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    var platform = 'Unknown';
                    var href = link.href.toLowerCase();
                    
                    if (href.includes('facebook.com')) platform = 'Facebook';
                    else if (href.includes('instagram.com')) platform = 'Instagram';
                    else if (href.includes('twitter.com') || href.includes('x.com')) platform = 'Twitter/X';
                    else if (href.includes('linkedin.com')) platform = 'LinkedIn';
                    else if (href.includes('youtube.com')) platform = 'YouTube';
                    else if (href.includes('tiktok.com')) platform = 'TikTok';
                    
                    trackEvent('social_profile_click', {
                        'social_platform': platform,
                        'profile_url': link.href,
                        'page_location': window.location.pathname
                    });
                });
            });
            
            
            // ============================================
            // PARTNERSHIP/SPONSOR TRACKING
            // ============================================
            
            var sponsorLinks = document.querySelectorAll('[class*="partner"], [class*="sponsor"]');
            sponsorLinks.forEach(function(link) {
                if (link.tagName === 'A') {
                    link.addEventListener('click', function() {
                        var sponsorName = link.getAttribute('data-sponsor') || 
                                        link.querySelector('img')?.alt || 
                                        'Unknown Sponsor';
                        
                        trackEvent('sponsor_click', {
                            'sponsor_name': sponsorName,
                            'sponsor_url': link.href
                        });
                    });
                }
            });
            
        }
        
        // Initialize tracking when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initTracking);
        } else {
            initTracking();
        }
        
    })();
    </script>
    <?php
}
add_action('wp_footer', 'ymca_175_event_tracking', 20);


/**
 * Media Coverage Grid Shortcode
 * Displays ACF repeater content in a responsive grid
 * Usage: [media_coverage]
 */

function ymca_media_coverage_shortcode($atts) {
    $atts = shortcode_atts(array(
        'columns' => 3,
        'limit' => -1,
        'order' => 'desc',
    ), $atts);

    // Check if ACF is active
    if (!function_exists('get_field')) {
        return '<!-- ACF not active -->';
    }

    $media_items = get_field('add_media_coverage');

    if (empty($media_items)) {
        return '';
    }

    // Apply limit if set
    if ($atts['limit'] > 0) {
        $media_items = array_slice($media_items, 0, (int)$atts['limit']);
    }

    // Sort by date
    usort($media_items, function($a, $b) use ($atts) {
        $date_a = strtotime($a['mc_article_date'] ?? '');
        $date_b = strtotime($b['mc_article_date'] ?? '');
        return ($atts['order'] === 'asc') ? $date_a - $date_b : $date_b - $date_a;
    });

    ob_start();
    ?>
    <div class="mc-grid mc-cols-<?php echo esc_attr($atts['columns']); ?>">
        <?php foreach ($media_items as $item) :
            $headline = $item['mc_article_headline'] ?? '';
            $date = $item['mc_article_date'] ?? '';
            $preview = $item['mc_article_preview'] ?? '';
            $pub_title = $item['mc_publication_title'] ?? '';
            $pub_logo = $item['mc_publication_logo'] ?? '';
            $link = $item['mc_article_link'] ?? '#';

            // Get optimized image URL (medium size for performance)
            $logo_url = '';
            $logo_alt = '';
            if ($pub_logo) {
                if (is_array($pub_logo)) {
                    $logo_url = $pub_logo['sizes']['medium'] ?? $pub_logo['url'];
                    $logo_alt = $pub_logo['alt'] ?? $pub_title;
                } else {
                    $logo_url = wp_get_attachment_image_url($pub_logo, 'medium');
                    $logo_alt = get_post_meta($pub_logo, '_wp_attachment_image_alt', true) ?: $pub_title;
                }
            }
        ?>
        <div class="mc-card">
            <?php if ($logo_url) : ?>
            <div class="mc-card-logo">
                <img src="<?php echo esc_url($logo_url); ?>" 
                     alt="<?php echo esc_attr($logo_alt); ?>" 
                     loading="lazy" 
                     decoding="async">
            </div>
            <?php elseif ($pub_title) : ?>
            <div class="mc-card-logo mc-card-logo-text">
                <span><?php echo esc_html($pub_title); ?></span>
            </div>
            <?php endif; ?>

            <div class="mc-card-content">
                <?php if ($date) : ?>
                <time class="mc-card-date" datetime="<?php echo esc_attr(date('Y-m-d', strtotime($date))); ?>">
                    <?php echo esc_html($date); ?>
                </time>
                <?php endif; ?>

                <?php if ($headline) : ?>
                <h3 class="mc-card-headline"><?php echo esc_html($headline); ?></h3>
                <?php endif; ?>

                <?php if ($preview) : ?>
                <p class="mc-card-preview"><?php echo esc_html($preview); ?></p>
                <?php endif; ?>

                <?php if ($pub_title) : ?>
                <span class="mc-card-source"><?php echo esc_html($pub_title); ?></span>
                <?php endif; ?>
            </div>

            <a href="<?php echo esc_url($link); ?>" class="mc-card-cta" target="_blank" rel="noopener noreferrer">
                Read Article
                <svg class="mc-cta-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('media_coverage', 'ymca_media_coverage_shortcode');

/**
 * Preferred Vendors Grid Shortcode
 * Displays ACF repeater content in a responsive grid
 * Usage: [preferred_vendors]
 */

function ymca_preferred_vendors_shortcode($atts) {
    $atts = shortcode_atts(array(
        'columns' => 3,
        'limit' => -1,
    ), $atts);

    if (!function_exists('get_field')) {
        return '<!-- ACF not active -->';
    }

    $vendor_items = get_field('pv_add_preferred_vendor');

    if (empty($vendor_items)) {
        return '';
    }

    if ($atts['limit'] > 0) {
        $vendor_items = array_slice($vendor_items, 0, (int)$atts['limit']);
    }

    ob_start();
    ?>
    <div class="pv-grid pv-cols-<?php echo esc_attr($atts['columns']); ?>">
        <?php foreach ($vendor_items as $item) :
            $image = $item['example_product_image'] ?? '';
            $button_label = $item['vendor_website_link']['pv_button_label'] ?? 'Visit Vendor';
            $button_link = $item['vendor_website_link']['pv_button_link'] ?? '#';

            $image_url = '';
            $image_alt = '';
            if ($image) {
                if (is_array($image)) {
                    $image_url = $image['sizes']['large'] ?? $image['url'];
                    $image_alt = $image['alt'] ?? 'Vendor Product';
                } else {
                    $image_url = wp_get_attachment_image_url($image, 'large');
                    $image_alt = get_post_meta($image, '_wp_attachment_image_alt', true) ?: 'Vendor Product';
                }
            }
        ?>
        <div class="pv-card">
            <?php if ($image_url) : ?>
            <div class="pv-card-image">
                <img src="<?php echo esc_url($image_url); ?>" 
                     alt="<?php echo esc_attr($image_alt); ?>" 
                     loading="lazy" 
                     decoding="async">
            </div>
            <?php endif; ?>

            <a href="<?php echo esc_url($button_link); ?>" 
               class="pv-card-cta" 
               target="_blank" 
               rel="noopener noreferrer">
                <?php echo esc_html($button_label); ?>
                <svg class="pv-cta-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('preferred_vendors', 'ymca_preferred_vendors_shortcode');