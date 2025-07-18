<?php
/**
 * Single Agent Shortcode
 *
 * Handles the single agent display shortcode.
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 */

namespace WeCoza\Agents\Shortcodes;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Single Agent Shortcode class
 *
 * Displays a single agent's complete information on a dedicated page.
 *
 * @since 1.0.0
 */
class SingleAgentShortcode extends AbstractShortcode {
    
    /**
     * Agent ID from URL parameter
     *
     * @var int
     */
    private $agent_id = 0;
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Initialize shortcode
     *
     * @since 1.0.0
     */
    protected function init() {
        $this->tag = 'wecoza_display_single_agent';
        $this->default_atts = array(
            'agent_id' => 0, // Can be overridden by URL parameter
        );
    }

    /**
     * Check permissions
     *
     * @since 1.0.0
     * @return bool
     */
    protected function check_permissions() {
        // Allow all users to view agent details for now
        // In the future, this could check for specific capabilities
        return true;
    }

    /**
     * Enqueue assets
     *
     * @since 1.0.0
     */
    protected function enqueue_assets() {
        parent::enqueue_assets();
        
        // Bootstrap is already loaded by parent theme
        // Additional assets can be added here if needed
    }

    /**
     * Get agent by ID
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID to retrieve
     * @return array|false Agent data or false if not found
     */
    private function get_agent_by_id($agent_id) {
        // Get all hardcoded agents
        $agents = $this->get_hardcoded_agents();
        
        // Search for the agent with matching ID
        foreach ($agents as $agent) {
            if (isset($agent['agent_id']) && (int)$agent['agent_id'] === (int)$agent_id) {
                return $agent;
            }
        }
        
        return false;
    }

