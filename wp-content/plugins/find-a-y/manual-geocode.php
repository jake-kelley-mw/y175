<?php
// Manual geocoding trigger - LARGE BATCH
require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Unauthorized');
}

global $wpdb;
$table_name = $wpdb->prefix . 'ymca_locations';

// Process 50 at a time instead of 10
$locations = $wpdb->get_results("
    SELECT id, address, city, state, zip_code 
    FROM {$table_name} 
    WHERE geocoded = 0 
    LIMIT 50
", ARRAY_A);

$total_remaining = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE geocoded = 0");

echo "<h1>Batch Geocoding</h1>";
echo "<p><strong>{$total_remaining} locations remaining</strong></p>";
echo "<p>Processing batch of " . count($locations) . " locations...</p>";
echo "<hr>";

$api_key = get_option('find_a_y_google_api_key', '');

if (empty($api_key)) {
    die('<p style="color:red;">ERROR: No API key configured!</p>');
}

$success_count = 0;
$fail_count = 0;

foreach ($locations as $location) {
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
        $fail_count++;
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
        
        $success_count++;
        echo "✓ ";
    } else {
        $fail_count++;
        echo "✗ ";
    }
    
    // Flush output so you can see progress
    if ($success_count % 10 === 0) {
        echo "<br>";
        flush();
    }
    
    // Small delay to respect API limits
    usleep(100000); // 0.1 seconds
}

echo "<hr>";
echo "<h2>Batch Complete!</h2>";
echo "<p style='color:green;'><strong>✓ {$success_count} successfully geocoded</strong></p>";
echo "<p style='color:red;'><strong>✗ {$fail_count} failed</strong></p>";

$still_remaining = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE geocoded = 0");
echo "<p><strong>{$still_remaining} locations still need geocoding</strong></p>";

if ($still_remaining > 0) {
    echo "<p><a href='manual-geocode.php' class='button button-primary' style='display:inline-block;padding:10px 20px;background:#0073aa;color:white;text-decoration:none;border-radius:4px;'>Process Next 50</a></p>";
} else {
    echo "<p style='color:green;font-size:18px;'><strong>🎉 All locations geocoded!</strong></p>";
}

echo "<p><a href='/wp-admin/admin.php?page=find-a-y'>Back to Dashboard</a></p>";