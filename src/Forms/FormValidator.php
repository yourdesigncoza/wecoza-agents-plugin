<?php
/**
 * Form Validator
 *
 * Centralized form validation for all plugin forms.
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 */

namespace WeCoza\Agents\Forms;

use WeCoza\Agents\Helpers\ValidationHelper;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Form Validator class
 *
 * @since 1.0.0
 */
class FormValidator {

    /**
     * Form type
     *
     * @var string
     */
    protected $form_type = '';

    /**
     * Form data
     *
     * @var array
     */
    protected $data = array();

    /**
     * Validation errors
     *
     * @var array
     */
    protected $errors = array();

    /**
     * Validation rules
     *
     * @var array
     */
    protected $rules = array();

    /**
     * Field labels
     *
     * @var array
     */
    protected $labels = array();

    /**
     * Constructor
     *
     * @since 1.0.0
     * @param string $form_type Form type
     * @param array $data Form data
     */
    public function __construct($form_type = '', $data = array()) {
        $this->form_type = $form_type;
        $this->data = $data;
        $this->setup_rules();
        $this->setup_labels();
    }

    /**
     * Setup validation rules based on form type
     *
     * @since 1.0.0
     */
    protected function setup_rules() {
        switch ($this->form_type) {
            case 'agent_capture':
                $this->rules = $this->get_agent_capture_rules();
                break;
                
            case 'agent_search':
                $this->rules = $this->get_agent_search_rules();
                break;
                
            case 'agent_import':
                $this->rules = $this->get_agent_import_rules();
                break;
                
            case 'progression_update':
                $this->rules = $this->get_progression_update_rules();
                break;
                
            default:
                $this->rules = array();
                break;
        }
        
        // Allow filtering of rules
        $this->rules = apply_filters(
            'wecoza_agents_form_validation_rules',
            $this->rules,
            $this->form_type,
            $this->data
        );
    }

    /**
     * Setup field labels based on form type
     *
     * @since 1.0.0
     */
    protected function setup_labels() {
        // Get default labels from ValidationHelper
        $this->labels = ValidationHelper::get_field_names();
        
        // Add form-specific labels
        switch ($this->form_type) {
            case 'agent_capture':
                $this->labels = array_merge($this->labels, array(
                    'title' => __('Title', 'wecoza-agents-plugin'),
                    'initials' => __('Initials', 'wecoza-agents-plugin'),
                    'highest_qualification' => __('Highest qualification', 'wecoza-agents-plugin'),
                    'quantum_maths_passed' => __('Quantum mathematics test', 'wecoza-agents-plugin'),
                    'quantum_science_passed' => __('Quantum science test', 'wecoza-agents-plugin'),
                    'criminal_record_checked' => __('Criminal record check', 'wecoza-agents-plugin'),
                    'signed_agreement' => __('Agent agreement', 'wecoza-agents-plugin'),
                ));
                break;
                
            case 'progression_update':
                $this->labels = array_merge($this->labels, array(
                    'start_date' => __('Start date', 'wecoza-agents-plugin'),
                    'end_date' => __('End date', 'wecoza-agents-plugin'),
                    'training_module' => __('Training module', 'wecoza-agents-plugin'),
                    'comments' => __('Comments', 'wecoza-agents-plugin'),
                ));
                break;
        }
        
        // Allow filtering of labels
        $this->labels = apply_filters(
            'wecoza_agents_form_field_labels',
            $this->labels,
            $this->form_type
        );
    }

    /**
     * Get agent capture form validation rules
     *
     * @since 1.0.0
     * @return array Validation rules
     */
    protected function get_agent_capture_rules() {
        $rules = ValidationHelper::get_agent_validation_rules();
        
        // Add additional rules for capture form
        $rules['title'] = 'in:Mr,Mrs,Ms,Miss,Dr,Prof';
        $rules['initials'] = 'alpha|max_length:10';
        $rules['highest_qualification'] = 'max_length:100';
        
        // Date fields
        $rules['criminal_record_date'] = 'date';
        $rules['signed_agreement_date'] = 'date';
        $rules['agent_training_date'] = 'date';
        
        // Quantum test scores
        $rules['quantum_communications'] = 'numeric|min:0|max:100';
        $rules['quantum_mathematics'] = 'numeric|min:0|max:100';
        $rules['quantum_training'] = 'numeric|min:0|max:100';
        
        return $rules;
    }

