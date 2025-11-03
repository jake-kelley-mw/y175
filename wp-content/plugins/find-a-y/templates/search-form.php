<?php
/**
 * File: templates/search-form.php
 * Frontend search form for finding YMCA locations
 */

if (!defined('ABSPATH')) exit;
?>

<div class="find-a-y-search-container">
    <div class="find-a-y-search-form">
        
        <form id="ymca-location-search">
            <div class="search-input-group">
                <input 
                    type="text" 
                    id="ymca-zip-input" 
                    name="zip_code" 
                    placeholder="Enter ZIP code"
                    maxlength="10"
                    required
                    pattern="[0-9]{5}(-[0-9]{4})?"
                />
                <button type="submit" class="find-a-y-submit-btn">
                    Find Locations
                </button>
            </div>
            <div class="search-error" style="display: none;"></div>
        </form>
    </div>
    
    <div class="find-a-y-loading" style="display: none;">
        <div class="loading-spinner"></div>
        <p>Finding locations near you...</p>
    </div>
    
    <div class="find-a-y-results" style="display: none;">
        <h3 class="results-heading"></h3>
        <div id="ymca-results-list"></div>
    </div>
</div>

<style>
.find-a-y-search-container {
    max-width: 800px;
    margin: 30px auto;
    padding: 0 20px;
}

.find-a-y-search-form {
    background: #f8f9fa;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.find-a-y-search-form h2 {
    margin-top: 0;
    color: #333;
    font-size: 28px;
}

.find-a-y-search-form p {
    color: #666;
    margin-bottom: 20px;
}

.search-input-group {
    display: flex;
    gap: 10px;
    margin-bottom: 0px;
}

#ymca-zip-input {
    flex: 1;
    padding: 15px;
    font-size: 16px;
    border: 2px solid #ddd;
    border-radius: 4px;
    transition: border-color 0.3s;
}

#ymca-zip-input:focus {
    outline: none;
    border-color: #0073aa;
}

.find-a-y-submit-btn {
    padding: 15px 30px;
    font-family: 'Grack';
    font-style: normal;
    font-weight: 400 !important;
    font-size: 24px;
    letter-spacing: 0px;
    line-height: 1.2em;
    text-transform: uppercase;
    background: #20bdbe !important;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s;
    white-space: nowrap;
}

.find-a-y-submit-btn:hover {
    background: #006b6b;
}

.find-a-y-submit-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.search-error {
    padding: 12px;
    background: #f8d7da;
    color: #721c24;
    border-radius: 4px;
    border: 1px solid #f5c6cb;
    margin-top: 10px;
}

.find-a-y-loading {
    text-align: center;
    padding: 40px;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #006b6b;
    border-radius: 50%;
    margin: 0 auto 20px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.find-a-y-results {
    margin-top: 30px;
}

.results-heading {
    font-size: 24px;
    margin-bottom: 20px;
    color: #ffffffff;
    text-align: center;
    text-transform: uppercase !important;
    letter-spacing: .5px !important;
    font-weight: 400 !important;
}

.ymca-location-card {
    background: white;
    padding: 25px;
    margin-bottom: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.ymca-location-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.location-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.location-name {
    font-size: 20px;
    font-weight: 600;
    color: #20bdbe;
    margin: 0;
    text-transform: uppercase !important;
    letter-spacing: .5px !important;
    font-weight: 400 !important;
}

.location-distance {
    font-family: 'Grack';
    font-style: normal;
    font-weight: 300;
    background: #20bdbe;
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 16px;
    white-space: nowrap;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.location-address {
    color: #666;
    margin: 10px 0;
    line-height: 1.6;
}

.location-address strong {
    display: block;
    color: #333;
    margin-bottom: 5px;
}

.location-actions {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.location-link {
    display: inline-block;
    padding: 10px 20px;
    background: #ebebebff;
    color: #333;
    text-decoration: none;
    border-radius: 4px;
    font-weight: 500;
    transition: background 0.3s;
    font-family: 'Grack';
    font-style: normal;
    font-weight: 300;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 16px;
}

.location-link:hover {
    background: #e0e0e0;
}

.location-link.primary {
    background: #20bdbe;
    color: white;
}

.location-link.primary:hover {
    background: #20bdbe;
}

.find-a-y-search-form h5 {
  font-family: 'Noto Serif';
  font-style: italic;
  font-weight: 500;
  font-size: 16px;
  letter-spacing: -.15px;
  color: #636466;
  line-height: 1.5em;
  text-transform: capitalize !important;
  text-align: center;
}

@media (max-width: 640px) {
    .search-input-group {
        flex-direction: column;
    }
    
    .find-a-y-submit-btn {
        width: 100%;
    }
    
    .location-header {
        flex-direction: column;
        gap: 10px;
    }
    
    .location-distance {
        align-self: flex-start;
    }
    
    .find-a-y-submit-btn {
        padding: 16px 16px;
        font-family: 'Grack';
        font-style: normal;
        font-weight: 400 !important;
        font-size: 20px;
        letter-spacing: 0px;
        line-height: 1.2em;
        text-transform: uppercase;
        background: #20bdbe !important;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.3s;
        white-space: nowrap;
    }
}
</style>