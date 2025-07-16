<?php
/**
 * Agent Capture Form Template
 *
 * This template displays the agent capture/edit form.
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 *
 * @var array $agent Current agent data (if editing)
 * @var array $errors Form validation errors
 * @var string $mode Form mode ('add' or 'edit')
 * @var array $atts Shortcode attributes
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Helper function to get field value
if (!function_exists('wecoza_agents_get_field_value')) {
    function wecoza_agents_get_field_value($agent, $field, $default = '') {
        if (isset($_POST[$field])) {
            return esc_attr($_POST[$field]);
        }
        if (isset($agent[$field])) {
            return esc_attr($agent[$field]);
        }
        return esc_attr($default);
    }
}

// Helper function to get error class
if (!function_exists('wecoza_agents_get_error_class')) {
    function wecoza_agents_get_error_class($errors, $field) {
        return isset($errors[$field]) ? 'is-invalid' : '';
    }
}

// Helper function to display field error
if (!function_exists('wecoza_agents_display_field_error')) {
    function wecoza_agents_display_field_error($errors, $field) {
        if (isset($errors[$field])) {
            echo '<div class="invalid-feedback">' . esc_html($errors[$field]) . '</div>';
        }
    }
}
?>

<form id="agents-form" class="needs-validation ydcoza-compact-form" method="POST" enctype="multipart/form-data" novalidate>
    <?php wp_nonce_field('submit_agent_form', 'wecoza_agents_form_nonce'); ?>
    
    <!-- Agent ID (read-only if editing) -->
    <?php if ($mode === 'edit' && !empty($agent['id'])) : ?>
    <div class="row">
        <div class="col-md-3">
            <label for="agent_id" class="form-label">Agent ID</label>
            <input type="text" id="agent_id" name="agent_id" class="form-control form-control-sm" value="<?php echo esc_attr($agent['id']); ?>" readonly>
        </div>
    </div>
    <?php endif; ?>

    <!-- Personal Information Section -->
    <div class="row">
        <!-- Title -->
        <div class="col-md-2">
            <label for="title" class="form-label">Title</label>
            <select id="title" name="title" class="form-select form-select-sm <?php echo wecoza_agents_get_error_class($errors, 'title'); ?>">
                <option value="">Select</option>
                <option value="Mr" <?php selected(wecoza_agents_get_field_value($agent, 'title'), 'Mr'); ?>>Mr</option>
                <option value="Mrs" <?php selected(wecoza_agents_get_field_value($agent, 'title'), 'Mrs'); ?>>Mrs</option>
                <option value="Ms" <?php selected(wecoza_agents_get_field_value($agent, 'title'), 'Ms'); ?>>Ms</option>
                <option value="Miss" <?php selected(wecoza_agents_get_field_value($agent, 'title'), 'Miss'); ?>>Miss</option>
                <option value="Dr" <?php selected(wecoza_agents_get_field_value($agent, 'title'), 'Dr'); ?>>Dr</option>
                <option value="Prof" <?php selected(wecoza_agents_get_field_value($agent, 'title'), 'Prof'); ?>>Prof</option>
            </select>
            <?php wecoza_agents_display_field_error($errors, 'title'); ?>
        </div>
        
        <!-- First Name -->
        <div class="col-md-3">
            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
            <input type="text" id="first_name" name="first_name" class="form-control form-control-sm <?php echo wecoza_agents_get_error_class($errors, 'first_name'); ?>" 
                   value="<?php echo wecoza_agents_get_field_value($agent, 'first_name'); ?>" required>
            <div class="invalid-feedback">Please provide the first name.</div>
            <?php wecoza_agents_display_field_error($errors, 'first_name'); ?>
        </div>
        
        <!-- Surname -->
        <div class="col-md-3">
            <label for="surname" class="form-label">Surname <span class="text-danger">*</span></label>
            <input type="text" id="surname" name="surname" class="form-control form-control-sm <?php echo wecoza_agents_get_error_class($errors, 'surname'); ?>" 
                   value="<?php echo wecoza_agents_get_field_value($agent, 'last_name'); ?>" required>
            <div class="invalid-feedback">Please provide the surname.</div>
            <?php wecoza_agents_display_field_error($errors, 'surname'); ?>
        </div>
        
        <!-- Known As -->
        <div class="col-md-2">
            <label for="known_as" class="form-label">Known As</label>
            <input type="text" id="known_as" name="known_as" class="form-control form-control-sm" 
                   value="<?php echo wecoza_agents_get_field_value($agent, 'known_as'); ?>">
        </div>
        
        <!-- Gender -->
        <div class="col-md-2">
            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
            <select id="gender" name="gender" class="form-select form-select-sm <?php echo wecoza_agents_get_error_class($errors, 'gender'); ?>" required>
                <option value="">Select</option>
                <option value="M" <?php selected(wecoza_agents_get_field_value($agent, 'gender'), 'M'); ?>>Male</option>
                <option value="F" <?php selected(wecoza_agents_get_field_value($agent, 'gender'), 'F'); ?>>Female</option>
            </select>
            <div class="invalid-feedback">Please select your gender.</div>
            <?php wecoza_agents_display_field_error($errors, 'gender'); ?>
        </div>
    </div>

    <div class="row mt-3">
        <!-- Race -->
        <div class="col-md-3">
            <label for="race" class="form-label">Race <span class="text-danger">*</span></label>
            <select id="race" name="race" class="form-select form-select-sm <?php echo wecoza_agents_get_error_class($errors, 'race'); ?>" required>
                <option value="">Select</option>
                <option value="African" <?php selected(wecoza_agents_get_field_value($agent, 'race'), 'African'); ?>>African</option>
                <option value="Coloured" <?php selected(wecoza_agents_get_field_value($agent, 'race'), 'Coloured'); ?>>Coloured</option>
                <option value="White" <?php selected(wecoza_agents_get_field_value($agent, 'race'), 'White'); ?>>White</option>
                <option value="Indian" <?php selected(wecoza_agents_get_field_value($agent, 'race'), 'Indian'); ?>>Indian</option>
            </select>
            <div class="invalid-feedback">Please select your race.</div>
            <?php wecoza_agents_display_field_error($errors, 'race'); ?>
        </div>
    </div>

    <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>

    <!-- Identification Section -->
    <div class="row">
        <div class="col-md-3">
            <!-- Radio buttons for ID or Passport selection -->
            <div class="mb-1">
                <label class="form-label">Identification Type <span class="text-danger">*</span></label>
                <div class="row">
                    <div class="col">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="id_type" id="sa_id_option" value="sa_id" 
                                   <?php checked(wecoza_agents_get_field_value($agent, 'id_type', 'sa_id'), 'sa_id'); ?> required>
                            <label class="form-check-label" for="sa_id_option">SA ID</label>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="id_type" id="passport_option" value="passport" 
                                   <?php checked(wecoza_agents_get_field_value($agent, 'id_type'), 'passport'); ?> required>
                            <label class="form-check-label" for="passport_option">Passport</label>
                        </div>
                    </div>
                </div>
                <div class="invalid-feedback">Please select an identification type.</div>
            </div>
        </div>
        
        <div class="col-md-3">
            <!-- SA ID Number -->
            <div id="sa_id_field" class="mb-3 <?php echo wecoza_agents_get_field_value($agent, 'id_type', 'sa_id') !== 'sa_id' ? 'd-none' : ''; ?>">
                <label for="sa_id_no" class="form-label">SA ID Number <span class="text-danger">*</span></label>
                <input type="text" id="sa_id_no" name="sa_id_no" 
                       class="form-control form-control-sm <?php echo wecoza_agents_get_error_class($errors, 'sa_id_no'); ?>" 
                       value="<?php echo wecoza_agents_get_field_value($agent, 'id_number'); ?>" maxlength="13">
                <div class="invalid-feedback">Please provide a valid SA ID number.</div>
                <?php wecoza_agents_display_field_error($errors, 'sa_id_no'); ?>
            </div>
            
            <!-- Passport Number -->
            <div id="passport_field" class="mb-3 <?php echo wecoza_agents_get_field_value($agent, 'id_type') !== 'passport' ? 'd-none' : ''; ?>">
                <label for="passport_number" class="form-label">Passport Number <span class="text-danger">*</span></label>
                <input type="text" id="passport_number" name="passport_number" 
                       class="form-control form-control-sm <?php echo wecoza_agents_get_error_class($errors, 'passport_number'); ?>" 
                       value="<?php echo wecoza_agents_get_field_value($agent, 'passport_number'); ?>" maxlength="12">
                <div class="invalid-feedback">Please provide a valid passport number.</div>
                <?php wecoza_agents_display_field_error($errors, 'passport_number'); ?>
            </div>
        </div>
        
        <!-- Telephone Number -->
        <div class="col-md-3">
            <label for="tel_number" class="form-label">Telephone Number <span class="text-danger">*</span></label>
            <input type="text" id="tel_number" name="tel_number" 
                   class="form-control form-control-sm <?php echo wecoza_agents_get_error_class($errors, 'tel_number'); ?>" 
                   value="<?php echo wecoza_agents_get_field_value($agent, 'phone'); ?>" required>
            <div class="invalid-feedback">Please provide a telephone number.</div>
            <?php wecoza_agents_display_field_error($errors, 'tel_number'); ?>
        </div>
        
        <!-- Email -->
        <div class="col-md-3">
            <label for="email_address" class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" id="email_address" name="email_address" 
                   class="form-control form-control-sm <?php echo wecoza_agents_get_error_class($errors, 'email_address'); ?>" 
                   value="<?php echo wecoza_agents_get_field_value($agent, 'email'); ?>" required>
            <div class="invalid-feedback">Please provide a valid email address.</div>
            <?php wecoza_agents_display_field_error($errors, 'email_address'); ?>
        </div>
    </div>

    <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>

    <!-- Address Section -->
    <div class="row">
        <div class="col-md-6">
            <label for="address_line_1" class="form-label">Address Line 1 <span class="text-danger">*</span></label>
            <input type="text" id="address_line_1" name="address_line_1" 
                   class="form-control form-control-sm <?php echo wecoza_agents_get_error_class($errors, 'address_line_1'); ?>" 
                   value="<?php echo wecoza_agents_get_field_value($agent, 'street_address'); ?>" required>
            <div class="invalid-feedback">Please provide Address Line 1.</div>
            <?php wecoza_agents_display_field_error($errors, 'address_line_1'); ?>
        </div>
        
        <div class="col-md-6">
            <label for="address_line_2" class="form-label">Address Line 2</label>
            <input type="text" id="address_line_2" name="address_line_2" class="form-control form-control-sm" 
                   value="<?php echo wecoza_agents_get_field_value($agent, 'address_line_2'); ?>">
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <label for="city_town" class="form-label">City/Town <span class="text-danger">*</span></label>
            <input type="text" id="city_town" name="city_town" 
                   class="form-control form-control-sm <?php echo wecoza_agents_get_error_class($errors, 'city_town'); ?>" 
                   value="<?php echo wecoza_agents_get_field_value($agent, 'city'); ?>" required>
            <div class="invalid-feedback">Please provide a city or town.</div>
            <?php wecoza_agents_display_field_error($errors, 'city_town'); ?>
        </div>
        
        <div class="col-md-4">
            <label for="province_region" class="form-label">Province/Region <span class="text-danger">*</span></label>
            <select id="province_region" name="province_region" 
                    class="form-select form-select-sm <?php echo wecoza_agents_get_error_class($errors, 'province_region'); ?>" required>
                <option value="">Select</option>
                <option value="Eastern Cape" <?php selected(wecoza_agents_get_field_value($agent, 'province'), 'Eastern Cape'); ?>>Eastern Cape</option>
                <option value="Free State" <?php selected(wecoza_agents_get_field_value($agent, 'province'), 'Free State'); ?>>Free State</option>
                <option value="Gauteng" <?php selected(wecoza_agents_get_field_value($agent, 'province'), 'Gauteng'); ?>>Gauteng</option>
                <option value="KwaZulu-Natal" <?php selected(wecoza_agents_get_field_value($agent, 'province'), 'KwaZulu-Natal'); ?>>KwaZulu-Natal</option>
                <option value="Limpopo" <?php selected(wecoza_agents_get_field_value($agent, 'province'), 'Limpopo'); ?>>Limpopo</option>
                <option value="Mpumalanga" <?php selected(wecoza_agents_get_field_value($agent, 'province'), 'Mpumalanga'); ?>>Mpumalanga</option>
                <option value="Northern Cape" <?php selected(wecoza_agents_get_field_value($agent, 'province'), 'Northern Cape'); ?>>Northern Cape</option>
                <option value="North West" <?php selected(wecoza_agents_get_field_value($agent, 'province'), 'North West'); ?>>North West</option>
                <option value="Western Cape" <?php selected(wecoza_agents_get_field_value($agent, 'province'), 'Western Cape'); ?>>Western Cape</option>
            </select>
            <div class="invalid-feedback">Please select a province or region.</div>
            <?php wecoza_agents_display_field_error($errors, 'province_region'); ?>
        </div>
        
        <div class="col-md-4">
            <label for="postal_code" class="form-label">Postal Code <span class="text-danger">*</span></label>
            <input type="text" id="postal_code" name="postal_code" 
                   class="form-control form-control-sm <?php echo wecoza_agents_get_error_class($errors, 'postal_code'); ?>" 
                   value="<?php echo wecoza_agents_get_field_value($agent, 'postal_code'); ?>" required>
            <div class="invalid-feedback">Please provide a postal code.</div>
            <?php wecoza_agents_display_field_error($errors, 'postal_code'); ?>
        </div>
    </div>

    <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>

    <!-- Preferred Working Areas Section -->
    <div class="row">
        <?php 
        // Get stored preferred areas
        $preferred_areas = isset($agent['preferred_areas']) ? json_decode($agent['preferred_areas'], true) : array();
        if (!is_array($preferred_areas)) {
            $preferred_areas = array();
        }
        
        // Working areas options
        $working_areas = array(
            '1' => 'Sandton, Johannesburg, Gauteng, 2196',
            '2' => 'Durbanville, Cape Town, Western Cape, 7551',
            '3' => 'Durban, Durban, KwaZulu-Natal, 4320',
            '4' => 'Hatfield, Pretoria, Gauteng, 0028',
            '5' => 'Stellenbosch, Stellenbosch, Western Cape, 7600',
            '6' => 'Polokwane, Polokwane, Limpopo, 0699',
            '7' => 'Kimberley, Kimberley, Northern Cape, 8301',
            '8' => 'Nelspruit, Mbombela, Mpumalanga, 1200',
            '9' => 'Bloemfontein, Bloemfontein, Free State, 9300',
            '10' => 'Port Elizabeth, Gqeberha, Eastern Cape, 6001',
            '11' => 'Soweto, Johannesburg, Gauteng, 1804',
            '12' => 'Paarl, Paarl, Western Cape, 7620',
            '13' => 'Pietermaritzburg, Pietermaritzburg, KwaZulu-Natal, 3201',
            '14' => 'East London, East London, Eastern Cape, 5201',
        );
        
        for ($i = 1; $i <= 3; $i++) : 
            $field_name = "preferred_working_area_$i";
            $selected_value = isset($_POST[$field_name]) ? $_POST[$field_name] : (isset($preferred_areas[$i-1]) ? $preferred_areas[$i-1] : '');
        ?>
        <div class="col-md-4">
            <label for="<?php echo $field_name; ?>" class="form-label">
                Preferred Working Area <?php echo $i; ?> <?php if ($i === 1) : ?><span class="text-danger">*</span><?php endif; ?>
            </label>
            <select id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" 
                    class="form-select form-select-sm <?php echo wecoza_agents_get_error_class($errors, $field_name); ?>" 
                    <?php echo $i === 1 ? 'required' : ''; ?>>
                <option value="">Select</option>
                <?php foreach ($working_areas as $value => $label) : ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php selected($selected_value, $value); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($i === 1) : ?>
            <div class="invalid-feedback">Please select a preferred working area.</div>
            <?php endif; ?>
            <?php wecoza_agents_display_field_error($errors, $field_name); ?>
        </div>
        <?php endfor; ?>
    </div>

    <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>

    <!-- SACE Registration Section -->
    <div class="row">
        <div class="col-md-3">
            <label for="sace_number" class="form-label">SACE Registration Number</label>
            <input type="text" id="sace_number" name="sace_number" class="form-control form-control-sm" 
                   value="<?php echo wecoza_agents_get_field_value($agent, 'sace_number'); ?>">
        </div>
        
        <div class="col-md-3">
            <label for="phase_registered" class="form-label">Phase Registered</label>
            <select id="phase_registered" name="phase_registered" class="form-select form-select-sm">
                <option value="">Select</option>
                <option value="Foundation" <?php selected(wecoza_agents_get_field_value($agent, 'phase_registered'), 'Foundation'); ?>>Foundation Phase</option>
                <option value="Intermediate" <?php selected(wecoza_agents_get_field_value($agent, 'phase_registered'), 'Intermediate'); ?>>Intermediate Phase</option>
                <option value="Senior" <?php selected(wecoza_agents_get_field_value($agent, 'phase_registered'), 'Senior'); ?>>Senior Phase</option>
                <option value="FET" <?php selected(wecoza_agents_get_field_value($agent, 'phase_registered'), 'FET'); ?>>FET Phase</option>
            </select>
        </div>
        
        <div class="col-md-6">
            <label for="subjects_registered" class="form-label">Subjects Registered</label>
            <input type="text" id="subjects_registered" name="subjects_registered" class="form-control form-control-sm" 
                   value="<?php echo wecoza_agents_get_field_value($agent, 'subjects_registered'); ?>" 
                   placeholder="e.g., Mathematics, Science, English">
        </div>
    </div>

    <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>

    <!-- Quantum Tests Section -->
    <div class="row">
        <div class="col-md-6">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="quantum_maths_passed" name="quantum_maths_passed" value="1" 
                       <?php checked(wecoza_agents_get_field_value($agent, 'quantum_maths_passed'), '1'); ?>>
                <label class="form-check-label" for="quantum_maths_passed">
                    Quantum Mathematics Test Passed
                </label>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="quantum_science_passed" name="quantum_science_passed" value="1" 
                       <?php checked(wecoza_agents_get_field_value($agent, 'quantum_science_passed'), '1'); ?>>
                <label class="form-check-label" for="quantum_science_passed">
                    Quantum Science Test Passed
                </label>
            </div>
        </div>
    </div>

    <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>

    <!-- Criminal Record Check Section -->
    <div class="row">
        <div class="col-md-6">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="criminal_record_checked" name="criminal_record_checked" value="1" 
                       <?php checked(wecoza_agents_get_field_value($agent, 'criminal_record_checked'), '1'); ?>>
                <label class="form-check-label" for="criminal_record_checked">
                    Criminal Record Check Completed
                </label>
            </div>
        </div>
        
        <div class="col-md-3">
            <label for="criminal_record_date" class="form-label">Criminal Record Check Date</label>
            <input type="date" id="criminal_record_date" name="criminal_record_date" class="form-control form-control-sm" 
                   value="<?php echo wecoza_agents_get_field_value($agent, 'criminal_record_date'); ?>">
        </div>
        
        <div class="col-md-3">
            <label for="criminal_record_file" class="form-label">Upload Criminal Record</label>
            <input type="file" id="criminal_record_file" name="criminal_record_file" class="form-control form-control-sm" 
                   accept=".pdf,.doc,.docx">
            <?php if (!empty($agent['criminal_record_file'])) : ?>
            <small class="text-muted">Current file uploaded</small>
            <?php endif; ?>
        </div>
    </div>

    <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>

    <!-- Agreement Section -->
    <div class="row">
        <div class="col-md-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="signed_agreement" name="signed_agreement" value="1" 
                       <?php checked(wecoza_agents_get_field_value($agent, 'signed_agreement'), '1'); ?>>
                <label class="form-check-label" for="signed_agreement">
                    Agent Agreement Signed
                </label>
            </div>
        </div>
        
        <div class="col-md-4">
            <label for="signed_agreement_date" class="form-label">Agreement Signed Date</label>
            <input type="date" id="signed_agreement_date" name="signed_agreement_date" class="form-control form-control-sm" 
                   value="<?php echo wecoza_agents_get_field_value($agent, 'signed_agreement_date'); ?>">
        </div>
        
        <div class="col-md-4">
            <label for="signed_agreement_file" class="form-label">Upload Signed Agreement</label>
            <input type="file" id="signed_agreement_file" name="signed_agreement_file" class="form-control form-control-sm" 
                   accept=".pdf,.doc,.docx">
            <?php if (!empty($agent['agreement_file_path'])) : ?>
            <small class="text-muted">Current file uploaded</small>
            <?php endif; ?>
        </div>
    </div>

    <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>

    <!-- Banking Details Section -->
    <h5 class="mb-3">Banking Details</h5>
    <div class="row">
        <div class="col-md-3">
            <label for="bank_name" class="form-label">Bank Name</label>
            <input type="text" id="bank_name" name="bank_name" class="form-control form-control-sm" 
                   value="<?php echo wecoza_agents_get_field_value($agent, 'bank_name'); ?>">
        </div>
        
        <div class="col-md-3">
            <label for="account_holder" class="form-label">Account Holder Name</label>
            <input type="text" id="account_holder" name="account_holder" class="form-control form-control-sm" 
                   value="<?php echo wecoza_agents_get_field_value($agent, 'account_holder'); ?>">
        </div>
        
        <div class="col-md-3">
            <label for="account_number" class="form-label">Account Number</label>
            <input type="text" id="account_number" name="account_number" class="form-control form-control-sm" 
                   value="<?php echo wecoza_agents_get_field_value($agent, 'account_number'); ?>">
        </div>
        
        <div class="col-md-3">
            <label for="branch_code" class="form-label">Branch Code</label>
            <input type="text" id="branch_code" name="branch_code" class="form-control form-control-sm" 
                   value="<?php echo wecoza_agents_get_field_value($agent, 'branch_code'); ?>">
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-3">
            <label for="account_type" class="form-label">Account Type</label>
            <select id="account_type" name="account_type" class="form-select form-select-sm">
                <option value="">Select</option>
                <option value="Savings" <?php selected(wecoza_agents_get_field_value($agent, 'account_type'), 'Savings'); ?>>Savings</option>
                <option value="Current" <?php selected(wecoza_agents_get_field_value($agent, 'account_type'), 'Current'); ?>>Current/Cheque</option>
                <option value="Transmission" <?php selected(wecoza_agents_get_field_value($agent, 'account_type'), 'Transmission'); ?>>Transmission</option>
            </select>
        </div>
    </div>

    <div class="border-top border-opacity-25 border-3 border-discovery my-5 mx-1"></div>

    <!-- Submit Button -->
    <div class="row">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary mt-3">
                <?php echo $mode === 'edit' ? 'Update Agent' : 'Add New Agent'; ?>
            </button>
            <?php if (!empty($atts['redirect_after_save'])) : ?>
            <a href="<?php echo esc_url($atts['redirect_after_save']); ?>" class="btn btn-secondary mt-3">Cancel</a>
            <?php endif; ?>
        </div>
    </div>
</form>

<script>
// ID Type Toggle
jQuery(document).ready(function($) {
    // Handle ID type radio button changes
    $('input[name="id_type"]').on('change', function() {
        if ($(this).val() === 'sa_id') {
            $('#sa_id_field').removeClass('d-none');
            $('#passport_field').addClass('d-none');
            $('#sa_id_no').prop('required', true);
            $('#passport_number').prop('required', false).val('');
        } else {
            $('#sa_id_field').addClass('d-none');
            $('#passport_field').removeClass('d-none');
            $('#sa_id_no').prop('required', false).val('');
            $('#passport_number').prop('required', true);
        }
    });
    
    // Initialize Select2 for preferred working areas if available
    if ($.fn.select2) {
        $('[id^="preferred_working_area_"]').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Select a location'
        });
    }
    
    // Bootstrap form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            // Custom validation for ID fields
            var idType = $('input[name="id_type"]:checked').val();
            if (idType === 'sa_id') {
                var saId = $('#sa_id_no').val();
                if (!saId || saId.length !== 13) {
                    $('#sa_id_no')[0].setCustomValidity('Invalid');
                } else {
                    $('#sa_id_no')[0].setCustomValidity('');
                }
            } else if (idType === 'passport') {
                var passport = $('#passport_number').val();
                if (!passport || passport.length < 6) {
                    $('#passport_number')[0].setCustomValidity('Invalid');
                } else {
                    $('#passport_number')[0].setCustomValidity('');
                }
            }
            
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
});
</script>