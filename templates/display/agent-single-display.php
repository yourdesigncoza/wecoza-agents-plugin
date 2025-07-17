<?php
/**
 * Agent Single Display Template - Modern Layout
 *
 * This template displays a single agent's complete information on a dedicated page
 * using a modern, clean design inspired by the Phoenix design system.
 *
 * Available variables:
 * - $agent_id (int): The ID of the agent being displayed
 * - $agent (array|false): Agent data array or false if not found
 * - $error (string|false): Error message if any
 * - $loading (bool): Whether to show loading state
 * - $back_url (string): URL to return to agents list
 * - $can_manage (bool): Whether current user can manage agents
 * - $date_format (string): WordPress date format setting
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wecoza-single-agent-display">
    
    <?php // Loading state ?>
    <?php if ($loading) : ?>
        <div class="agent-loading-state text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden"><?php esc_html_e('Loading...', 'wecoza-agents-plugin'); ?></span>
            </div>
            <p class="mt-3 text-muted"><?php esc_html_e('Loading agent details...', 'wecoza-agents-plugin'); ?></p>
        </div>
    <?php endif; ?>
    
    <?php // Error state ?>
    <?php if ($error) : ?>
        <div class="agent-error-state">
            <div class="alert alert-danger d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                <div>
                    <h6 class="alert-heading mb-1"><?php esc_html_e('Error Loading Agent', 'wecoza-agents-plugin'); ?></h6>
                    <p class="mb-0"><?php echo esc_html($error); ?></p>
                </div>
            </div>
            <div class="mt-3">
                <a href="<?php echo esc_url($back_url); ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>
                    <?php esc_html_e('Back to Agents', 'wecoza-agents-plugin'); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($agent && !$error && !$loading) : ?>
        
        <?php // Action Buttons ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="<?php echo esc_url($back_url); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                <?php esc_html_e('Back to Agents', 'wecoza-agents-plugin'); ?>
            </a>
            <?php if ($can_manage) : ?>
            <div>
                <button class="btn btn-phoenix-secondary px-3 px-sm-5 me-2">
                    <i class="bi bi-pencil-square me-sm-2"></i>
                    <span class="d-none d-sm-inline"><?php esc_html_e('Edit', 'wecoza-agents-plugin'); ?></span>
                </button>
                <button class="btn btn-phoenix-danger">
                    <i class="bi bi-trash me-2"></i>
                    <span><?php esc_html_e('Delete', 'wecoza-agents-plugin'); ?></span>
                </button>
            </div>
            <?php endif; ?>
        </div>
        
        <?php // Top Summary Cards ?>
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-4 justify-content-between">
                    <!-- Name Card -->
                    <div class="col-sm-auto">
                        <div class="d-flex align-items-center">
                            <div class="d-flex bg-primary-subtle rounded flex-center me-3" style="width:32px; height:32px">
                                <i class="bi bi-person text-primary"></i>
                            </div>
                            <div>
                                <p class="fw-bold mb-1"><?php esc_html_e('Agent Name', 'wecoza-agents-plugin'); ?></p>
                                <h5 class="fw-bolder text-nowrap">
                                    <?php echo esc_html($agent['first_name'] . ' ' . $agent['last_name']); ?>
                                </h5>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ID Type Card -->
                    <div class="col-sm-auto">
                        <div class="d-flex align-items-center border-start-sm ps-sm-5">
                            <div class="d-flex bg-info-subtle rounded flex-center me-3" style="width:32px; height:32px">
                                <i class="bi bi-credit-card-2-front text-info"></i>
                            </div>
                            <div>
                                <p class="fw-bold mb-1"><?php esc_html_e('ID Type', 'wecoza-agents-plugin'); ?></p>
                                <h5 class="fw-bolder text-nowrap">
                                    <?php 
                                    if ($agent['id_type'] === 'sa_id') {
                                        esc_html_e('SA ID', 'wecoza-agents-plugin');
                                    } else {
                                        esc_html_e('Passport', 'wecoza-agents-plugin');
                                    }
                                    ?>
                                </h5>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Card -->
                    <div class="col-sm-auto">
                        <div class="d-flex align-items-center border-start-sm ps-sm-5">
                            <div class="d-flex bg-success-subtle rounded flex-center me-3" style="width:32px; height:32px">
                                <i class="bi bi-toggle-on text-success"></i>
                            </div>
                            <div>
                                <p class="fw-bold mb-1"><?php esc_html_e('Status', 'wecoza-agents-plugin'); ?></p>
                                <h5 class="fw-bolder text-nowrap">
                                    <?php
                                    $status_class = ($agent['status'] === 'active') ? 'success' : 'secondary';
                                    $status_text = ($agent['status'] === 'active') ? __('Active', 'wecoza-agents-plugin') : __('Inactive', 'wecoza-agents-plugin');
                                    ?>
                                    <span class="badge bg-<?php echo esc_attr($status_class); ?>">
                                        <?php echo esc_html($status_text); ?>
                                    </span>
                                </h5>
                            </div>
                        </div>
                    </div>
                    
                    <!-- SACE Card -->
                    <div class="col-sm-auto">
                        <div class="d-flex align-items-center border-start-sm ps-sm-5">
                            <div class="d-flex bg-warning-subtle rounded flex-center me-3" style="width:32px; height:32px">
                                <i class="bi bi-award text-warning"></i>
                            </div>
                            <div>
                                <p class="fw-bold mb-1"><?php esc_html_e('SACE Registration', 'wecoza-agents-plugin'); ?></p>
                                <h5 class="fw-bolder text-nowrap">
                                    <?php if (!empty($agent['sace_number'])) : ?>
                                        <span class="text-success"><?php echo esc_html($agent['sace_number']); ?></span>
                                    <?php else : ?>
                                        <span class="text-muted"><?php esc_html_e('Not Registered', 'wecoza-agents-plugin'); ?></span>
                                    <?php endif; ?>
                                </h5>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Card -->
                    <div class="col-sm-auto">
                        <div class="d-flex align-items-center border-start-sm ps-sm-5">
                            <div class="d-flex bg-primary-subtle rounded flex-center me-3" style="width:32px; height:32px">
                                <i class="bi bi-telephone text-primary"></i>
                            </div>
                            <div>
                                <p class="fw-bold mb-1"><?php esc_html_e('Contact', 'wecoza-agents-plugin'); ?></p>
                                <h5 class="fw-bolder text-nowrap">
                                    <a href="tel:<?php echo esc_attr($agent['phone']); ?>" class="text-decoration-none">
                                        <?php echo esc_html($agent['phone']); ?>
                                    </a>
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php // Details Tables ?>
        <div class="px-xl-4 mb-7">
            <div class="row mx-0">
                <!-- Left Column - Personal Information -->
                <div class="col-sm-12 col-xxl-6 border-bottom border-end-xxl py-3">
                    <table class="w-100 table-stats table table-hover table-sm fs-9 mb-0">
                        <tbody>
                            <tr>
                                <td class="py-2 ydcoza-w-150">
                                    <div class="d-inline-flex align-items-center">
                                        <div class="d-flex bg-primary-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                            <i class="bi bi-hash text-primary" style="font-size: 12px;"></i>
                                        </div>
                                        <p class="fw-bold mb-0"><?php esc_html_e('Agent ID :', 'wecoza-agents-plugin'); ?></p>
                                    </div>
                                </td>
                                <td class="py-2">
                                    <p class="fw-semibold mb-0">#<?php echo esc_html($agent['id']); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <td class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex bg-info-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                            <i class="bi bi-person-circle text-info" style="font-size: 12px;"></i>
                                        </div>
                                        <p class="fw-bold mb-0"><?php esc_html_e('Full Name :', 'wecoza-agents-plugin'); ?></p>
                                    </div>
                                </td>
                                <td class="py-2">
                                    <p class="fw-semibold mb-0"><?php echo esc_html($agent['first_name'] . ' ' . $agent['last_name']); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <td class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex bg-primary-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                            <i class="bi bi-people text-primary" style="font-size: 12px;"></i>
                                        </div>
                                        <p class="fw-bold mb-0"><?php esc_html_e('Gender :', 'wecoza-agents-plugin'); ?></p>
                                    </div>
                                </td>
                                <td class="py-2">
                                    <p class="fw-semibold mb-0"><?php echo esc_html($agent['gender']); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <td class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex bg-success-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                            <i class="bi bi-globe text-success" style="font-size: 12px;"></i>
                                        </div>
                                        <p class="fw-bold mb-0"><?php esc_html_e('Race :', 'wecoza-agents-plugin'); ?></p>
                                    </div>
                                </td>
                                <td class="py-2">
                                    <p class="fw-semibold mb-0"><?php echo esc_html($agent['race']); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <td class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex bg-info-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                            <i class="bi bi-credit-card text-info" style="font-size: 12px;"></i>
                                        </div>
                                        <p class="fw-bold mb-0"><?php esc_html_e('ID Number :', 'wecoza-agents-plugin'); ?></p>
                                    </div>
                                </td>
                                <td class="py-2">
                                    <p class="fw-semibold mb-0">
                                        <?php 
                                        if ($agent['id_type'] === 'sa_id' && !empty($agent['id_number'])) {
                                            echo esc_html($agent['id_number']);
                                        } elseif (isset($agent['passport_number'])) {
                                            echo esc_html($agent['passport_number']);
                                        } else {
                                            echo '<span class="text-muted">' . esc_html__('Not provided', 'wecoza-agents-plugin') . '</span>';
                                        }
                                        ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <td class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex bg-primary-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                            <i class="bi bi-telephone text-primary" style="font-size: 12px;"></i>
                                        </div>
                                        <p class="fw-bold mb-0"><?php esc_html_e('Phone :', 'wecoza-agents-plugin'); ?></p>
                                    </div>
                                </td>
                                <td class="py-2">
                                    <p class="fw-semibold mb-0">
                                        <a href="tel:<?php echo esc_attr($agent['phone']); ?>" class="text-decoration-none">
                                            <?php echo esc_html($agent['phone']); ?>
                                        </a>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <td class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex bg-info-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                            <i class="bi bi-envelope text-info" style="font-size: 12px;"></i>
                                        </div>
                                        <p class="fw-bold mb-0"><?php esc_html_e('Email :', 'wecoza-agents-plugin'); ?></p>
                                    </div>
                                </td>
                                <td class="py-2">
                                    <p class="fw-semibold mb-0">
                                        <a href="mailto:<?php echo esc_attr($agent['email']); ?>" class="text-decoration-none">
                                            <?php echo esc_html($agent['email']); ?>
                                        </a>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <td class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex bg-success-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                            <i class="bi bi-geo-alt text-success" style="font-size: 12px;"></i>
                                        </div>
                                        <p class="fw-bold mb-0"><?php esc_html_e('Address :', 'wecoza-agents-plugin'); ?></p>
                                    </div>
                                </td>
                                <td class="py-2">
                                    <div class="fw-semibold mb-0">
                                        <?php echo esc_html($agent['street_address']); ?><br>
                                        <?php echo esc_html($agent['city'] . ', ' . $agent['province'] . ', ' . $agent['postal_code']); ?>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Right Column - Professional & Compliance -->
                <div class="col-sm-12 col-xxl-6 border-bottom py-3">
                    <table class="w-100 table-stats table table-hover table-sm fs-9 mb-0">
                        <tbody>
                            <tr>
                                <td class="py-2 ydcoza-w-150">
                                    <div class="d-inline-flex align-items-center">
                                        <div class="d-flex bg-warning-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                            <i class="bi bi-award text-warning" style="font-size: 12px;"></i>
                                        </div>
                                        <p class="fw-bold mb-0"><?php esc_html_e('SACE Number :', 'wecoza-agents-plugin'); ?></p>
                                    </div>
                                </td>
                                <td class="py-2">
                                    <p class="fw-semibold mb-0">
                                        <?php if (!empty($agent['sace_number'])) : ?>
                                            <?php echo esc_html($agent['sace_number']); ?>
                                        <?php else : ?>
                                            <span class="text-muted"><?php esc_html_e('Not registered', 'wecoza-agents-plugin'); ?></span>
                                        <?php endif; ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <td class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex bg-primary-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                            <i class="bi bi-mortarboard text-primary" style="font-size: 12px;"></i>
                                        </div>
                                        <p class="fw-bold mb-0"><?php esc_html_e('Qualification :', 'wecoza-agents-plugin'); ?></p>
                                    </div>
                                </td>
                                <td class="py-2">
                                    <p class="fw-semibold mb-0"><?php echo esc_html($agent['highest_qualification']); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <td class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex bg-success-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                            <i class="bi bi-check-circle text-success" style="font-size: 12px;"></i>
                                        </div>
                                        <p class="fw-bold mb-0"><?php esc_html_e('Quantum Tests :', 'wecoza-agents-plugin'); ?></p>
                                    </div>
                                </td>
                                <td class="py-2">
                                    <div class="fw-semibold mb-0">
                                        <?php if ($agent['quantum_maths_passed']) : ?>
                                            <span class="badge badge-phoenix fs-10 badge-phoenix-success me-1">
                                                <i class="bi bi-check me-1"></i><?php esc_html_e('Maths', 'wecoza-agents-plugin'); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($agent['quantum_science_passed']) : ?>
                                            <span class="badge badge-phoenix fs-10 badge-phoenix-success">
                                                <i class="bi bi-check me-1"></i><?php esc_html_e('Science', 'wecoza-agents-plugin'); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!$agent['quantum_maths_passed'] && !$agent['quantum_science_passed']) : ?>
                                            <span class="text-muted"><?php esc_html_e('None passed', 'wecoza-agents-plugin'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr>
                                <td class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex bg-warning-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                            <i class="bi bi-file-earmark-text text-warning" style="font-size: 12px;"></i>
                                        </div>
                                        <p class="fw-bold mb-0"><?php esc_html_e('Agreement :', 'wecoza-agents-plugin'); ?></p>
                                    </div>
                                </td>
                                <td class="py-2">
                                    <div class="fw-semibold mb-0">
                                        <?php if ($agent['signed_agreement']) : ?>
                                            <span class="text-success">
                                                <i class="bi bi-check-circle me-1"></i><?php esc_html_e('Signed', 'wecoza-agents-plugin'); ?>
                                            </span>
                                            <?php if (!empty($agent['signed_agreement_date'])) : ?>
                                                <div class="fs-9 text-muted mt-1">
                                                    <?php echo esc_html(date_i18n($date_format, strtotime($agent['signed_agreement_date']))); ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else : ?>
                                            <span class="text-danger">
                                                <i class="bi bi-x-circle me-1"></i><?php esc_html_e('Not Signed', 'wecoza-agents-plugin'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr>
                                <td class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex bg-info-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                            <i class="bi bi-bank text-info" style="font-size: 12px;"></i>
                                        </div>
                                        <p class="fw-bold mb-0"><?php esc_html_e('Banking :', 'wecoza-agents-plugin'); ?></p>
                                    </div>
                                </td>
                                <td class="py-2">
                                    <div class="fw-semibold mb-0">
                                        <?php if (!empty($agent['bank_name']) && !empty($agent['account_number'])) : ?>
                                            <?php echo esc_html($agent['bank_name']); ?>
                                            <div class="fs-9 text-muted">
                                                <?php echo esc_html($agent['account_type'] . ' - ' . substr($agent['account_number'], -4)); ?>
                                            </div>
                                        <?php else : ?>
                                            <span class="text-warning">
                                                <i class="bi bi-exclamation-circle me-1"></i><?php esc_html_e('Incomplete', 'wecoza-agents-plugin'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr>
                                <td class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex bg-success-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                            <i class="bi bi-shield-check text-success" style="font-size: 12px;"></i>
                                        </div>
                                        <p class="fw-bold mb-0"><?php esc_html_e('Criminal Record :', 'wecoza-agents-plugin'); ?></p>
                                    </div>
                                </td>
                                <td class="py-2">
                                    <div class="fw-semibold mb-0">
                                        <?php if ($agent['criminal_record_checked']) : ?>
                                            <span class="text-success">
                                                <i class="bi bi-check-circle me-1"></i><?php esc_html_e('Checked', 'wecoza-agents-plugin'); ?>
                                            </span>
                                            <?php if (!empty($agent['criminal_record_date'])) : ?>
                                                <div class="fs-9 text-muted mt-1">
                                                    <?php echo esc_html(date_i18n($date_format, strtotime($agent['criminal_record_date']))); ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else : ?>
                                            <span class="text-danger">
                                                <i class="bi bi-x-circle me-1"></i><?php esc_html_e('Pending', 'wecoza-agents-plugin'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr>
                                <td class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex bg-secondary-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                            <i class="bi bi-calendar-check text-secondary" style="font-size: 12px;"></i>
                                        </div>
                                        <p class="fw-bold mb-0"><?php esc_html_e('Created :', 'wecoza-agents-plugin'); ?></p>
                                    </div>
                                </td>
                                <td class="py-2">
                                    <p class="fw-semibold mb-0">
                                        <?php 
                                        if (!empty($agent['created_at'])) {
                                            echo esc_html(date_i18n($date_format . ' ' . get_option('time_format'), strtotime($agent['created_at'])));
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                        ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <td class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex bg-primary-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                            <i class="bi bi-calendar-event text-primary" style="font-size: 12px;"></i>
                                        </div>
                                        <p class="fw-bold mb-0"><?php esc_html_e('Last Updated :', 'wecoza-agents-plugin'); ?></p>
                                    </div>
                                </td>
                                <td class="py-2">
                                    <p class="fw-semibold mb-0">
                                        <?php 
                                        if (!empty($agent['updated_at'])) {
                                            echo esc_html(date_i18n($date_format . ' ' . get_option('time_format'), strtotime($agent['updated_at'])));
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                        ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <?php if (!empty($agent['notes'])) : ?>
                            <tr>
                                <td class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex bg-info-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                            <i class="bi bi-sticky text-info" style="font-size: 12px;"></i>
                                        </div>
                                        <p class="fw-bold mb-0"><?php esc_html_e('Notes :', 'wecoza-agents-plugin'); ?></p>
                                    </div>
                                </td>
                                <td class="py-2">
                                    <div class="fw-semibold mb-0 text-wrap">
                                        <?php echo esc_html($agent['notes']); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    <?php endif; ?>
    
</div>