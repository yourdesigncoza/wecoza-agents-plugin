<?php
/**
 * Agent Capture Form Processor
 *
 * Handles agent form processing logic separate from the shortcode.
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 */

namespace WeCoza\Agents\Forms;

use WeCoza\Agents\Models\Agent;
use WeCoza\Agents\Helpers\ValidationHelper;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Agent Capture Form class
 *
 * @since 1.0.0
 */
class AgentCaptureForm {

    /**
     * Form data
     *
     * @var array
     */
    protected $data = array();

    /**
     * Form errors
     *
     * @var array
     */
    protected $errors = array();

    /**
     * Current agent
     *
     * @var Agent|null
     */
    protected $agent = null;

    /**
     * Form mode
     *
     * @var string
     */
    protected $mode = 'add';

    /**
     * Constructor
     *
     * @since 1.0.0
     * @param array $data Form data
     * @param int $agent_id Agent ID for edit mode
     */
    public function __construct($data = array(), $agent_id = 0) {
        $this->data = $data;
        
        if ($agent_id > 0) {
            $this->agent = new Agent($agent_id);
            if ($this->agent->id) {
                $this->mode = 'edit';
            } else {
                $this->agent = null;
            }
        }
    }

    /**
     * Process form submission
     *
     * @since 1.0.0
     * @return array Result with 'success', 'agent_id', and 'message' keys
     */
    public function process() {
        $result = array(
            'success' => false,
            'agent_id' => 0,
            'message' => '',
        );
        
        // Validate nonce
        if (!$this->verify_nonce()) {
            $result['message'] = __('Security check failed. Please try again.', 'wecoza-agents-plugin');
            return $result;
        }
        
        // Check permissions
        if (!wecoza_agents_can_manage()) {
            $result['message'] = __('You do not have permission to manage agents.', 'wecoza-agents-plugin');
            return $result;
        }
        
        // Prepare form data
        $this->prepare_data();
        
        // Validate form data
        if (!$this->validate()) {
            $result['message'] = __('Please correct the errors and try again.', 'wecoza-agents-plugin');
            return $result;
        }
        
        // Save agent
        $agent_id = $this->save();
        
        if ($agent_id) {
            $result['success'] = true;
            $result['agent_id'] = $agent_id;
            $result['message'] = $this->mode === 'edit' 
                ? __('Agent updated successfully.', 'wecoza-agents-plugin')
                : __('Agent added successfully.', 'wecoza-agents-plugin');
            
            // Handle file uploads
            $this->handle_file_uploads($agent_id);
            
            // Fire action hook
            do_action('wecoza_agents_form_processed', $agent_id, $this->data, $this->mode);
        } else {
            $result['message'] = __('Failed to save agent. Please try again.', 'wecoza-agents-plugin');
        }
        
        return $result;
    }

    /**
     * Verify nonce
     *
     * @since 1.0.0
     * @return bool Whether nonce is valid
     */
    protected function verify_nonce() {
        return isset($_POST['wecoza_agents_form_nonce']) && 
               wp_verify_nonce($_POST['wecoza_agents_form_nonce'], 'submit_agent_form');
    }

    /**
     * Prepare form data
     *
     * @since 1.0.0
     */
    protected function prepare_data() {
        // Map form fields to agent fields
        $field_map = $this->get_field_map();
        $prepared = array();
        
        foreach ($field_map as $form_field => $agent_field) {
            if (isset($this->data[$form_field])) {
                $prepared[$agent_field] = $this->sanitize_field($agent_field, $this->data[$form_field]);
            }
        }
        
        // Handle special fields
        $prepared['id_type'] = isset($this->data['id_type']) ? $this->data['id_type'] : 'sa_id';
        
        // Clear ID fields based on type
        if ($prepared['id_type'] === 'passport') {
            $prepared['id_number'] = '';
        } else {
            $prepared['passport_number'] = '';
        }
        
        // Handle address fields
        $address_parts = array();
        if (!empty($this->data['address_line_1'])) {
            $address_parts[] = sanitize_text_field($this->data['address_line_1']);
        }
        if (!empty($this->data['address_line_2'])) {
            $address_parts[] = sanitize_text_field($this->data['address_line_2']);
        }
        $prepared['street_address'] = implode("\n", $address_parts);
        
        // Handle preferred areas
        $areas = array();
        for ($i = 1; $i <= 3; $i++) {
            if (!empty($this->data["preferred_working_area_$i"])) {
                $areas[] = sanitize_text_field($this->data["preferred_working_area_$i"]);
            }
        }
        $prepared['preferred_areas'] = json_encode($areas);
        
        // Handle checkboxes
        $checkbox_fields = array(
            'criminal_record_checked',
            'signed_agreement'
        );
        
        foreach ($checkbox_fields as $field) {
            $prepared[$field] = isset($this->data[$field]) && $this->data[$field] ? 1 : 0;
        }
        
        $this->data = $prepared;
    }