    /**
     * Get agent search form validation rules
     *
     * @since 1.0.0
     * @return array Validation rules
     */
    protected function get_agent_search_rules() {
        return array(
            'search_query' => 'max_length:100',
            'search_field' => 'in:all,name,email,phone,id_number',
            'status' => 'in:all,active,inactive,suspended',
            'province' => 'max_length:50',
            'date_from' => 'date',
            'date_to' => 'date',
        );
    }

    /**
     * Get agent import form validation rules
     *
     * @since 1.0.0
     * @return array Validation rules
     */
    protected function get_agent_import_rules() {
        return array(
            'import_file' => 'required',
            'file_type' => 'required|in:csv,xlsx',
            'update_existing' => 'in:0,1',
            'skip_validation' => 'in:0,1',
        );
    }

    /**
     * Get progression update form validation rules
     *
     * @since 1.0.0
     * @return array Validation rules
     */
    protected function get_progression_update_rules() {
        return array(
            'agent_id' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'training_module' => 'required',
            'comments' => 'max_length:500',
        );
    }

    /**
     * Validate form data
     *
     * @since 1.0.0
     * @return bool Whether validation passed
     */
    public function validate() {
        // Clear previous errors
        $this->errors = array();
        
        // Validate using ValidationHelper
        $this->errors = ValidationHelper::validate_fields(
            $this->data,
            $this->rules,
            $this->labels
        );
        
        // Additional custom validation
        $this->custom_validation();
        
        // Allow filtering of errors
        $this->errors = apply_filters(
            'wecoza_agents_form_validation_errors',
            $this->errors,
            $this->form_type,
            $this->data
        );
        
        return empty($this->errors);
    }

    /**
     * Perform custom validation based on form type
     *
     * @since 1.0.0
     */
    protected function custom_validation() {
        switch ($this->form_type) {
            case 'agent_capture':
                $this->validate_agent_capture();
                break;
                
            case 'agent_search':
                $this->validate_agent_search();
                break;
                
            case 'progression_update':
                $this->validate_progression_update();
                break;
        }
    }

    /**
     * Custom validation for agent capture form
     *
     * @since 1.0.0
     */
    protected function validate_agent_capture() {
        // Validate ID type consistency
        if (isset($this->data['id_type'])) {
            if ($this->data['id_type'] === 'passport') {
                if (empty($this->data['passport_number']) && !isset($this->errors['passport_number'])) {
                    $this->errors['passport_number'] = __('Passport number is required.', 'wecoza-agents-plugin');
                }
            } else {
                if (empty($this->data['id_number']) && !isset($this->errors['id_number'])) {
                    $this->errors['id_number'] = __('SA ID number is required.', 'wecoza-agents-plugin');
                }
            }
        }
        
        // Validate banking details completeness
        $bank_fields = array('bank_name', 'account_holder', 'account_number', 'branch_code');
        $has_bank_info = false;
        $missing_bank_fields = array();
        
        foreach ($bank_fields as $field) {
            if (!empty($this->data[$field])) {
                $has_bank_info = true;
            } elseif ($has_bank_info) {
                $missing_bank_fields[] = $field;
            }
        }
        
        if ($has_bank_info && !empty($missing_bank_fields)) {
            foreach ($missing_bank_fields as $field) {
                if (!isset($this->errors[$field])) {
                    $this->errors[$field] = sprintf(
                        __('%s is required when providing banking details.', 'wecoza-agents-plugin'),
                        $this->labels[$field] ?? $field
                    );
                }
            }
        }
        
        // Validate date relationships
        if (!empty($this->data['signed_agreement']) && empty($this->data['signed_agreement_date'])) {
            $this->errors['signed_agreement_date'] = __('Agreement date is required when agreement is signed.', 'wecoza-agents-plugin');
        }
        
        if (!empty($this->data['criminal_record_checked']) && empty($this->data['criminal_record_date'])) {
            $this->errors['criminal_record_date'] = __('Criminal record check date is required.', 'wecoza-agents-plugin');
        }
    }

