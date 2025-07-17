<?php

namespace WeCoza\Agents\Helpers;

/**
 * FormHelpers
 * 
 * Centralized form helper methods for template rendering
 */
class FormHelpers {
    
    /**
     * Get field value from agent data with fallback to default
     * 
     * @param array $agent Agent data array
     * @param string $field Field name
     * @param string $default Default value if field not found
     * @return string Field value or default
     */
    public static function get_field_value(array $agent, string $field, string $default = ''): string {
        return isset($agent[$field]) ? esc_attr($agent[$field]) : $default;
    }
    
    /**
     * Get error CSS class for field
     * 
     * @param array $errors Errors array
     * @param string $field Field name
     * @return string CSS class for error state
     */
    public static function get_error_class(array $errors, string $field): string {
        return isset($errors[$field]) ? 'is-invalid' : '';
    }
    
    /**
     * Display field error message
     * 
     * @param array $errors Errors array
     * @param string $field Field name
     * @return void
     */
    public static function display_field_error(array $errors, string $field): void {
        if (isset($errors[$field])) {
            echo '<div class="invalid-feedback">' . esc_html($errors[$field]) . '</div>';
        }
    }
}