    /**
     * Get field mapping
     *
     * @since 1.0.0
     * @return array Field map (form field => agent field)
     */
    protected function get_field_map() {
        return array(
            // Personal Information
            'title' => 'title',
            'first_name' => 'first_name',
            'surname' => 'last_name',
            'gender' => 'gender',
            'race' => 'race',
            
            // Identification
            'sa_id_no' => 'id_number',
            'passport_number' => 'passport_number',
            
            // Contact
            'tel_number' => 'phone',
            'email_address' => 'email',
            'city_town' => 'city',
            'province_region' => 'province',
            'postal_code' => 'postal_code',
            
            // SACE Registration
            'sace_number' => 'sace_number',
            'phase_registered' => 'phase_registered',
            'subjects_registered' => 'subjects_registered',
            
            // Banking
            'bank_name' => 'bank_name',
            'account_holder' => 'account_holder',
            'account_number' => 'account_number',
            'branch_code' => 'branch_code',
            'account_type' => 'account_type',
            
            // Dates
            'criminal_record_date' => 'criminal_record_date',
            'signed_agreement_date' => 'signed_agreement_date',
            'agent_training_date' => 'agent_training_date',
            
            // Other
            'highest_qualification' => 'highest_qualification',
            'quantum_communications' => 'quantum_communications',
            'quantum_mathematics' => 'quantum_mathematics',
            'quantum_training' => 'quantum_training',
        );
    }

    /**
     * Sanitize field value
     *
     * @since 1.0.0
     * @param string $field Field name
     * @param mixed $value Field value
     * @return mixed Sanitized value
     */
    protected function sanitize_field($field, $value) {
        switch ($field) {
            case 'email':
                return sanitize_email($value);
                
            case 'phone':
                return ValidationHelper::sanitize_phone($value);
                
            case 'id_number':
                return ValidationHelper::sanitize_sa_id($value);
                
            case 'passport_number':
                return ValidationHelper::sanitize_passport($value);
                
            case 'street_address':
            case 'notes':
                return sanitize_textarea_field($value);
                
            case 'postal_code':
            case 'account_number':
            case 'branch_code':
                return preg_replace('/[^0-9]/', '', $value);
                
            case 'quantum_communications':
            case 'quantum_mathematics':
            case 'quantum_training':
                return is_numeric($value) ? floatval($value) : '';
                
            default:
                return sanitize_text_field($value);
        }
    }

    /**
     * Validate form data
     *
     * @since 1.0.0
     * @return bool Whether data is valid
     */
    protected function validate() {
        // Get validation rules
        $rules = $this->get_validation_rules();
        $field_names = ValidationHelper::get_field_names();
        
        // Validate fields
        $this->errors = ValidationHelper::validate_fields($this->data, $rules, $field_names);
        
        // Additional validation
        if ($this->mode === 'add' || $this->has_changed('email')) {
            $this->validate_unique_email();
        }
        
        if ($this->mode === 'add' || $this->has_changed('id_number')) {
            $this->validate_unique_id_number();
        }
        
        // Allow custom validation
        $this->errors = apply_filters(
            'wecoza_agents_form_validation', 
            $this->errors, 
            $this->data, 
            $this->mode
        );
        
        return empty($this->errors);
    }

    /**
     * Get validation rules
     *
     * @since 1.0.0
     * @return array Validation rules
     */
    protected function get_validation_rules() {
        $rules = ValidationHelper::get_agent_validation_rules();
        
        // Adjust rules based on ID type
        if (isset($this->data['id_type'])) {
            if ($this->data['id_type'] === 'passport') {
                $rules['passport_number'] = 'required|passport';
                unset($rules['id_number']);
            } else {
                $rules['id_number'] = 'required|sa_id';
                unset($rules['passport_number']);
            }
        }
        
        return $rules;
    }

