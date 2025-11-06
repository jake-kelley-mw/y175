/**
 * File: js/frontend-script.js
 * Handles frontend YMCA location search
 */

jQuery(document).ready(function($) {
    
    $('#ymca-location-search').on('submit', function(e) {
        e.preventDefault();
        
        var zipCode = $('#ymca-zip-input').val().trim();
        
        // Validate ZIP code
        if (!zipCode || zipCode.length < 5) {
            $('.search-error').text('Please enter a valid ZIP code').show();
            return;
        }
        
        // Track ZIP code search
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            'event': 'zip_code_search',
            'zip_code': zipCode,
            'page_location': window.location.pathname
        });
        
        // Show loading state
        $('.find-a-y-loading').show();
        $('.find-a-y-search-form').hide();
        $('.find-a-y-results').hide();
        $('.search-error').hide();
        
        // Make AJAX request
        $.ajax({
            url: findAY.ajax_url,
            type: 'POST',
            data: {
                action: 'find_ymca_locations',
                nonce: findAY.nonce,
                zip_code: zipCode
            },
            success: function(response) {
                $('.find-a-y-loading').hide();
                
                if (response.success) {
                    var resultsCount = response.data.length;
                    
                    // Track successful search with results count
                    window.dataLayer.push({
                        'event': 'zip_search_results',
                        'zip_code': zipCode,
                        'results_count': resultsCount
                    });
                    
                    displayResults(response.data, zipCode);
                } else {
                    // Track no results found
                    window.dataLayer.push({
                        'event': 'zip_search_no_results',
                        'zip_code': zipCode
                    });
                    
                    $('.search-error').text(response.data).show();
                    $('.find-a-y-search-form').show();
                }
            },
            error: function() {
                $('.find-a-y-loading').hide();
                $('.search-error').text('An error occurred. Please try again.').show();
                $('.find-a-y-search-form').show();
            }
        });
    });
    
    // Function to display results
    function displayResults(locations, zipCode) {
        var resultsHTML = '';
        
        $.each(locations, function(index, location) {
            resultsHTML += '<div class="ymca-location-card">';
            resultsHTML += '<div class="location-header">';
            resultsHTML += '<h3 class="location-name">' + location.y_name + '</h3>';
            if (location.distance) {
                resultsHTML += '<span class="location-distance">' + location.distance + ' miles</span>';
            }
            resultsHTML += '</div>';
            
            resultsHTML += '<div class="location-address">';
            resultsHTML += location.address + '<br>';
            if (location.address2) {
                resultsHTML += location.address2 + '<br>';
            }
            resultsHTML += location.city + ', ' + location.state + ' ' + location.zip_code;
            resultsHTML += '</div>';
            
            if (location.website) {
                resultsHTML += '<div class="location-actions">';
                resultsHTML += '<a href="' + location.website + '" class="location-link primary" target="_blank" ';
                resultsHTML += 'data-location-name="' + location.y_name + '" ';
                resultsHTML += 'data-location-zip="' + location.zip_code + '">';
                resultsHTML += 'Visit Website</a>';
                resultsHTML += '</div>';
            }
            
            resultsHTML += '</div>';
        });
        
        $('#ymca-results-list').html(resultsHTML);
        $('.results-heading').text('YMCA Locations near ' + zipCode);
        $('.find-a-y-results').show();
        
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
});