<?php
/**
 * File: includes/class-import-handler.php
 * Handles smart import/update logic for YMCA locations
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class Find_A_Y_Import_Handler {
    
    private $table_name;
    private $geocode_api_key;
    
    public function __construct($table_name) {
        $this->table_name = $table_name;
        $this->geocode_api_key = get_option('find_a_y_google_api_key', '');
    }
    
    public function preview_import($uploaded_file) {
        $file_path = $uploaded_file['tmp_name'];
        $file_name = $uploaded_file['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        try {
            $data = $this->read_file($file_path, $file_ext);
            
            if (empty($data)) {
                return array('success' => false, 'message' => 'No data found in file');
            }
            
            $analysis = $this->analyze_changes($data);
            
            $temp_file = wp_upload_dir()['basedir'] . '/find-a-y-temp-' . time() . '.' . $file_ext;
            move_uploaded_file($file_path, $temp_file);
            set_transient('find_a_y_import_file_' . get_current_user_id(), $temp_file, 3600);
            
            return array(
                'success' => true,
                'analysis' => $analysis,
                'total_records' => count($data)
            );
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => $e->getMessage());
        }
    }
    
    private function read_file($file_path, $file_ext) {
        if ($file_ext === 'csv') {
            $reader = new Csv();
        } else if (in_array($file_ext, array('xlsx', 'xls'))) {
            $reader = new Xlsx();
        } else {
            throw new Exception('Unsupported file format. Please upload CSV or Excel file.');
        }
        
        $spreadsheet = $reader->load($file_path);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        $header_row = 0;
        foreach ($rows as $index => $row) {
            if (in_array('Y Name', $row) || in_array('Y name', $row)) {
                $header_row = $index;
                break;
            }
        }
        
        $headers = $rows[$header_row];
        $data = array();
        
        for ($i = $header_row + 1; $i < count($rows); $i++) {
            if (empty(array_filter($rows[$i]))) {
                continue;
            }
            
            $record = array();
            foreach ($headers as $col_index => $header) {
                if (!empty($header)) {
                    $record[$header] = isset($rows[$i][$col_index]) ? $rows[$i][$col_index] : null;
                }
            }
            
            if (!empty($record['Y Name'])) {
                $data[] = $this->clean_record($record);
            }
        }
        
        return $data;
    }
    
    private function clean_record($record) {
        return array(
            'y_name' => trim($record['Y Name']),
            'address' => trim($record['Physical Address']),
            'address2' => !empty($record['Physical Address1']) ? trim($record['Physical Address1']) : null,
            'city' => trim($record['Physical City']),
            'state' => trim($record['Physical State']),
            'zip_code' => $this->clean_zip($record['Physical ZIP Code']),
            'website' => $this->clean_website($record['Website'])
        );
    }
    
    private function clean_zip($zip) {
        $zip = trim($zip);
        $zip = preg_replace('/\s+/', '', $zip);
        $zip = preg_replace('/\-+$/', '', $zip);
        return $zip;
    }
    
    private function clean_website($website) {
        if (empty($website) || $website === 'undefined' || strtolower($website) === 'null') {
            return null;
        }
        return trim($website);
    }
    
    private function analyze_changes($new_data) {
        global $wpdb;
        
        $existing = $wpdb->get_results("SELECT * FROM {$this->table_name}", ARRAY_A);
        
        $existing_map = array();
        foreach ($existing as $location) {
            $key = $this->get_unique_key($location['y_name'], $location['zip_code']);
            $existing_map[$key] = $location;
        }
        
        $new_map = array();
        foreach ($new_data as $location) {
            $key = $this->get_unique_key($location['y_name'], $location['zip_code']);
            $new_map[$key] = $location;
        }
        
        $new_locations = array();
        $updated_locations = array();
        $unchanged_locations = array();
        
        foreach ($new_data as $location) {
            $key = $this->get_unique_key($location['y_name'], $location['zip_code']);
            
            if (!isset($existing_map[$key])) {
                $new_locations[] = $location;
            } else {
                $changes = $this->compare_records($existing_map[$key], $location);
                if (!empty($changes)) {
                    $updated_locations[] = array(
                        'location' => $location,
                        'changes' => $changes
                    );
                } else {
                    $unchanged_locations[] = $location;
                }
            }
        }
        
        $removed_locations = array();
        foreach ($existing as $location) {
            $key = $this->get_unique_key($location['y_name'], $location['zip_code']);
            if (!isset($new_map[$key])) {
                $removed_locations[] = $location;
            }
        }
        
        return array(
            'new' => $new_locations,
            'updated' => $updated_locations,
            'unchanged' => $unchanged_locations,
            'removed' => $removed_locations,
            'counts' => array(
                'new' => count($new_locations),
                'updated' => count($updated_locations),
                'unchanged' => count($unchanged_locations),
                'removed' => count($removed_locations)
            )
        );
    }
    
    private function get_unique_key($name, $zip) {
        return strtolower(trim($name)) . '|' . trim($zip);
    }
    
    private function compare_records($existing, $new) {
        $changes = array();
        
        $fields = array('address', 'address2', 'city', 'state', 'website');
        
        foreach ($fields as $field) {
            $existing_value = isset($existing[$field]) ? $existing[$field] : '';
            $new_value = isset($new[$field]) ? $new[$field] : '';
            
            if ($existing_value !== $new_value) {
                $changes[$field] = array(
                    'old' => $existing_value,
                    'new' => $new_value
                );
            }
        }
        
        return $changes;
    }
    
    public function process_import($file_path) {
        global $wpdb;
        
        $file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        try {
            $data = $this->read_file($file_path, $file_ext);
            $analysis = $this->analyze_changes($data);
            
            $wpdb->query('START TRANSACTION');
            
            $results = array(
                'inserted' => 0,
                'updated' => 0,
                'deleted' => 0,
                'errors' => array()
            );
            
            foreach ($analysis['new'] as $location) {
                $result = $wpdb->insert($this->table_name, $location);
                if ($result) {
                    $results['inserted']++;
                    $this->queue_geocoding($wpdb->insert_id);
                } else {
                    $results['errors'][] = "Failed to insert: {$location['y_name']}";
                }
            }
            
            foreach ($analysis['updated'] as $update) {
                $location = $update['location'];
                $existing = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$this->table_name} WHERE y_name = %s AND zip_code = %s",
                    $location['y_name'],
                    $location['zip_code']
                ), ARRAY_A);
                
                if ($existing) {
                    $needs_geocoding = $this->address_changed($update['changes']);
                    
                    $result = $wpdb->update(
                        $this->table_name,
                        $location,
                        array('id' => $existing['id'])
                    );
                    
                    if ($result !== false) {
                        $results['updated']++;
                        if ($needs_geocoding) {
                            $this->queue_geocoding($existing['id']);
                        }
                    } else {
                        $results['errors'][] = "Failed to update: {$location['y_name']}";
                    }
                }
            }
            
            foreach ($analysis['removed'] as $location) {
                $result = $wpdb->delete(
                    $this->table_name,
                    array('id' => $location['id'])
                );
                if ($result) {
                    $results['deleted']++;
                } else {
                    $results['errors'][] = "Failed to delete: {$location['y_name']}";
                }
            }
            
            $wpdb->query('COMMIT');
            
            $this->process_geocoding_queue();
            
            return array(
                'success' => true,
                'results' => $results,
                'summary' => sprintf(
                    'Import complete: %d new, %d updated, %d deleted',
                    $results['inserted'],
                    $results['updated'],
                    $results['deleted']
                )
            );
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return array('success' => false, 'message' => $e->getMessage());
        }
    }
    
    private function address_changed($changes) {
        $address_fields = array('address', 'address2', 'city', 'state');
        foreach ($address_fields as $field) {
            if (isset($changes[$field])) {
                return true;
            }
        }
        return false;
    }
    
    private function queue_geocoding($location_id) {
        $queue = get_option('find_a_y_geocode_queue', array());
        if (!in_array($location_id, $queue)) {
            $queue[] = $location_id;
            update_option('find_a_y_geocode_queue', $queue);
        }
    }
    
    private function process_geocoding_queue() {
        $queue = get_option('find_a_y_geocode_queue', array());
        
        if (empty($queue) || empty($this->geocode_api_key)) {
            return;
        }
        
        $batch_size = 10;
        $batch = array_slice($queue, 0, $batch_size);
        
        foreach ($batch as $location_id) {
            $this->geocode_location($location_id);
            $key = array_search($location_id, $queue);
            if ($key !== false) {
                unset($queue[$key]);
            }
        }
        
        update_option('find_a_y_geocode_queue', array_values($queue));
        
        if (!empty($queue)) {
            wp_schedule_single_event(time() + 60, 'find_a_y_continue_geocoding');
        }
    }
    
    private function geocode_location($location_id) {
        global $wpdb;
        
        if (empty($this->geocode_api_key)) {
            return false;
        }
        
        $location = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $location_id
        ), ARRAY_A);
        
        if (!$location) {
            return false;
        }
        
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
            $this->geocode_api_key
        );
        
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($body['status'] === 'OK' && !empty($body['results'][0])) {
            $lat = $body['results'][0]['geometry']['location']['lat'];
            $lng = $body['results'][0]['geometry']['location']['lng'];
            
            $wpdb->update(
                $this->table_name,
                array(
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'geocoded' => 1
                ),
                array('id' => $location_id)
            );
            
            return true;
        }
        
        return false;
    }
}