    /**
     * Check if field has changed
     *
     * @since 1.0.0
     * @param string $field Field name
     * @return bool Whether field has changed
     */
    protected function has_changed($field) {
        if (!$this->agent) {
            return true;
        }
        
        return $this->agent->get($field) !== $this->data[$field];
    }

    /**
     * Validate unique email
     *
     * @since 1.0.0
     */
    protected function validate_unique_email() {
        if (empty($this->data['email_address'])) {
            return;
        }
        
        $agent_queries = new \WeCoza\Agents\Database\AgentQueries();
        $existing = $agent_queries->get_agent_by_email($this->data['email_address']);
        
        if ($existing && (!$this->agent || $existing['agent_id'] != $this->agent->id)) {
            $this->errors['email_address'] = __('This email address is already registered.', 'wecoza-agents-plugin');
        }
    }

    /**
     * Validate unique ID number
     *
     * @since 1.0.0
     */
    protected function validate_unique_id_number() {
        if (empty($this->data['sa_id_no'])) {
            return;
        }
        
        $agent_queries = new \WeCoza\Agents\Database\AgentQueries();
        $existing = $agent_queries->get_agent_by_id_number($this->data['sa_id_no']);
        
        if ($existing && (!$this->agent || $existing['agent_id'] != $this->agent->id)) {
            $this->errors['sa_id_no'] = __('This ID number is already registered.', 'wecoza-agents-plugin');
        }
    }

    /**
     * Save agent
     *
     * @since 1.0.0
     * @return int|false Agent ID or false on failure
     */
    protected function save() {
        if ($this->agent) {
            // Update existing agent
            $this->agent->set_data($this->data);
        } else {
            // Create new agent
            $this->agent = new Agent($this->data);
        }
        
        return $this->agent->save();
    }

    /**
     * Handle file uploads
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     */
    protected function handle_file_uploads($agent_id) {
        $upload_fields = array(
            'signed_agreement_file' => 'signed_agreement_file',
            'criminal_record_file' => 'criminal_record_file',
        );
        
        $uploaded_files = array();
        
        foreach ($upload_fields as $field => $db_field) {
            if (!empty($_FILES[$field]['name'])) {
                $file_path = $this->upload_file($field, $agent_id);
                if ($file_path) {
                    $uploaded_files[$db_field] = $file_path;
                }
            }
        }
        
        // Update agent with file paths
        if (!empty($uploaded_files)) {
            $agent_queries = new \WeCoza\Agents\Database\AgentQueries();
            
            // If we have criminal_record_file, update it directly
            if (isset($uploaded_files['criminal_record_file'])) {
                $agent_queries->update_agent($agent_id, array(
                    'criminal_record_file' => $uploaded_files['criminal_record_file']
                ));
            }
            
            // Store all uploaded files in the latest_document JSON column
            $agent_queries->update_agent($agent_id, array(
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
    protected function upload_file($field_name, $agent_id) {
        $file = $_FILES[$field_name];
        
        // Validate file
        $validated = wecoza_agents_sanitize_file_upload($file, array('pdf', 'doc', 'docx'));
        if (is_wp_error($validated)) {
            $this->errors[$field_name] = $validated->get_error_message();
            return false;
        }
        
        // Create upload directory
        $upload_dir = wp_upload_dir();
        $agents_dir = $upload_dir['basedir'] . '/agents';
        
        if (!file_exists($agents_dir)) {
            wp_mkdir_p($agents_dir);
        }
        
        // Generate unique filename with agent ID prefix
        $filename = sanitize_file_name(
            'agent-' . $agent_id . '-' . $field_name . '-' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION)
        );
        $file_path = $agents_dir . '/' . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Return relative path
            return str_replace($upload_dir['basedir'], '', $file_path);
        }
        
        return false;
    }

    /**
     * Get form errors
     *
     * @since 1.0.0
     * @return array Form errors
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * Get form data
     *
     * @since 1.0.0
     * @return array Form data
     */
    public function get_data() {
        return $this->data;
    }

    /**
     * Get agent
     *
     * @since 1.0.0
     * @return Agent|null Current agent
     */
    public function get_agent() {
        return $this->agent;
    }

    /**
     * Get form mode
     *
     * @since 1.0.0
     * @return string Form mode (add or edit)
     */
    public function get_mode() {
        return $this->mode;
    }
}