/**
 * YMCA 175 Timeline Sticky Fallback
 * If CSS sticky isn't working due to Divi structure, this JS will handle it
 */

jQuery(document).ready(function($) {
    // Only run if timeline exists
    if (!$('.y175-timeline-wrapper').length) return;
    
    // Debug: Check if sticky is actually working
    function checkStickySupport() {
        var testEl = document.createElement('div');
        testEl.style.position = 'sticky';
        testEl.style.position = '-webkit-sticky';
        return testEl.style.position.indexOf('sticky') !== -1;
    }
    
    console.log('Sticky CSS support:', checkStickySupport());
    
    // Manual sticky implementation
    function initStickyTimeline() {
        var headerHeight = 70; // Desktop header height
        if ($(window).width() <= 768) {
            headerHeight = 50; // Mobile header height
        }
        
        $('.y175-timeline-entry').each(function() {
            var $entry = $(this);
            var $sticky = $entry.find('.y175-sticky-content');
            var $left = $entry.find('.y175-timeline-left');
            
            if (!$sticky.length) return;
            
            // Store original position
            $sticky.data('original-top', $sticky.offset().top);
            
            // Calculate boundaries
            var entryTop = $entry.offset().top;
            var entryBottom = entryTop + $entry.outerHeight();
            var stickyHeight = $sticky.outerHeight();
            
            $(window).on('scroll resize', function() {
                var scrollTop = $(window).scrollTop();
                var triggerPoint = entryTop - headerHeight - 10;
                var stopPoint = entryBottom - stickyHeight - headerHeight - 10;
                
                if (scrollTop > triggerPoint && scrollTop < stopPoint) {
                    // Make it sticky
                    $sticky.css({
                        'position': 'fixed',
                        'top': headerHeight + 10 + 'px',
                        'width': $left.width() + 'px',
                        'z-index': 10
                    });
                } else if (scrollTop >= stopPoint) {
                    // Stop at bottom of entry
                    $sticky.css({
                        'position': 'absolute',
                        'top': 'auto',
                        'bottom': '0',
                        'width': $left.width() + 'px'
                    });
                    $left.css('position', 'relative');
                } else {
                    // Reset to normal
                    $sticky.css({
                        'position': '',
                        'top': '',
                        'bottom': '',
                        'width': ''
                    });
                    $left.css('position', '');
                }
            });
        });
    }
    
    // Initialize
    setTimeout(function() {
        initStickyTimeline();
    }, 100);
    
    // Reinitialize on window resize
    var resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            initStickyTimeline();
        }, 250);
    });
});