<?php
/**
 * Agent Modal Template
 *
 * This template displays the agent details in a modal popup.
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Agent Details Modal -->
<div id="agentModal" class="modal fade" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable modal-xl modal-fullscreen-xxl-down">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h6 class="modal-title" id="modalTitle"><?php esc_html_e('Agent Details', 'wecoza-agents-plugin'); ?></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php esc_attr_e('Close', 'wecoza-agents-plugin'); ?>"></button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body ydcoza-compact-content" id="modalContent">
                <!-- Loading indicator -->
                <div class="text-center py-5 modal-loading">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden"><?php esc_html_e('Loading...', 'wecoza-agents-plugin'); ?></span>
                    </div>
                </div>
                
                <!-- Agent details will be loaded here via AJAX -->
                <div class="agent-details-content" style="display: none;">
                    <!-- TOP SUMMARY ROW -->
                    <div class="container-fluid lh-1 mb-2">
                        <div class="row border">
                            <div class="col-1 p-2 text-black fw-medium border-end"><?php esc_html_e('First Name', 'wecoza-agents-plugin'); ?></div>
                            <div class="col-1 p-2 text-black fw-medium border-end"><?php esc_html_e('Initials', 'wecoza-agents-plugin'); ?></div>
                            <div class="col-1 p-2 text-black fw-medium border-end"><?php esc_html_e('Surname', 'wecoza-agents-plugin'); ?></div>
                            <div class="col-1 p-2 text-black fw-medium border-end"><?php esc_html_e('Gender', 'wecoza-agents-plugin'); ?></div>
                            <div class="col-1 p-2 text-black fw-medium border-end"><?php esc_html_e('Race', 'wecoza-agents-plugin'); ?></div>
                            <div class="col-2 p-2 text-black fw-medium border-end"><?php esc_html_e('Tel Number', 'wecoza-agents-plugin'); ?></div>
                            <div class="col-2 p-2 text-black fw-medium border-end"><?php esc_html_e('Email Address', 'wecoza-agents-plugin'); ?></div>
                            <div class="col-1 p-2 text-black fw-medium border-end"><?php esc_html_e('City/Town', 'wecoza-agents-plugin'); ?></div>
                            <div class="col-2 p-2 text-black fw-medium"><?php esc_html_e('Actions', 'wecoza-agents-plugin'); ?></div>
                        </div>
                        <div class="row border border-top-0 agent-summary-row">
                            <!-- Summary data will be populated via JavaScript -->
                        </div>
                    </div>
                    
                    <!-- TABBED SECTIONS -->
                    <div class="gtabs ydcoza-tab mb-3">
                        <!-- Tab Buttons -->
                        <div class="ydcoza-tab-buttons mb-2">
                            <button data-toggle="tab" data-tabs=".gtabs.ydcoza-tab" data-tab=".tab-1" class="active">
                                <span class="ydcoza-badge"><?php esc_html_e('Agent Info.', 'wecoza-agents-plugin'); ?></span>
                            </button>
                            <button data-toggle="tab" data-tabs=".gtabs.ydcoza-tab" data-tab=".tab-2">
                                <span class="ydcoza-badge"><?php esc_html_e('Identification & Contact', 'wecoza-agents-plugin'); ?></span>
                            </button>
                            <button data-toggle="tab" data-tabs=".gtabs.ydcoza-tab" data-tab=".tab-3">
                                <span class="ydcoza-badge"><?php esc_html_e('Current Status', 'wecoza-agents-plugin'); ?></span>
                            </button>
                            <button data-toggle="tab" data-tabs=".gtabs.ydcoza-tab" data-tab=".tab-4">
                                <span class="ydcoza-badge"><?php esc_html_e('Progression History', 'wecoza-agents-plugin'); ?></span>
                            </button>
                        </div>
                        <div class="clearfix"></div>
                        
                        <!-- TAB 1: Agent Info -->
                        <div class="container-fluid gtab tab-1 border-top border-bottom lh-1 mb-2 active">
                            <!-- Row 1 -->
                            <div class="row border-start">
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Agent ID', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-id">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Highest Qualification', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-qualification">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Date Loaded', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-date-loaded">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Agent Notes', 'wecoza-agents-plugin'); ?></div>
                                <div class="col p-2 d-flex align-items-center agent-field-notes">-</div>
                            </div>
                            <!-- Row 2 -->
                            <div class="row border-top border-start">
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('SACE Reg. Number', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-sace-number">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Reg Date', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-sace-reg-date">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Expiry Date', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-sace-expiry-date">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Training Date', 'wecoza-agents-plugin'); ?></div>
                                <div class="col p-2 d-flex align-items-center agent-field-training-date">-</div>
                            </div>
                            <!-- Row 3 -->
                            <div class="row border-top border-start">
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Quantum (Comm)', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-quantum-comm">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Quantum (Math)', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-quantum-math">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Quantum (Training)', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-quantum-training">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Signed Agreement', 'wecoza-agents-plugin'); ?></div>
                                <div class="col p-2 d-flex align-items-center agent-field-signed-agreement">-</div>
                            </div>
                            <!-- Row 4 -->
                            <div class="row border-top border-start">
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Signed Agreement Date', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-agreement-date">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Bank Name', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-bank-name">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Branch Code', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-branch-code">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Account Number', 'wecoza-agents-plugin'); ?></div>
                                <div class="col p-2 d-flex align-items-center agent-field-account-number">-</div>
                            </div>
                        </div>
                        
                        <!-- TAB 2: Identification & Contact -->
                        <div class="container-fluid gtab tab-2 border-top border-bottom lh-1 mb-2">
                            <!-- Row 1 -->
                            <div class="row border-start">
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('SA ID No', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-id-number">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Passport No', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-passport">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Tel Number', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-phone">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Email Address', 'wecoza-agents-plugin'); ?></div>
                                <div class="col p-2 d-flex align-items-center agent-field-email">-</div>
                            </div>
                            <!-- Row 2: Address -->
                            <div class="row border-top border-start">
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Address Line 1', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-address1">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Suburb', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-suburb">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Town', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-city">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Postal Code', 'wecoza-agents-plugin'); ?></div>
                                <div class="col p-2 d-flex align-items-center agent-field-postal-code">-</div>
                            </div>
                            <!-- Row 3: Preferred Working Areas -->
                            <div class="row border-top border-start">
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Preferred Area 1', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-area1">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Preferred Area 2', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-area2">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Preferred Area 3', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-area3">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center">&nbsp;</div>
                                <div class="col p-2 d-flex align-items-center">&nbsp;</div>
                            </div>
                        </div>
                        
                        <!-- TAB 3: Current Status -->
                        <div class="container-fluid gtab tab-3 border-top border-bottom lh-1 mb-2">
                            <!-- Row 1: Class Info -->
                            <div class="row border-start">
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Agent Absent', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-absent">N</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Agent Backup', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-backup">Y</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Agent Replacement', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-replacement">N</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Original Agent', 'wecoza-agents-plugin'); ?></div>
                                <div class="col p-2 d-flex align-items-center agent-field-original">Y</div>
                            </div>
                            <!-- Row 2: Class Order Info -->
                            <div class="row border-top border-start">
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Agent Order Number', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-order-number">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Class Time', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-class-time">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Class Days', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-class-days">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Agent order hours', 'wecoza-agents-plugin'); ?></div>
                                <div class="col p-2 d-flex align-items-center agent-field-order-hours">-</div>
                            </div>
                            <!-- Row 3: Additional Class Info -->
                            <div class="row border-top border-start">
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Agent Class Allocation', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-class-allocation">-</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('QA Reports', 'wecoza-agents-plugin'); ?></div>
                                <div class="col border-end p-2 d-flex align-items-center agent-field-qa-reports">None</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center">&nbsp;</div>
                                <div class="col border-end p-2 d-flex align-items-center">&nbsp;</div>
                                <div class="col border-end p-2 bg-light d-flex align-items-center">&nbsp;</div>
                                <div class="col p-2 d-flex align-items-center">
                                    <button class="btn btn-sm btn-outline-discovery" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasHistory" aria-controls="offcanvasHistory">
                                        <?php esc_html_e('History', 'wecoza-agents-plugin'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- TAB 4: Progressions -->
                        <div class="container-fluid gtab tab-4 border-top border-bottom mb-2 lh-1">
                            <div class="accordion accordion-flush ml-0 mr-2" style="margin:0 -11px" id="accordionProducts">
                                <!-- Training modules will be populated dynamically -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed btn btn-light border-start border-end" style="background-color: #6e5dc6; color: white" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#update-1" aria-expanded="false">
                                            <?php esc_html_e('Update Agents Progression', 'wecoza-agents-plugin'); ?>
                                        </button>
                                    </h2>
                                    <div id="update-1" class="accordion-collapse collapse" data-bs-parent="#accordionProducts">
                                        <div class="accordion-body pb-0">
                                            <div class="container-fluid">
                                                <form id="agents-progression-form" class="needs-validation ydcoza-compact-form" novalidate method="POST" enctype="multipart/form-data">
                                                    <div class="row border">
                                                        <div class="col-2 border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Agent product trained start date', 'wecoza-agents-plugin'); ?></div>
                                                        <div class="col-4 border-end p-2 d-flex align-items-center">
                                                            <input type="date" class="form-control" id="startDateAETComm1" name="startDateAETComm1" />
                                                        </div>
                                                        <div class="col-2 border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Agent product trained end date', 'wecoza-agents-plugin'); ?></div>
                                                        <div class="col-4 p-2 d-flex align-items-center">
                                                            <input type="date" class="form-control" id="endDateAETComm1" name="endDateAETComm1" />
                                                        </div>
                                                    </div>
                                                    <div class="row border border-top-0">
                                                        <div class="col-2 border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Select Training Module', 'wecoza-agents-plugin'); ?></div>
                                                        <div class="col-4 border-end p-2 d-flex align-items-center">
                                                            <select class="form-select" id="productSelect" name="productSelect">
                                                                <option value="" disabled selected><?php esc_html_e('-- Select an option --', 'wecoza-agents-plugin'); ?></option>
                                                                <?php 
                                                                $training_modules = wecoza_agents_get_training_modules();
                                                                foreach ($training_modules as $value => $label) : 
                                                                ?>
                                                                <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-2 border-end p-2 bg-light d-flex align-items-center"><?php esc_html_e('Comments / Notes', 'wecoza-agents-plugin'); ?></div>
                                                        <div class="col-4 p-2 d-flex align-items-center">
                                                            <textarea class="form-control" id="commentsAETComm1" name="commentsAETComm1" rows="1"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-3 mb-3">
                                                        <div class="col">
                                                            <button type="submit" class="btn btn-primary"><?php esc_html_e('Update Profile', 'wecoza-agents-plugin'); ?></button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Tab switching functionality
jQuery(document).ready(function($) {
    // Tab switching
    $(document).on('click', '[data-toggle="tab"]', function(e) {
        e.preventDefault();
        var $this = $(this);
        var tabsSelector = $this.data('tabs');
        var tabSelector = $this.data('tab');
        
        // Remove active from all tabs and buttons
        $(tabsSelector).find('.gtab').removeClass('active');
        $(tabsSelector).find('[data-toggle="tab"]').removeClass('active');
        
        // Add active to clicked button and corresponding tab
        $this.addClass('active');
        $(tabsSelector).find(tabSelector).addClass('active');
    });
});
</script>