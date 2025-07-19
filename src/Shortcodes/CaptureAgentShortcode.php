<?php
/**
 * Capture Agent Shortcode
 *
 * Handles the agent capture/edit form shortcode.
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 */

namespace WeCoza\Agents\Shortcodes;

use WeCoza\Agents\Services\WorkingAreasService;
use WeCoza\Agents\Helpers\FormHelpers;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Capture Agent Shortcode class
 *
 * @since 1.0.0
 */
class CaptureAgentShortcode extends AbstractShortcode {

    /**
     * Form data
     *
     * @var array
     */
    private $form_data = array();

    /**
     * Form errors
     *
     * @var array
     */
    private $form_errors = array();

    /**
     * Current agent
     *
     * @var array|null
     */
    private $current_agent = null;

    /**
     * Initialize shortcode
     *
     * @since 1.0.0
     */
    protected function init() {
        $this->tag = 'wecoza_capture_agents';
        $this->default_atts = array(
            'mode' => 'add', // add or edit
            'agent_id' => 0,
            'redirect_after_save' => '',
        );
    }

    /**
     * Check permissions
     *
     * @since 1.0.0
     * @return bool
     */
    protected function check_permissions() {
        // Only editors and above can manage agents
        return $this->can_manage_agents();
    }

    /**
     * Enqueue assets
     *
     * @since 1.0.0
     */
    protected function enqueue_assets() {
        parent::enqueue_assets();
        
        // Enqueue Google Maps API first (new API without callback)
        $google_maps_api_key = $this->get_google_maps_api_key();
        
        if ($google_maps_api_key) {
            wp_enqueue_script(
                'google-maps-api',
                'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($google_maps_api_key) . '&libraries=places&loading=async',
                array(),
                null,
                true
            );
        } else {
            // Log warning if API key is missing
            error_log('[WeCoza Agents] Warning: Google Maps API key not configured. Address autocomplete will not work.');
        }

        // Enqueue agent form validation
        wp_enqueue_script(
            'wecoza-agent-form-validation',
            WECOZA_AGENTS_PLUGIN_URL . 'assets/js/agent-form-validation.js',
            array('jquery', 'google-maps-api'),
            '1.0.0',
            true
        );
        
        // Add inline script to initialize Google Places when API is loaded
        wp_add_inline_script('wecoza-agent-form-validation', '
            jQuery(document).ready(function($) {
                // Initialize Google Places when the API is ready
                if (typeof google !== "undefined" && google.maps && google.maps.importLibrary) {
                    initializeGooglePlaces();
                } else {
                    // Fallback: wait for the API to load
                    var checkAPI = setInterval(function() {
                        if (typeof google !== "undefined" && google.maps && google.maps.importLibrary) {
                            clearInterval(checkAPI);
                            initializeGooglePlaces();
                        }
                    }, 100);
                }
            });
        ', 'after');
        
        // Update script dependencies
        wp_script_add_data('wecoza-agents', 'deps', array('jquery'));
    }

    /**
     * Render shortcode content
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes
     * @param string $content Shortcode content
     */
    protected function render_shortcode($atts, $content) {
        // Check for agent ID in URL
        $agent_id = $this->get_request_param('agent_id', $atts['agent_id'], 'GET');
        $mode = $agent_id > 0 ? 'edit' : $atts['mode'];
        
        // Load agent data if editing
        if ($mode === 'edit' && $agent_id > 0) {
            $this->current_agent = $this->agent_queries->get_agent($agent_id);
            if (!$this->current_agent) {
                $this->add_error_message(__('Agent not found.', 'wecoza-agents-plugin'));
                return;
            }
        }
        
        // Handle form submission
        if ($this->is_form_submitted()) {
            $this->handle_form_submission($atts);
        }
        
        // Display the form
        $this->display_form($atts);
    }

    /**
     * Check if form is submitted
     *
     * @since 1.0.0
     * @return bool
     */
    private function is_form_submitted() {
        return $_SERVER['REQUEST_METHOD'] === 'POST' && 
               $this->verify_nonce('submit_agent_form', 'wecoza_agents_form_nonce');
    }

    /**
     * Handle form submission
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes
     */
    private function handle_form_submission($atts) {
        // Collect form data
        $this->form_data = $this->collect_form_data();
        
        // Validate form data
        if (!$this->validate_form_data()) {
            return;
        }
        
        // Save agent
        $agent_id = $this->save_agent();
        
        if ($agent_id) {
            // Handle file uploads
            $this->handle_file_uploads($agent_id);
            
            // Success message
            $this->add_success_message(__('Agent saved successfully.', 'wecoza-agents-plugin'));
            
            // Redirect if specified
            if (!empty($atts['redirect_after_save'])) {
                wp_safe_redirect($atts['redirect_after_save']);
                exit;
            }
            
            // Clear form data on success
            $this->form_data = array();
        } else {
            $this->add_error_message(__('Failed to save agent. Please try again.', 'wecoza-agents-plugin'));
        }
    }

    /**
     * Collect form data
     *
     * @since 1.0.0
     * @return array Form data
     */
    private function collect_form_data() {
        // Log raw POST data for debugging
        
        $data = array(
            // Personal Information - Using DB field names
            'title' => sanitize_text_field($this->get_request_param('title', '', 'POST')),
            'first_name' => sanitize_text_field($this->get_request_param('first_name', '', 'POST')),
            'second_name' => $this->process_text_field($this->get_request_param('second_name', '', 'POST')),
            'surname' => sanitize_text_field($this->get_request_param('surname', '', 'POST')),  // DB field name
            'initials' => sanitize_text_field($this->get_request_param('initials', '', 'POST')),
            'gender' => sanitize_text_field($this->get_request_param('gender', '', 'POST')),
            'race' => sanitize_text_field($this->get_request_param('race', '', 'POST')),
            
            // Identification - Using DB field names
            'id_type' => sanitize_text_field($this->get_request_param('id_type', 'sa_id', 'POST')),
            'sa_id_no' => preg_replace('/[^0-9]/', '', $this->get_request_param('sa_id_no', '', 'POST')),  // DB field name - numbers only
            'passport_number' => sanitize_text_field($this->get_request_param('passport_number', '', 'POST')),
            
            // Contact Information - Using DB field names
            'tel_number' => preg_replace('/[^0-9+\-\(\)\s]/', '', $this->get_request_param('tel_number', '', 'POST')),  // DB field name - phone format
            'email_address' => sanitize_email($this->get_request_param('email_address', '', 'POST')),  // DB field name
            
            // Address Information - Using DB field names
            'residential_address_line' => sanitize_text_field($this->get_request_param('address_line_1', '', 'POST')),  // DB field name
            'address_line_2' => sanitize_text_field($this->get_request_param('address_line_2', '', 'POST')),
            'residential_suburb' => sanitize_text_field($this->get_request_param('residential_suburb', '', 'POST')),
            'city' => sanitize_text_field($this->get_request_param('city_town', '', 'POST')),
            'province' => sanitize_text_field($this->get_request_param('province_region', '', 'POST')),
            'residential_postal_code' => preg_replace('/[^0-9]/', '', $this->get_request_param('postal_code', '', 'POST')),  // DB field name - numbers only
            
            // Working Areas - Using DB field names (pass as-is for AgentQueries to handle NULL conversion)
            'preferred_working_area_1' => $this->get_request_param('preferred_working_area_1', '', 'POST'),
            'preferred_working_area_2' => $this->get_request_param('preferred_working_area_2', '', 'POST'),
            'preferred_working_area_3' => $this->get_request_param('preferred_working_area_3', '', 'POST'),
            
            // SACE Registration
            'sace_number' => sanitize_text_field($this->get_request_param('sace_number', '', 'POST')),
            'sace_registration_date' => $this->process_date_field($this->get_request_param('sace_registration_date', '', 'POST')),
            'sace_expiry_date' => $this->process_date_field($this->get_request_param('sace_expiry_date', '', 'POST')),
            'phase_registered' => sanitize_text_field($this->get_request_param('phase_registered', '', 'POST')),
            'subjects_registered' => sanitize_textarea_field($this->get_request_param('subjects_registered', '', 'POST')),
            
            // Qualifications
            'highest_qualification' => sanitize_text_field($this->get_request_param('highest_qualification', '', 'POST')),
            
            // Quantum Tests
            'quantum_maths_score' => $this->process_numeric_field($this->get_request_param('quantum_maths_score', '', 'POST')),
            'quantum_science_score' => $this->process_numeric_field($this->get_request_param('quantum_science_score', '', 'POST')),
            'quantum_assessment' => $this->process_numeric_field($this->get_request_param('quantum_assessment', '', 'POST')),
            
            // Training
            'agent_training_date' => $this->process_date_field($this->get_request_param('agent_training_date', '', 'POST')),
            
            // Criminal Record
            'criminal_record_date' => $this->process_date_field($this->get_request_param('criminal_record_date', '', 'POST')),
            
            // Agreement
            'signed_agreement_date' => $this->process_date_field($this->get_request_param('signed_agreement_date', '', 'POST')),
            
            // Banking Details - Using DB field names
            'bank_name' => sanitize_text_field($this->get_request_param('bank_name', '', 'POST')),
            'account_holder' => sanitize_text_field($this->get_request_param('account_holder', '', 'POST')),
            'bank_account_number' => preg_replace('/[^0-9]/', '', $this->get_request_param('account_number', '', 'POST')),  // DB field name - numbers only
            'bank_branch_code' => preg_replace('/[^0-9]/', '', $this->get_request_param('branch_code', '', 'POST')),  // DB field name - numbers only
            'account_type' => sanitize_text_field($this->get_request_param('account_type', '', 'POST')),
        );
        
        // Clear unused field based on ID type
        if ($data['id_type'] === 'passport') {
            $data['sa_id_no'] = '';
        } else {
            $data['passport_number'] = '';
        }
        
        // Log collected form data for debugging
        
        return $data;
    }


    /**
     * Validate form data
     *
     * @since 1.0.0
     * @return bool Whether data is valid
     */
    private function validate_form_data() {
        $valid = true;
        
        // Required fields
        if (empty($this->form_data['first_name'])) {
            $this->form_errors['first_name'] = __('First name is required.', 'wecoza-agents-plugin');
            $valid = false;
        }
        
        if (empty($this->form_data['surname'])) {
            $this->form_errors['surname'] = __('Surname is required.', 'wecoza-agents-plugin');
            $valid = false;
        }
        
        if (empty($this->form_data['tel_number'])) {
            $this->form_errors['tel_number'] = __('Contact number is required.', 'wecoza-agents-plugin');
            $valid = false;
        }
        
        if (empty($this->form_data['email_address'])) {
            $this->form_errors['email_address'] = __('Email address is required.', 'wecoza-agents-plugin');
            $valid = false;
        } elseif (!is_email($this->form_data['email_address'])) {
            $this->form_errors['email_address'] = __('Please enter a valid email address.', 'wecoza-agents-plugin');
            $valid = false;
        }
        
        // Validate other required fields
        if (empty($this->form_data['gender'])) {
            $this->form_errors['gender'] = __('Gender is required.', 'wecoza-agents-plugin');
            $valid = false;
        }
        
        if (empty($this->form_data['race'])) {
            $this->form_errors['race'] = __('Race is required.', 'wecoza-agents-plugin');
            $valid = false;
        }
        
        if (empty($this->form_data['residential_address_line'])) {
            $this->form_errors['residential_address_line'] = __('Address is required.', 'wecoza-agents-plugin');
            $valid = false;
        }
        
        if (empty($this->form_data['city'])) {
            $this->form_errors['city'] = __('City is required.', 'wecoza-agents-plugin');
            $valid = false;
        }
        
        if (empty($this->form_data['province'])) {
            $this->form_errors['province'] = __('Province is required.', 'wecoza-agents-plugin');
            $valid = false;
        }
        
        if (empty($this->form_data['residential_postal_code'])) {
            $this->form_errors['residential_postal_code'] = __('Postal code is required.', 'wecoza-agents-plugin');
            $valid = false;
        }
        
        if (empty($this->form_data['preferred_working_area_1'])) {
            $this->form_errors['preferred_working_area_1'] = __('At least one preferred working area is required.', 'wecoza-agents-plugin');
            $valid = false;
        }
        
        // Validate ID based on type
        if ($this->form_data['id_type'] === 'sa_id') {
            if (empty($this->form_data['sa_id_no'])) {
                $this->form_errors['sa_id_no'] = __('SA ID number is required.', 'wecoza-agents-plugin');
                $valid = false;
            } else {
                // Validate SA ID format and checksum
                $validation = \WeCoza\Agents\Helpers\ValidationHelper::validate_sa_id($this->form_data['sa_id_no']);
                if (is_array($validation) && !$validation['valid']) {
                    $this->form_errors['sa_id_no'] = $validation['message'];
                    $valid = false;
                } elseif (is_bool($validation) && !$validation) {
                    $this->form_errors['sa_id_no'] = __('SA ID number is invalid.', 'wecoza-agents-plugin');
                    $valid = false;
                }
            }
        } else {
            if (empty($this->form_data['passport_number'])) {
                $this->form_errors['passport_number'] = __('Passport number is required.', 'wecoza-agents-plugin');
                $valid = false;
            } else {
                $validation = \WeCoza\Agents\Helpers\ValidationHelper::validate_passport($this->form_data['passport_number']);
                if (is_array($validation) && !$validation['valid']) {
                    $this->form_errors['passport_number'] = $validation['message'];
                    $valid = false;
                } elseif (is_bool($validation) && !$validation) {
                    $this->form_errors['passport_number'] = __('Passport number is invalid.', 'wecoza-agents-plugin');
                    $valid = false;
                }
            }
        }
        
        // Check for duplicate email (excluding current agent if editing)
        if (!empty($this->form_data['email_address'])) {
            $existing = $this->agent_queries->get_agent_by_email($this->form_data['email_address']);
            if ($existing && (!$this->current_agent || $existing['agent_id'] != $this->current_agent['agent_id'])) {
                $this->form_errors['email_address'] = __('This email address is already registered.', 'wecoza-agents-plugin');
                $valid = false;
            }
        }
        
        // Check for duplicate ID number
        if (!empty($this->form_data['sa_id_no'])) {
            $existing = $this->agent_queries->get_agent_by_id_number($this->form_data['sa_id_no']);
            if ($existing && (!$this->current_agent || $existing['agent_id'] != $this->current_agent['agent_id'])) {
                $this->form_errors['sa_id_no'] = __('This ID number is already registered.', 'wecoza-agents-plugin');
                $valid = false;
            }
        }
        
        return $valid;
    }

    /**
     * Save agent
     *
     * @since 1.0.0
     * @return int|false Agent ID or false on failure
     */
    private function save_agent() {
        // Log data being sent to database
        
        if ($this->current_agent) {
            // Update existing agent
            $success = $this->agent_queries->update_agent($this->current_agent['agent_id'], $this->form_data);
            $result = $success ? $this->current_agent['agent_id'] : false;
        } else {
            // Create new agent
            $result = $this->agent_queries->create_agent($this->form_data);
        }
        
        // Log result
        
        return $result;
    }

    /**
     * Handle file uploads
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     */
    private function handle_file_uploads($agent_id) {
        $uploaded_files = array();
        
        // Handle signed agreement file
        if (!empty($_FILES['signed_agreement_file']['name'])) {
            $file_path = $this->upload_file('signed_agreement_file', $agent_id);
            if ($file_path) {
                $uploaded_files['signed_agreement_file'] = $file_path;
                // Also update the dedicated signed_agreement_file column
                $this->agent_queries->update_agent($agent_id, array(
                    'signed_agreement_file' => $file_path
                ));
            }
        }
        
        // Handle criminal record file
        if (!empty($_FILES['criminal_record_file']['name'])) {
            $file_path = $this->upload_file('criminal_record_file', $agent_id);
            if ($file_path) {
                $uploaded_files['criminal_record_file'] = $file_path;
                // Also update the dedicated criminal_record_file column
                $this->agent_queries->update_agent($agent_id, array(
                    'criminal_record_file' => $file_path
                ));
            }
        }
        
        // Store all uploaded files in the latest_document JSON column
        if (!empty($uploaded_files)) {
            $this->agent_queries->update_agent($agent_id, array(
                'latest_document' => json_encode($uploaded_files)
            ));
        }
    }

    /**
     * Upload file
     *
     * @since 1.0.0
     * @param string $field_name File field name
     * @param int $agent_id Agent ID
     * @return string|false File path or false on failure
     */
    private function upload_file($field_name, $agent_id) {
        if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        $file = $_FILES[$field_name];
        
        // Validate file type
        $allowed_types = array('pdf', 'doc', 'docx');
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_types)) {
            $this->add_error_message(__('Invalid file type. Only PDF and Word documents are allowed.', 'wecoza-agents-plugin'));
            return false;
        }
        
