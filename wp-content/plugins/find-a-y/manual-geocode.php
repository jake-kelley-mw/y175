<?php
// Manual geocoding trigger
require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Unauthorized');
}

global $wpdb;
$table_name = $wpdb->prefix . 'ymca_locations';

// Get locations that need geocoding
$locations = $wpdb->get_results("
    SELECT id, address, city, state, zip_code 
    FROM {$table_name} 
    WHERE geocoded = 0 
    LIMIT 10
", ARRAY_A);

echo "<h1>Manual Geocoding</h1>";
echo "<p>Processing " . count($locations) . " locations...</p>";

$api_key = get_option('find_a_y_google_api_key', '');

if (empty($api_key)) {
    die('<p style="color:red;">ERROR: No API key configured!</p>');
}

foreach ($locations as $location) {
    echo "<p>Processing: {$location['address']}, {$location['city']}, {$location['state']}...</p>";
    
    $address = sprintf(
        '%s, %s, %s %s',
        $location['address'],
        $location['city'],
        $location['state'],
        $location['zip_code']
    );
    
    $url = sprintf(
        'https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s',
        urlencode($address),
        $api_key
    );
    
    $response = wp_remote_get($url);
    
    if (is_wp_error($response)) {
        echo "<p style='color:red;'>Error: " . $response->get_error_message() . "</p>";
        continue;
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if ($body['status'] === 'OK' && !empty($body['results'][0])) {
        $lat = $body['results'][0]['geometry']['location']['lat'];
        $lng = $body['results'][0]['geometry']['location']['lng'];
        
        $wpdb->update(
            $table_name,
            array(
                'latitude' => $lat,
                'longitude' => $lng,
                'geocoded' => 1
            ),
            array('id' => $location['id'])
        );
        
        echo "<p style='color:green;'>✓ Geocoded: {$lat}, {$lng}</p>";
    } else {
        echo "<p style='color:orange;'>⚠ Failed: " . $body['status'] . "</p>";
        if (isset($body['error_message'])) {
            echo "<p style='color:orange;'>Message: " . $body['error_message'] . "</p>";
        }
    }
    
    // Prevent hitting rate limits
    sleep(1);
}

echo "<h2>Complete!</h2>";
echo "<p><a href='/wp-admin/admin.php?page=find-a-y'>Back to Dashboard</a></p>";
echo "<p><a href='manual-geocode.php'>Run Again (10 more)</a></p>";