    /**
     * Custom validation for agent search form
     *
     * @since 1.0.0
     */
    protected function validate_agent_search() {
        // Validate date range
        if (!empty($this->data['date_from']) && !empty($this->data['date_to'])) {
            $from = strtotime($this->data['date_from']);
            $to = strtotime($this->data['date_to']);
            
            if ($from > $to) {
                $this->errors['date_to'] = __('End date must be after start date.', 'wecoza-agents-plugin');
            }
        }
    }

    /**
     * Custom validation for progression update form
     *
     * @since 1.0.0
     */
    protected function validate_progression_update() {
        // Validate date range
        if (!empty($this->data['start_date']) && !empty($this->data['end_date'])) {
            $start = strtotime($this->data['start_date']);
            $end = strtotime($this->data['end_date']);
            
            if ($start > $end) {
                $this->errors['end_date'] = __('End date must be after start date.', 'wecoza-agents-plugin');
            }
            
            // Check if dates are not in the future
            if ($end > time()) {
                $this->errors['end_date'] = __('End date cannot be in the future.', 'wecoza-agents-plugin');
            }
        }
        
        // Validate training module exists
        if (!empty($this->data['training_module'])) {
            $modules = wecoza_agents_get_training_modules();
            if (!isset($modules[$this->data['training_module']])) {
                $this->errors['training_module'] = __('Invalid training module selected.', 'wecoza-agents-plugin');
            }
        }
    }

    /**
     * Get validation errors
     *
     * @since 1.0.0
     * @return array Validation errors
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * Get specific error
     *
     * @since 1.0.0
     * @param string $field Field name
     * @return string|null Error message or null
     */
    public function get_error($field) {
        return isset($this->errors[$field]) ? $this->errors[$field] : null;
    }

    /**
     * Has error for field
     *
     * @since 1.0.0
     * @param string $field Field name
     * @return bool Whether field has error
     */
    public function has_error($field) {
        return isset($this->errors[$field]);
    }

    /**
     * Add error
     *
     * @since 1.0.0
     * @param string $field Field name
     * @param string $message Error message
     */
    public function add_error($field, $message) {
        $this->errors[$field] = $message;
    }

    /**
     * Get all errors as HTML
     *
     * @since 1.0.0
     * @param string $format Format string with %s placeholders
     * @return string HTML string
     */
    public function get_errors_html($format = '<div class="error">%s</div>') {
        $html = '';
        
        foreach ($this->errors as $field => $message) {
            $html .= sprintf($format, esc_html($message));
        }
        
        return $html;
    }

    /**
     * Get errors as JSON
     *
     * @since 1.0.0
     * @return string JSON string
     */
    public function get_errors_json() {
        return json_encode($this->errors);
    }

    /**
     * Set form type
     *
     * @since 1.0.0
     * @param string $form_type Form type
     */
    public function set_form_type($form_type) {
        $this->form_type = $form_type;
        $this->setup_rules();
        $this->setup_labels();
    }

    /**
     * Set form data
     *
     * @since 1.0.0
     * @param array $data Form data
     */
    public function set_data($data) {
        $this->data = $data;
    }

    /**
     * Set custom rules
     *
     * @since 1.0.0
     * @param array $rules Validation rules
     */
    public function set_rules($rules) {
        $this->rules = $rules;
    }

    /**
     * Set custom labels
     *
     * @since 1.0.0
     * @param array $labels Field labels
     */
    public function set_labels($labels) {
        $this->labels = array_merge($this->labels, $labels);
    }
}