        // Create upload directory
        $upload_dir = wp_upload_dir();
        $agents_dir = $upload_dir['basedir'] . '/agents';
        
        if (!file_exists($agents_dir)) {
            wp_mkdir_p($agents_dir);
        }
        
        // Generate unique filename with agent ID prefix
        $filename = sanitize_file_name('agent-' . $agent_id . '-' . $field_name . '-' . time() . '.' . $file_ext);
        $file_path = $agents_dir . '/' . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Return relative path
            return str_replace($upload_dir['basedir'], '', $file_path);
        }
        
        return false;
    }

    /**
     * Display form
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes
     */
    private function display_form($atts) {
        // Prepare form data
        $agent = $this->current_agent ?: $this->form_data;
        
        // Load template
        $this->load_template('agent-capture-form.php', array(
            'agent' => $agent,
            'errors' => $this->form_errors,
            'mode' => $this->current_agent ? 'edit' : 'add',
            'atts' => $atts,
            'working_areas' => WorkingAreasService::get_working_areas(),
        ), 'forms');
    }

    /**
     * Get field value
     *
     * @since 1.0.0
     * @param array $agent Agent data
     * @param string $field Field name
     * @param mixed $default Default value
     * @return mixed Field value
     */
    private function get_field_value($agent, $field, $default = '') {
        if (isset($this->form_data[$field])) {
            return $this->form_data[$field];
        }
        
        if (isset($agent[$field])) {
            return $agent[$field];
        }
        
        return $default;
    }

    /**
     * Process date field value
     *
     * @since 1.0.0
     * @param string $date_value Date value from form
     * @return string|null Processed date or null if empty
     */
    private function process_date_field($date_value) {
        $date_value = trim($date_value);
        
        // Return null for empty dates
        if (empty($date_value)) {
            return null;
        }
        
        // Validate HTML5 date format and return as-is if valid
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_value)) {
            return $date_value;
        }
        
        // Try to parse common date formats (prioritize database format)
        $formats = array(
            'Y-m-d',     // 2023-05-15 (database format - priority)
            'd/m/Y',     // 15/05/2023 (European format)
            'm/d/Y',     // 05/15/2023 (US format)
            'd-m-Y',     // 15-05-2023
            'm-d-Y',     // 05-15-2023
            'd.m.Y',     // 15.05.2023
            'm.d.Y',     // 05.15.2023
        );
        
        foreach ($formats as $format) {
            $date_obj = DateTime::createFromFormat($format, $date_value);
            if ($date_obj !== false) {
                return $date_obj->format('Y-m-d');
            }
        }
        
        // Try to parse with strtotime as fallback
        $timestamp = strtotime($date_value);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
        
        // Return null if invalid date
        return null;
    }

    /**
     * Process numeric field values
     *
     * @since 1.0.0
     * @param string $value Numeric value from form
     * @return int|null Processed numeric value or null if empty
     */
    private function process_numeric_field($value) {
        $value = trim($value);
        
        // Return null for empty values
        if (empty($value)) {
            return null;
        }
        
        // Return integer value for numeric values
        if (is_numeric($value)) {
            return intval($value);
        }
        
        // Return null if not numeric
        return null;
    }

    /**
     * Process text field values
     *
     * @since 1.0.0
     * @param string $value Text value from form
     * @return string|null Processed text value or null if empty
     */
    private function process_text_field($value) {
        $value = sanitize_text_field($value);
        $value = trim($value);
        
        // Return null for empty values
        if (empty($value)) {
            return null;
        }
        
        return $value;
    }

    /**
     * Get Google Maps API key from environment or WordPress options
     *
     * @since 1.0.0
     * @return string|false Google Maps API key or false if not found
     */
    private function get_google_maps_api_key() {
        // First, try to get from environment variable (most secure)
        $api_key = getenv('GOOGLE_MAPS_API_KEY');
        if (!empty($api_key)) {
            return $api_key;
        }
        
        // Second, try to get from WordPress constant (defined in wp-config.php)
        if (defined('GOOGLE_MAPS_API_KEY')) {
            return GOOGLE_MAPS_API_KEY;
        }
        
        // Third, try to get from WordPress options (configurable in admin)
        $api_key = get_option('wecoza_agents_google_maps_api_key');
        if (!empty($api_key)) {
            return $api_key;
        }
        
        // No API key found
        return false;
    }
}