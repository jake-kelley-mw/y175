<?php
/**
 * File: includes/class-location-finder.php
 * Handles location search and distance calculations
 */

if (!defined('ABSPATH')) exit;

class Find_A_Y_Location_Finder {
    
    private $table_name;
    private $geocode_api_key;
    
    public function __construct($table_name) {
        $this->table_name = $table_name;
        $this->geocode_api_key = get_option('find_a_y_google_api_key', '');
    }
    
    public function find_by_zip($zip_code, $limit = 10, $radius_miles = 50) {
        $zip_coords = $this->get_zip_coordinates($zip_code);
        
        if (!$zip_coords) {
            return false;
        }
        
        global $wpdb;
        
        $locations = $wpdb->get_results($wpdb->prepare("
            SELECT *,
            (3959 * acos(
                cos(radians(%f)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(%f)) + 
                sin(radians(%f)) * sin(radians(latitude))
            )) AS distance
            FROM {$this->table_name}
            WHERE latitude IS NOT NULL 
            AND longitude IS NOT NULL
            AND geocoded = 1
            HAVING distance <= %d
            ORDER BY distance ASC
            LIMIT %d
        ", $zip_coords['lat'], $zip_coords['lng'], $zip_coords['lat'], $radius_miles, $limit), ARRAY_A);
        
        if (empty($locations)) {
            return $this->fallback_search($zip_code, $limit);
        }
        
        foreach ($locations as &$location) {
            $location['distance'] = round($location['distance'], 1);
            $location['formatted_address'] = $this->format_address($location);
        }
        
        return $locations;
    }
    
    private function fallback_search($zip_code, $limit) {
        global $wpdb;
        
        $partial_zip = substr($zip_code, 0, 3);
        
        $locations = $wpdb->get_results($wpdb->prepare("
            SELECT *
            FROM {$this->table_name}
            WHERE zip_code LIKE %s
            ORDER BY zip_code ASC
            LIMIT %d
        ", $partial_zip . '%', $limit), ARRAY_A);
        
        foreach ($locations as &$location) {
            $location['distance'] = null;
            $location['formatted_address'] = $this->format_address($location);
        }
        
        return $locations;
    }
    
    private function get_zip_coordinates($zip_code) {
        $cache_key = 'find_a_y_zip_' . $zip_code;
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        if (empty($this->geocode_api_key)) {
            return $this->get_zip_from_database($zip_code);
        }
        
        $url = sprintf(
            'https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s',
            urlencode($zip_code . ', USA'),
            $this->geocode_api_key
        );
        
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            return $this->get_zip_from_database($zip_code);
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($body['status'] === 'OK' && !empty($body['results'][0])) {
            $coords = array(
                'lat' => $body['results'][0]['geometry']['location']['lat'],
                'lng' => $body['results'][0]['geometry']['location']['lng']
            );
            
            set_transient($cache_key, $coords, WEEK_IN_SECONDS);
            
            return $coords;
        }
        
        return $this->get_zip_from_database($zip_code);
    }
    
    private function get_zip_from_database($zip_code) {
        global $wpdb;
        
        $location = $wpdb->get_row($wpdb->prepare("
            SELECT latitude, longitude
            FROM {$this->table_name}
            WHERE zip_code = %s
            AND latitude IS NOT NULL
            AND longitude IS NOT NULL
            LIMIT 1
        ", $zip_code), ARRAY_A);
        
        if ($location) {
            return array(
                'lat' => $location['latitude'],
                'lng' => $location['longitude']
            );
        }
        
        return false;
    }
    
    private function format_address($location) {
        $parts = array($location['address']);
        
        if (!empty($location['address2'])) {
            $parts[] = $location['address2'];
        }
        
        $parts[] = $location['city'] . ', ' . $location['state'] . ' ' . $location['zip_code'];
        
        return implode(', ', $parts);
    }
    
    public function get_location_by_id($id) {
        global $wpdb;
        
        $location = $wpdb->get_row($wpdb->prepare("
            SELECT *
            FROM {$this->table_name}
            WHERE id = %d
        ", $id), ARRAY_A);
        
        if ($location) {
            $location['formatted_address'] = $this->format_address($location);
        }
        
        return $location;
    }
    
    public function search_by_name($search_term, $limit = 20) {
        global $wpdb;
        
        $locations = $wpdb->get_results($wpdb->prepare("
            SELECT *
            FROM {$this->table_name}
            WHERE y_name LIKE %s
            OR city LIKE %s
            ORDER BY y_name ASC
            LIMIT %d
        ", '%' . $wpdb->esc_like($search_term) . '%', '%' . $wpdb->esc_like($search_term) . '%', $limit), ARRAY_A);
        
        foreach ($locations as &$location) {
            $location['formatted_address'] = $this->format_address($location);
        }
        
        return $locations;
    }
}