    /**
     * Get hardcoded agents data
     *
     * @since 1.0.0
     * @return array Agents data
     */
    private function get_hardcoded_agents() {
        // Using the same data structure as DisplayAgentShortcode
        return array(
            array(
                'agent_id' => 1,
                'first_name' => 'Peter',
                'initials' => 'P.',
                'last_name' => 'Wessels',
                'gender' => 'Male',
                'race' => 'White',
                'phone' => '0123456789',
                'email' => 'peter.w@example.com',
                'city' => 'Cape Town',
                'status' => 'active',
                'sace_number' => 'SACE123456',
                'quantum_maths_score' => 85,
                'quantum_science_score' => 92,
                'signed_agreement' => true,
                'street_address' => '123 Main Street',
                'province' => 'Western Cape',
                'postal_code' => '8001',
                'id_type' => 'sa_id',
                'id_number' => '8501015800084',
                'highest_qualification' => 'Bachelor of Education',
                'criminal_record_checked' => true,
                'criminal_record_date' => '2023-06-15',
                'signed_agreement_date' => '2023-07-01',
                'bank_name' => 'FNB',
                'account_holder' => 'Peter Wessels',
                'account_number' => '62123456789',
                'branch_code' => '250655',
                'account_type' => 'Cheque',
                'date_loaded' => '2023-07-01',
                'created_at' => '2023-07-01',
                'updated_at' => '2023-07-15',
                'notes' => 'Experienced mathematics teacher',
            ),
            array(
                'agent_id' => 2,
                'first_name' => 'Sarah',
                'initials' => 'S.',
                'last_name' => 'Johnson',
                'gender' => 'Female',
                'race' => 'African',
                'phone' => '0987654321',
                'email' => 'sarah.j@example.com',
                'city' => 'Johannesburg',
                'status' => 'active',
                'sace_number' => 'SACE789012',
                'quantum_maths_score' => 0,
                'quantum_science_score' => 78,
                'signed_agreement' => true,
                'street_address' => '456 Oak Avenue',
                'province' => 'Gauteng',
                'postal_code' => '2001',
                'id_type' => 'sa_id',
                'id_number' => '9203150800084',
                'highest_qualification' => 'Honours in Science Education',
                'criminal_record_checked' => true,
                'criminal_record_date' => '2023-05-20',
                'signed_agreement_date' => '2023-06-15',
                'bank_name' => 'Standard Bank',
                'account_holder' => 'Sarah Johnson',
                'account_number' => '0123456789',
                'branch_code' => '051001',
                'account_type' => 'Savings',
                'date_loaded' => '2023-06-15',
                'created_at' => '2023-06-15',
                'updated_at' => '2023-06-20',
                'notes' => 'Specializes in physical sciences',
            ),
            array(
                'agent_id' => 3,
                'first_name' => 'David',
                'initials' => 'D.',
                'last_name' => 'Smith',
                'gender' => 'Male',
                'race' => 'Coloured',
                'phone' => '0212223344',
                'email' => 'david.s@example.com',
                'city' => 'Durban',
                'status' => 'active',
                'sace_number' => '',
                'quantum_maths_score' => 88,
                'quantum_science_score' => 0,
                'signed_agreement' => true,
                'street_address' => '789 Beach Road',
                'province' => 'KwaZulu-Natal',
                'postal_code' => '4001',
                'id_type' => 'passport',
                'passport_number' => 'A1234567',
                'highest_qualification' => 'Diploma in Education',
                'criminal_record_checked' => true,
                'criminal_record_date' => '2023-04-10',
                'signed_agreement_date' => '2023-05-01',
                'bank_name' => 'Capitec',
                'account_holder' => 'David Smith',
                'account_number' => '1234567890',
                'branch_code' => '470010',
                'account_type' => 'Savings',
                'date_loaded' => '2023-05-01',
                'created_at' => '2023-05-01',
                'updated_at' => '2023-05-15',
                'notes' => 'Strong mathematics background',
            ),
            array(
                'id' => 4,
                'first_name' => 'Maria',
                'initials' => 'M.',
                'last_name' => 'Garcia',
                'gender' => 'Female',
                'race' => 'Indian',
                'phone' => '0334455667',
                'email' => 'maria.g@example.com',
                'city' => 'Pretoria',
                'status' => 'active',
                'sace_number' => 'SACE345678',
                'quantum_maths_score' => 85,
                'quantum_science_score' => 92,
                'signed_agreement' => false,
                'street_address' => '321 Jacaranda Street',
                'province' => 'Gauteng',
                'postal_code' => '0001',
                'id_type' => 'sa_id',
                'id_number' => '8812150800084',
                'highest_qualification' => 'Master of Education',
                'criminal_record_checked' => true,
                'criminal_record_date' => '2023-03-25',
                'signed_agreement_date' => '',
                'bank_name' => 'ABSA',
                'account_holder' => 'Maria Garcia',
                'account_number' => '9876543210',
                'branch_code' => '632005',
                'account_type' => 'Cheque',
                'date_loaded' => '2023-04-01',
                'created_at' => '2023-04-01',
                'updated_at' => '2023-04-10',
                'notes' => 'Pending agreement signature',
            ),
            array(
                'id' => 5,
                'first_name' => 'John',
                'initials' => 'J.',
                'last_name' => 'Doe',
                'gender' => 'Male',
                'race' => 'White',
                'phone' => '0112233445',
                'email' => 'john.d@example.com',
                'city' => 'Bloemfontein',
                'status' => 'inactive',
                'sace_number' => 'SACE567890',
                'quantum_maths_score' => 0,
                'quantum_science_score' => 0,
                'signed_agreement' => true,
                'street_address' => '555 Rose Avenue',
                'province' => 'Free State',
                'postal_code' => '9301',
                'id_type' => 'sa_id',
                'id_number' => '7506150800084',
                'highest_qualification' => 'Bachelor of Arts in Teaching',
                'criminal_record_checked' => true,
                'criminal_record_date' => '2023-02-15',
                'signed_agreement_date' => '2023-03-01',
                'bank_name' => 'Nedbank',
                'account_holder' => 'John Doe',
                'account_number' => '1122334455',
                'branch_code' => '198765',
                'account_type' => 'Savings',
                'date_loaded' => '2023-03-01',
                'created_at' => '2023-03-01',
                'updated_at' => '2023-03-10',
                'notes' => 'Currently on leave',
            ),
            array(
                'id' => 6,
                'first_name' => 'Emily',
                'initials' => 'E.',
                'last_name' => 'Davis',
                'gender' => 'Female',
                'race' => 'African',
                'phone' => '0445566778',
                'email' => 'emily.d@example.com',
                'city' => 'Port Elizabeth',
                'status' => 'active',
                'sace_number' => 'SACE678901',
                'quantum_maths_score' => 0,
                'quantum_science_score' => 78,
                'signed_agreement' => true,
                'street_address' => '987 Marine Drive',
                'province' => 'Eastern Cape',
                'postal_code' => '6001',
                'id_type' => 'sa_id',
                'id_number' => '9105150800084',
                'highest_qualification' => 'PGCE',
                'criminal_record_checked' => true,
                'criminal_record_date' => '2023-01-20',
                'signed_agreement_date' => '2023-02-01',
                'bank_name' => 'FNB',
                'account_holder' => 'Emily Davis',
                'account_number' => '6677889900',
                'branch_code' => '250655',
                'account_type' => 'Cheque',
                'date_loaded' => '2023-02-01',
                'created_at' => '2023-02-01',
                'updated_at' => '2023-02-15',
                'notes' => 'Science specialist',
            ),
            array(
                'id' => 7,
                'first_name' => 'Michael',
                'initials' => 'M.',
                'last_name' => 'Brown',
                'gender' => 'Male',
                'race' => 'Coloured',
                'phone' => '0556677889',
                'email' => 'michael.b@example.com',
                'city' => 'East London',
                'status' => 'active',
                'sace_number' => '',
                'quantum_maths_score' => 88,
                'quantum_science_score' => 0,
                'signed_agreement' => true,
                'street_address' => '654 Sunset Boulevard',
                'province' => 'Eastern Cape',
                'postal_code' => '5201',
                'id_type' => 'sa_id',
                'id_number' => '8709150800084',
                'highest_qualification' => 'Bachelor of Education',
                'criminal_record_checked' => true,
                'criminal_record_date' => '2022-12-10',
                'signed_agreement_date' => '2023-01-15',
                'bank_name' => 'Capitec',
                'account_holder' => 'Michael Brown',
                'account_number' => '9988776655',
                'branch_code' => '470010',
                'account_type' => 'Savings',
                'date_loaded' => '2023-01-15',
                'created_at' => '2023-01-15',
                'updated_at' => '2023-01-20',
                'notes' => 'Mathematics tutor',
            ),
            array(
                'id' => 8,
                'first_name' => 'Linda',
                'initials' => 'L.',
                'last_name' => 'Taylor',
                'gender' => 'Female',
                'race' => 'Indian',
                'phone' => '0667788990',
                'email' => 'linda.t@example.com',
                'city' => 'Kimberley',
                'status' => 'active',
                'sace_number' => 'SACE890123',
                'quantum_maths_score' => 85,
                'quantum_science_score' => 92,
                'signed_agreement' => true,
                'street_address' => '111 Diamond Street',
                'province' => 'Northern Cape',
                'postal_code' => '8301',
                'id_type' => 'sa_id',
                'id_number' => '8403150800084',
                'highest_qualification' => 'Honours in Mathematics',
                'criminal_record_checked' => true,
                'criminal_record_date' => '2022-11-15',
                'signed_agreement_date' => '2022-12-01',
                'bank_name' => 'Standard Bank',
                'account_holder' => 'Linda Taylor',
                'account_number' => '5544332211',
                'branch_code' => '051001',
                'account_type' => 'Cheque',
                'date_loaded' => '2022-12-01',
                'created_at' => '2022-12-01',
                'updated_at' => '2022-12-15',
                'notes' => 'Excellent track record',
            ),
            array(
                'id' => 9,
                'first_name' => 'Robert',
                'initials' => 'R.',
                'last_name' => 'Wilson',
                'gender' => 'Male',
                'race' => 'White',
                'phone' => '0778899001',
                'email' => 'robert.w@example.com',
                'city' => 'Polokwane',
                'status' => 'active',
                'sace_number' => 'SACE901234',
                'quantum_maths_score' => 0,
                'quantum_science_score' => 0,
                'signed_agreement' => true,
                'street_address' => '222 Savanna Road',
                'province' => 'Limpopo',
                'postal_code' => '0699',
                'id_type' => 'sa_id',
                'id_number' => '7908150800084',
                'highest_qualification' => 'Diploma in Primary Education',
                'criminal_record_checked' => true,
                'criminal_record_date' => '2022-10-20',
                'signed_agreement_date' => '2022-11-01',
                'bank_name' => 'ABSA',
                'account_holder' => 'Robert Wilson',
                'account_number' => '1122554433',
                'branch_code' => '632005',
                'account_type' => 'Savings',
                'date_loaded' => '2022-11-01',
                'created_at' => '2022-11-01',
                'updated_at' => '2022-11-10',
                'notes' => 'Primary school specialist',
            ),
            array(
                'id' => 10,
                'first_name' => 'Jessica',
                'initials' => 'J.',
                'last_name' => 'Lee',
                'gender' => 'Female',
                'race' => 'African',
                'phone' => '0889900112',
                'email' => 'jessica.l@example.com',
                'city' => 'Nelspruit',
                'status' => 'active',
                'sace_number' => 'SACE012345',
                'quantum_maths_score' => 88,
                'quantum_science_score' => 0,
                'signed_agreement' => true,
                'street_address' => '333 Lowveld Lane',
                'province' => 'Mpumalanga',
                'postal_code' => '1200',
                'id_type' => 'sa_id',
                'id_number' => '9412150800084',
                'highest_qualification' => 'Bachelor of Education',
                'criminal_record_checked' => true,
                'criminal_record_date' => '2022-09-15',
                'signed_agreement_date' => '2022-10-01',
                'bank_name' => 'Nedbank',
                'account_holder' => 'Jessica Lee',
                'account_number' => '9988112233',
                'branch_code' => '198765',
                'account_type' => 'Cheque',
                'date_loaded' => '2022-10-01',
                'created_at' => '2022-10-01',
                'updated_at' => '2022-10-15',
                'notes' => 'Enthusiastic educator',
            ),
        );
    }

