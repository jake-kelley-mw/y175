/**
 * File: js/frontend-script.js
 * Handles frontend YMCA location search
 */

jQuery(document).ready(function($) {
    
    var searchCache = {};
    
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
        
        hideError();
        
        if (searchCache[zipCode]) {
            displayResults(searchCache[zipCode], zipCode);
            return;
        }
        
        performSearch(zipCode);
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
                nonce: findAY.nonce,
                zip_code: zipCode
            },
            success: function(response) {
                $('.find-a-y-loading').hide();
                $('.find-a-y-search-form').slideDown();
                
                if (response.success) {
                    searchCache[zipCode] = response.data;
                    displayResults(response.data, zipCode);
                } else {
                    showError(response.data || 'No locations found near that ZIP code. Please try a different ZIP code.');
                }
            },
            error: function() {
                $('.find-a-y-loading').hide();
                $('.find-a-y-search-form').slideDown();
                showError('An error occurred. Please try again.');
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
        card += '<strong>Address:</strong><br>';
        card += escapeHtml(location.formatted_address);
        card += '</div>';
        
        card += '<div class="location-actions">';
        
        if (location.website) {
            card += '<a href="' + escapeHtml(location.website) + '" target="_blank" class="location-link primary">';
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