/**
 * File: js/frontend-script.js
 * Handles frontend YMCA location search
 */

jQuery(document).ready(function($) {
    
    var searchCache = {};
    var currentNonce = findAY.nonce; // Start with embedded nonce
    
    // Function to get a fresh nonce
    function getFreshNonce(callback) {
        $.ajax({
            url: findAY.ajax_url,
            type: 'POST',
            data: {
                action: 'get_find_a_y_nonce'
            },
            success: function(response) {
                if (response.success && response.data.nonce) {
                    currentNonce = response.data.nonce;
                    callback(true);
                } else {
                    callback(false);
                }
            },
            error: function() {
                callback(false);
            }
        });
    }
    
    $('#ymca-location-search').on('submit', function(e) {
        e.preventDefault();
        
        var zipCode = $('#ymca-zip-input').val().trim();
        
        if (!zipCode) {
            showError('Please enter a ZIP code');
            return;
        }
        
        if (!isValidZip(zipCode)) {
            showError('Please enter a valid 5-digit ZIP code');
            return;
        }
        
        // Track ZIP code search
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            'event': 'zip_code_search',
            'zip_code': zipCode,
            'page_location': window.location.pathname
        });
        
        hideError();
        
        if (searchCache[zipCode]) {
            displayResults(searchCache[zipCode], zipCode);
            return;
        }
        
        // Get fresh nonce before searching
        getFreshNonce(function(success) {
            if (success) {
                performSearch(zipCode);
            } else {
                showError('Unable to verify security. Please refresh the page.');
            }
        });
    });
    
    $('#ymca-zip-input').on('input', function() {
        var value = $(this).val();
        value = value.replace(/[^0-9-]/g, '');
        $(this).val(value);
    });
    
    function isValidZip(zip) {
        return /^\d{5}(-\d{4})?$/.test(zip);
    }
    
    function performSearch(zipCode) {
        $('.find-a-y-search-form').slideUp();
        $('.find-a-y-results').hide();
        $('.find-a-y-loading').show();
        
        $.ajax({
            url: findAY.ajax_url,
            type: 'POST',
            data: {
                action: 'find_ymca_locations',
                nonce: currentNonce, // Use fresh nonce
                zip_code: zipCode
            },
            success: function(response) {
                $('.find-a-y-loading').hide();
                $('.find-a-y-search-form').slideDown();
                
                if (response.success) {
                    var resultsCount = response.data.length;
                    
                    // Track successful search with results count
                    window.dataLayer.push({
                        'event': 'zip_search_results',
                        'zip_code': zipCode,
                        'results_count': resultsCount
                    });
                    
                    searchCache[zipCode] = response.data;
                    displayResults(response.data, zipCode);
                } else {
                    // Track no results found
                    window.dataLayer.push({
                        'event': 'zip_search_no_results',
                        'zip_code': zipCode
                    });
                    
                    showError(response.data || 'No locations found near that ZIP code. Please try a different ZIP code.');
                }
            },
            error: function(xhr, status, error) {
                $('.find-a-y-loading').hide();
                $('.find-a-y-search-form').slideDown();
                
                // Log details to console for debugging
                console.error('Find A Y Error:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status
                });
                
                var errorMessage = 'An error occurred. Please try again.';
                
                // Provide more specific error messages
                if (xhr.status === 403) {
                    errorMessage = 'Security check failed. Refreshing page...';
                    showError(errorMessage);
                    
                    // Auto-refresh after 2 seconds if nonce failed
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                    return;
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error. Please try again in a moment.';
                } else if (xhr.status === 0) {
                    errorMessage = 'Connection lost. Please check your internet and try again.';
                }
                
                showError(errorMessage);
            }
        });
    }
    
    function displayResults(locations, zipCode) {
        if (!locations || locations.length === 0) {
            showError('No YMCA locations found near ZIP code ' + zipCode);
            return;
        }
        
        var resultsHtml = '';
        
        locations.forEach(function(location) {
            resultsHtml += buildLocationCard(location);
        });
        
        $('#ymca-results-list').html(resultsHtml);
        
        var heading = locations.length + ' YMCA Location' + (locations.length !== 1 ? 's' : '') + ' Near You';
        $('.results-heading').text(heading);
        
        $('.find-a-y-results').slideDown();
        
        $('html, body').animate({
            scrollTop: $('.find-a-y-results').offset().top - 20
        }, 500);
        
        // Track clicks on location websites
        $('.location-link.primary').on('click', function() {
            var locationName = $(this).data('location-name');
            var locationZip = $(this).data('location-zip');
            
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'event': 'location_website_click',
                'location_name': locationName,
                'location_zip': locationZip,
                'search_zip': zipCode
            });
        });
    }
    
    function buildLocationCard(location) {
        var card = '<div class="ymca-location-card">';
        
        card += '<div class="location-header">';
        card += '<h3 class="location-name">' + escapeHtml(location.y_name) + '</h3>';
        
        if (location.distance !== null && location.distance !== undefined) {
            card += '<span class="location-distance">' + location.distance + ' miles</span>';
        }
        
        card += '</div>';
        
        card += '<div class="location-address">';
        card += escapeHtml(location.formatted_address);
        card += '</div>';
        
        card += '<div class="location-actions">';
        
        if (location.website) {
            card += '<a href="' + escapeHtml(location.website) + '" target="_blank" class="location-link primary" ';
            card += 'data-location-name="' + escapeHtml(location.y_name) + '" ';
            card += 'data-location-zip="' + escapeHtml(location.zip_code) + '">';
            card += 'Visit Website';
            card += '</a> ';
        }
        
        var mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' + 
                      encodeURIComponent(location.formatted_address);
        card += '<a href="' + mapsUrl + '" target="_blank" class="location-link">';
        card += 'Get Directions';
        card += '</a>';
        
        card += '</div>';
        
        card += '</div>';
        
        return card;
    }
    
    function showError(message) {
        $('.search-error').text(message).slideDown();
    }
    
    function hideError() {
        $('.search-error').slideUp();
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }
});