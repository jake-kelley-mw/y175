<?php
/**
 * File: includes/import-page.php
 * Admin page for importing/updating YMCA locations
 */

if (!defined('ABSPATH')) exit;
?>

<div class="wrap find-a-y-import-page">
    <h1>Import/Update YMCA Locations</h1>
    
    <div class="find-a-y-instructions">
        <h2>How It Works</h2>
        <ol>
            <li><strong>Upload File:</strong> Select your CSV or Excel file containing YMCA location data</li>
            <li><strong>Preview Changes:</strong> Review what will be added, updated, or removed</li>
            <li><strong>Confirm Import:</strong> Apply the changes to your database</li>
            <li><strong>Automatic Geocoding:</strong> New and updated addresses will be geocoded automatically</li>
        </ol>
        
        <div class="notice notice-info">
            <p><strong>Smart Update System:</strong> This system compares the new file with existing data using Y Name + ZIP Code as the unique identifier. It will:</p>
            <ul>
                <li>Add new locations that don't exist</li>
                <li>Update locations where address, city, state, or website changed</li>
                <li>Remove locations not in the new file</li>
                <li>Skip unchanged locations to improve performance</li>
                <li>Only re-geocode locations with address changes</li>
            </ul>
        </div>
    </div>
    
    <div class="find-a-y-api-settings">
        <h2>Google Maps API Key</h2>
        <p>Required for geocoding addresses to latitude/longitude coordinates</p>
        <form id="find-a-y-api-form">
            <?php
            $api_key = get_option('find_a_y_google_api_key', '');
            ?>
            <input 
                type="text" 
                id="google-api-key" 
                name="google_api_key" 
                value="<?php echo esc_attr($api_key); ?>" 
                class="regular-text"
                placeholder="Enter your Google Maps API key"
            />
            <button type="submit" class="button button-secondary">Save API Key</button>
            
            <?php if (empty($api_key)): ?>
                <p class="description">
                    <strong>No API key set.</strong> Without an API key, locations cannot be geocoded. 
                    <a href="https://developers.google.com/maps/documentation/geocoding/get-api-key" target="_blank">Get an API key</a>
                </p>
            <?php else: ?>
                <p class="description" style="color: green;">API key configured</p>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="find-a-y-upload-section">
        <h2>Upload Location File</h2>
        
        <form id="find-a-y-upload-form" enctype="multipart/form-data">
            <input 
                type="file" 
                name="import_file" 
                id="import-file" 
                accept=".csv,.xlsx,.xls"
                required
            />
            <button type="submit" class="button button-primary">Preview Import</button>
        </form>
        
        <div id="upload-progress" style="display: none;">
            <p>Processing file, please wait...</p>
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
        </div>
    </div>
    
    <div id="preview-results" style="display: none;">
        <h2>Import Preview</h2>
        
        <div class="find-a-y-preview-summary">
            <div class="summary-box new">
                <h3 id="new-count">0</h3>
                <p>New Locations</p>
            </div>
            <div class="summary-box updated">
                <h3 id="updated-count">0</h3>
                <p>Updated Locations</p>
            </div>
            <div class="summary-box unchanged">
                <h3 id="unchanged-count">0</h3>
                <p>Unchanged</p>
            </div>
            <div class="summary-box removed">
                <h3 id="removed-count">0</h3>
                <p>To Be Removed</p>
            </div>
        </div>
        
        <div class="preview-details">
            <h3>New Locations</h3>
            <div id="new-locations-list" class="locations-list"></div>
            
            <h3>Updated Locations</h3>
            <div id="updated-locations-list" class="locations-list"></div>
            
            <h3>Locations To Be Removed</h3>
            <div id="removed-locations-list" class="locations-list"></div>
        </div>
        
        <div class="import-actions">
            <button id="confirm-import" class="button button-primary button-large">
                Confirm and Process Import
            </button>
            <button id="cancel-import" class="button button-secondary button-large">
                Cancel
            </button>
        </div>
    </div>
    
    <div id="import-complete" style="display: none;">
        <div class="notice notice-success">
            <h2>Import Complete!</h2>
            <div id="import-results"></div>
        </div>
        
        <button id="new-import" class="button button-primary">Start New Import</button>
    </div>
</div>

<style>
.find-a-y-import-page {
    max-width: 1200px;
}

.find-a-y-instructions {
    background: white;
    padding: 20px;
    margin: 20px 0;
    border-left: 4px solid #2271b1;
}

.find-a-y-instructions ol {
    margin-left: 20px;
}

.find-a-y-api-settings {
    background: white;
    padding: 20px;
    margin: 20px 0;
}

.find-a-y-upload-section {
    background: white;
    padding: 20px;
    margin: 20px 0;
}

#import-file {
    margin-right: 10px;
}

.progress-bar {
    width: 100%;
    height: 30px;
    background: #f0f0f0;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 10px;
}

.progress-fill {
    height: 100%;
    background: #2271b1;
    width: 0%;
    transition: width 0.3s;
    animation: progress-animation 1.5s infinite;
}

@keyframes progress-animation {
    0% { width: 0%; }
    50% { width: 70%; }
    100% { width: 100%; }
}

.find-a-y-preview-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.summary-box {
    background: white;
    padding: 20px;
    text-align: center;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.summary-box.new {
    border-left: 4px solid #00a32a;
}

.summary-box.updated {
    border-left: 4px solid #2271b1;
}

.summary-box.unchanged {
    border-left: 4px solid #dba617;
}

.summary-box.removed {
    border-left: 4px solid #d63638;
}

.summary-box h3 {
    font-size: 36px;
    margin: 0;
}

.summary-box p {
    margin: 10px 0 0 0;
    color: #666;
}

.preview-details {
    background: white;
    padding: 20px;
    margin: 20px 0;
}

.preview-details h3 {
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 10px;
    margin-bottom: 15px;
}

.locations-list {
    max-height: 400px;
    overflow-y: auto;
    margin-bottom: 30px;
}

.location-item {
    padding: 15px;
    border: 1px solid #e0e0e0;
    margin-bottom: 10px;
    border-radius: 4px;
}

.location-item strong {
    display: block;
    font-size: 16px;
    margin-bottom: 5px;
}

.location-item .address {
    color: #666;
    font-size: 14px;
}

.changes-list {
    margin-top: 10px;
    padding-left: 20px;
    font-size: 13px;
    color: #2271b1;
}

.import-actions {
    text-align: center;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 4px;
}

.import-actions button {
    margin: 0 10px;
}

#import-results {
    font-size: 16px;
    padding: 10px 0;
}
</style>