    /**
     * Render shortcode content
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes
     * @param string $content Shortcode content
     */
    protected function render_shortcode($atts, $content) {
        // Sub-task 1.9: Add URL parameter handling
        // Get agent_id from URL parameter if not provided in shortcode attributes
        $agent_id = $atts['agent_id'];
        if (empty($agent_id)) {
            $agent_id = $this->get_request_param('agent_id', 0, 'GET');
        }
        
        // Validate agent ID
        $agent_id = intval($agent_id);
        
        // Sub-task 1.11: Pass all required variables to template using load_template() method
        // Prepare template variables
        $template_args = array(
            'agent_id' => $agent_id,
            'agent' => false,
            'error' => false,
            'loading' => false,
            'back_url' => $this->get_back_url(),
            'can_manage' => $this->can_manage_agents(),
            'date_format' => get_option('date_format'),
        );
        
        // Check if agent ID is valid
        if (empty($agent_id)) {
            $template_args['error'] = __('Invalid agent ID. Please provide a valid agent ID.', 'wecoza-agents-plugin');
        } else {
            // Get agent data
            $agent = $this->get_agent_by_id($agent_id);
            
            if ($agent === false) {
                $template_args['error'] = sprintf(
                    __('Agent with ID %d not found.', 'wecoza-agents-plugin'),
                    $agent_id
                );
            } else {
                $template_args['agent'] = $agent;
            }
        }
        
        // Load the template
        $this->load_template('agent-single-display.php', $template_args, 'display');
    }
    
    /**
     * Get back URL to agents list
     *
     * @since 1.0.0
     * @return string Back URL
     */
    private function get_back_url() {
        // Sub-task 1.10: Create get_back_url() method
        // Try to get the referrer first
        $referrer = wp_get_referer();
        
        // If referrer exists and is from the same site, use it
        if ($referrer && strpos($referrer, home_url()) === 0) {
            // Check if referrer is the agents list page (contains our display shortcode)
            // This ensures we go back to the correct page with filters/search preserved
            if (strpos($referrer, 'wecoza_display_agents') !== false || 
                strpos($referrer, 'agents') !== false ||
                strpos($referrer, 'agent-list') !== false) {
                return $referrer;
            }
        }
        
        // Default to home page with /app/agents/ path
        // In production, this should be configurable or use a settings page
        return home_url('/app/agents/');
    }
}