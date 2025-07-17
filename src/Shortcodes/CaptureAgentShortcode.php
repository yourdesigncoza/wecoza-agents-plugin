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
        
        // Enqueue agent form validation
        wp_enqueue_script(
            'wecoza-agent-form-validation',
            WECOZA_AGENTS_PLUGIN_URL . 'assets/js/agent-form-validation.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
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
        $data = array(
            // Personal Information
            'title' => $this->get_request_param('title', '', 'POST'),
            'first_name' => $this->get_request_param('first_name', '', 'POST'),
            'last_name' => $this->get_request_param('surname', '', 'POST'),
            'known_as' => $this->get_request_param('known_as', '', 'POST'),
            'gender' => $this->get_request_param('gender', '', 'POST'),
            'race' => $this->get_request_param('race', '', 'POST'),
            
            // Identification
            'id_type' => $this->get_request_param('id_type', 'sa_id', 'POST'),
            'id_number' => $this->get_request_param('sa_id_no', '', 'POST'),
            'passport_number' => $this->get_request_param('passport_number', '', 'POST'),
            
            // Contact Information
            'phone' => $this->get_request_param('tel_number', '', 'POST'),
            'email' => $this->get_request_param('email_address', '', 'POST'),
            'street_address' => $this->get_request_param('address_line_1', '', 'POST') . "\n" . 
                               $this->get_request_param('address_line_2', '', 'POST'),
            'city' => $this->get_request_param('city_town', '', 'POST'),
            'province' => $this->get_request_param('province_region', '', 'POST'),
            'postal_code' => $this->get_request_param('postal_code', '', 'POST'),
            
            // SACE Registration
            'sace_number' => $this->get_request_param('sace_number', '', 'POST'),
            'phase_registered' => $this->get_request_param('phase_registered', '', 'POST'),
            'subjects_registered' => $this->get_request_param('subjects_registered', '', 'POST'),
            
            // Quantum Tests
            'quantum_maths_passed' => $this->get_request_param('quantum_maths_passed', false, 'POST'),
            'quantum_science_passed' => $this->get_request_param('quantum_science_passed', false, 'POST'),
            
            // Criminal Record
            'criminal_record_checked' => $this->get_request_param('criminal_record_checked', false, 'POST'),
            'criminal_record_date' => $this->get_request_param('criminal_record_date', '', 'POST'),
            
            // Agreement
            'signed_agreement' => $this->get_request_param('signed_agreement', false, 'POST'),
            'signed_agreement_date' => $this->get_request_param('signed_agreement_date', '', 'POST'),
            
            // Banking Details
            'bank_name' => $this->get_request_param('bank_name', '', 'POST'),
            'account_holder' => $this->get_request_param('account_holder', '', 'POST'),
            'account_number' => $this->get_request_param('account_number', '', 'POST'),
            'branch_code' => $this->get_request_param('branch_code', '', 'POST'),
            'account_type' => $this->get_request_param('account_type', '', 'POST'),
            
            // Preferred Working Areas
            'preferred_areas' => $this->collect_preferred_areas(),
        );
        
        // Set ID number based on type
        if ($data['id_type'] === 'passport') {
            $data['id_number'] = '';
        } else {
            $data['passport_number'] = '';
        }
        
        return $data;
    }

    /**
     * Collect preferred areas
     *
     * @since 1.0.0
     * @return string JSON encoded preferred areas
     */
    private function collect_preferred_areas() {
        $areas = array();
        
        for ($i = 1; $i <= 3; $i++) {
            $area = $this->get_request_param("preferred_working_area_$i", '', 'POST');
            if (!empty($area)) {
                $areas[] = $area;
            }
        }
        
        return json_encode($areas);
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
        
        if (empty($this->form_data['last_name'])) {
            $this->form_errors['surname'] = __('Surname is required.', 'wecoza-agents-plugin');
            $valid = false;
        }
        
        if (empty($this->form_data['phone'])) {
            $this->form_errors['tel_number'] = __('Contact number is required.', 'wecoza-agents-plugin');
            $valid = false;
        }
        
        if (empty($this->form_data['email'])) {
            $this->form_errors['email_address'] = __('Email address is required.', 'wecoza-agents-plugin');
            $valid = false;
        } elseif (!is_email($this->form_data['email'])) {
            $this->form_errors['email_address'] = __('Please enter a valid email address.', 'wecoza-agents-plugin');
            $valid = false;
        }
        
        // Validate ID based on type
        if ($this->form_data['id_type'] === 'sa_id') {
            if (empty($this->form_data['id_number'])) {
                $this->form_errors['sa_id_no'] = __('SA ID number is required.', 'wecoza-agents-plugin');
                $valid = false;
            } else {
                $validation = \WeCoza\Agents\Helpers\ValidationHelper::validate_sa_id($this->form_data['id_number']);
                if (!$validation['valid']) {
                    $this->form_errors['sa_id_no'] = $validation['message'];
                    $valid = false;
                }
            }
        } else {
            if (empty($this->form_data['passport_number'])) {
                $this->form_errors['passport_number'] = __('Passport number is required.', 'wecoza-agents-plugin');
                $valid = false;
            } else {
                $validation = \WeCoza\Agents\Helpers\ValidationHelper::validate_passport($this->form_data['passport_number']);
                if (!$validation['valid']) {
                    $this->form_errors['passport_number'] = $validation['message'];
                    $valid = false;
                }
            }
        }
        
        // Check for duplicate email (excluding current agent if editing)
        $existing = $this->agent_queries->get_agent_by_email($this->form_data['email']);
        if ($existing && (!$this->current_agent || $existing['id'] != $this->current_agent['id'])) {
            $this->form_errors['email_address'] = __('This email address is already registered.', 'wecoza-agents-plugin');
            $valid = false;
        }
        
        // Check for duplicate ID number
        if (!empty($this->form_data['id_number'])) {
            $existing = $this->agent_queries->get_agent_by_id_number($this->form_data['id_number']);
            if ($existing && (!$this->current_agent || $existing['id'] != $this->current_agent['id'])) {
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
        if ($this->current_agent) {
            // Update existing agent
            $success = $this->agent_queries->update_agent($this->current_agent['id'], $this->form_data);
            return $success ? $this->current_agent['id'] : false;
        } else {
            // Create new agent
            return $this->agent_queries->create_agent($this->form_data);
        }
    }

    /**
     * Handle file uploads
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     */
    private function handle_file_uploads($agent_id) {
        // Handle signed agreement file
        if (!empty($_FILES['signed_agreement_file']['name'])) {
            $file_path = $this->upload_file('signed_agreement_file', $agent_id);
            if ($file_path) {
                $this->agent_queries->update_agent($agent_id, array(
                    'agreement_file_path' => $file_path
                ));
            }
        }
        
        // Handle criminal record file
        if (!empty($_FILES['criminal_record_file']['name'])) {
            $file_path = $this->upload_file('criminal_record_file', $agent_id);
            if ($file_path) {
                $this->agent_queries->add_agent_meta($agent_id, 'criminal_record_file', $file_path);
            }
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
        $agent_dir = $upload_dir['basedir'] . '/wecoza-agents/agent-' . $agent_id;
        
        if (!file_exists($agent_dir)) {
            wp_mkdir_p($agent_dir);
        }
        
        // Generate unique filename
        $filename = sanitize_file_name($field_name . '-' . time() . '.' . $file_ext);
        $file_path = $agent_dir . '/' . $filename;
        
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
        
        // Process preferred areas from JSON
        $preferred_areas = isset($agent['preferred_areas']) ? json_decode($agent['preferred_areas'], true) : array();
        if (!is_array($preferred_areas)) {
            $preferred_areas = array();
        }
        
        // Load template
        $this->load_template('agent-capture-form.php', array(
            'agent' => $agent,
            'errors' => $this->form_errors,
            'mode' => $this->current_agent ? 'edit' : 'add',
            'atts' => $atts,
            'working_areas' => WorkingAreasService::get_working_areas(),
            'preferred_areas' => $preferred_areas,
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
}