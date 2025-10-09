/**
 * File: js/admin-script.js
 * Handles admin interface for importing YMCA locations
 */

jQuery(document).ready(function($) {
    
    $('#find-a-y-api-form').on('submit', function(e) {
        e.preventDefault();
        
        var apiKey = $('#google-api-key').val();
        
        $.ajax({
            url: findAYAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'save_google_api_key',
                nonce: findAYAdmin.nonce,
                api_key: apiKey
            },
            success: function(response) {
                alert('API key saved successfully');
            },
            error: function() {
                alert('Failed to save API key');
            }
        });
    });
    
    $('#find-a-y-upload-form').on('submit', function(e) {
        e.preventDefault();
        
        var fileInput = $('#import-file')[0];
        if (!fileInput.files.length) {
            alert('Please select a file');
            return;
        }
        
        var formData = new FormData();
        formData.append('action', 'preview_ymca_import');
        formData.append('nonce', findAYAdmin.nonce);
        formData.append('import_file', fileInput.files[0]);
        
        $('#upload-progress').show();
        $('#find-a-y-upload-form button').prop('disabled', true);
        
        $.ajax({
            url: findAYAdmin.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#upload-progress').hide();
                $('#find-a-y-upload-form button').prop('disabled', false);
                
                if (response.success) {
                    displayPreview(response.data);
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                $('#upload-progress').hide();
                $('#find-a-y-upload-form button').prop('disabled', false);
                alert('Upload failed. Please try again.');
            }
        });
    });
    
    function displayPreview(data) {
        var analysis = data.analysis;
        var counts = analysis.counts;
        
        $('#new-count').text(counts.new);
        $('#updated-count').text(counts.updated);
        $('#unchanged-count').text(counts.unchanged);
        $('#removed-count').text(counts.removed);
        
        displayLocationList('#new-locations-list', analysis.new, 'new');
        displayUpdatedList('#updated-locations-list', analysis.updated);
        displayLocationList('#removed-locations-list', analysis.removed, 'removed');
        
        $('#preview-results').slideDown();
        
        $('html, body').animate({
            scrollTop: $('#preview-results').offset().top - 50
        }, 500);
    }
    
    function displayLocationList(selector, locations, type) {
        var $list = $(selector);
        $list.empty();
        
        if (locations.length === 0) {
            $list.html('<p style="color: #666; font-style: italic;">None</p>');
            return;
        }
        
        var displayLimit = 20;
        var showLocations = locations.slice(0, displayLimit);
        
        showLocations.forEach(function(location) {
            var html = '<div class="location-item">';
            html += '<strong>' + escapeHtml(location.y_name) + '</strong>';
            html += '<div class="address">';
            html += escapeHtml(location.address) + ', ';
            html += escapeHtml(location.city) + ', ';
            html += escapeHtml(location.state) + ' ';
            html += escapeHtml(location.zip_code);
            html += '</div>';
            html += '</div>';
            
            $list.append(html);
        });
        
        if (locations.length > displayLimit) {
            $list.append('<p style="color: #666; font-style: italic;">... and ' + (locations.length - displayLimit) + ' more</p>');
        }
    }
    
    function displayUpdatedList(selector, updates) {
        var $list = $(selector);
        $list.empty();
        
        if (updates.length === 0) {
            $list.html('<p style="color: #666; font-style: italic;">None</p>');
            return;
        }
        
        var displayLimit = 20;
        var showUpdates = updates.slice(0, displayLimit);
        
        showUpdates.forEach(function(update) {
            var location = update.location;
            var changes = update.changes;
            
            var html = '<div class="location-item">';
            html += '<strong>' + escapeHtml(location.y_name) + '</strong>';
            html += '<div class="address">';
            html += escapeHtml(location.city) + ', ' + escapeHtml(location.state);
            html += '</div>';
            
            html += '<div class="changes-list">';
            html += '<strong>Changes:</strong><ul style="margin: 5px 0;">';
            
            for (var field in changes) {
                var change = changes[field];
                var fieldName = field.charAt(0).toUpperCase() + field.slice(1).replace('_', ' ');
                html += '<li>' + fieldName + ': ';
                html += '<span style="color: #d63638; text-decoration: line-through;">' + escapeHtml(change.old || 'empty') + '</span> ';
                html += '→ <span style="color: #00a32a;">' + escapeHtml(change.new || 'empty') + '</span>';
                html += '</li>';
            }
            
            html += '</ul></div>';
            html += '</div>';
            
            $list.append(html);
        });
        
        if (updates.length > displayLimit) {
            $list.append('<p style="color: #666; font-style: italic;">... and ' + (updates.length - displayLimit) + ' more</p>');
        }
    }
    
    $('#confirm-import').on('click', function() {
        if (!confirm('Are you sure you want to process this import? This will modify your database.')) {
            return;
        }
        
        $(this).prop('disabled', true).text('Processing...');
        $('#cancel-import').prop('disabled', true);
        
        $.ajax({
            url: findAYAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'process_ymca_import',
                nonce: findAYAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    displayImportComplete(response.data);
                } else {
                    alert('Import failed: ' + response.data);
                    $('#confirm-import').prop('disabled', false).text('Confirm and Process Import');
                    $('#cancel-import').prop('disabled', false);
                }
            },
            error: function() {
                alert('Import failed. Please try again.');
                $('#confirm-import').prop('disabled', false).text('Confirm and Process Import');
                $('#cancel-import').prop('disabled', false);
            }
        });
    });
    
    function displayImportComplete(data) {
        $('#preview-results').slideUp();
        
        var results = data.results;
        var html = '<p><strong>' + data.summary + '</strong></p>';
        html += '<ul style="font-size: 14px; margin-top: 10px;">';
        html += '<li>' + results.inserted + ' locations added</li>';
        html += '<li>' + results.updated + ' locations updated</li>';
        html += '<li>' + results.deleted + ' locations removed</li>';
        html += '</ul>';
        
        if (results.errors && results.errors.length > 0) {
            html += '<p style="color: #d63638; margin-top: 10px;"><strong>Errors:</strong></p>';
            html += '<ul style="font-size: 13px;">';
            results.errors.forEach(function(error) {
                html += '<li>' + escapeHtml(error) + '</li>';
            });
            html += '</ul>';
        }
        
        $('#import-results').html(html);
        $('#import-complete').slideDown();
        
        $('html, body').animate({
            scrollTop: $('#import-complete').offset().top - 50
        }, 500);
    }
    
    $('#cancel-import').on('click', function() {
        if (confirm('Are you sure you want to cancel? The uploaded file will be discarded.')) {
            location.reload();
        }
    });
    
    $('#new-import').on('click', function() {
        location.reload();
    });
    
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