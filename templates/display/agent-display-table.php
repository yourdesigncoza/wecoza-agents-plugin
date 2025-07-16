<?php
/**
 * Agent Display Table Template
 *
 * This template displays the agents table with search, filters, and pagination.
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 *
 * @var array $agents Array of agents to display
 * @var int $total_agents Total number of agents
 * @var int $current_page Current page number
 * @var int $per_page Items per page
 * @var int $total_pages Total number of pages
 * @var int $start_index Start index for display
 * @var int $end_index End index for display
 * @var string $search_query Current search query
 * @var string $sort_column Current sort column
 * @var string $sort_order Current sort order (ASC/DESC)
 * @var array $columns Columns to display
 * @var array $atts Shortcode attributes
 * @var bool $can_manage Whether user can manage agents
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Alert Container -->
<div id="alert-container" class="alert-container"></div>

<!-- Loader -->
<div id="wecoza-agents-loader-container" style="display: none;">
    <button id="wecoza-loader-02" class="btn btn-primary mt-7" type="button">
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        <?php esc_html_e('Loading...', 'wecoza-agents-plugin'); ?>
    </button>
</div>

<!-- Main Content Container -->
<div id="agents-container">
    <div class="table-responsive">
        <div class="bootstrap-table bootstrap5">
            <!-- Toolbar -->
            <div class="fixed-table-toolbar">
                <?php if ($atts['show_filters']) : ?>
                <div class="columns columns-right btn-group float-right">
                    <button class="btn btn-secondary" type="button" name="refresh" aria-label="<?php esc_attr_e('Refresh', 'wecoza-agents-plugin'); ?>" title="<?php esc_attr_e('Refresh', 'wecoza-agents-plugin'); ?>" onclick="window.location.reload();">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <div class="keep-open btn-group">
                        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-label="<?php esc_attr_e('Columns', 'wecoza-agents-plugin'); ?>" title="<?php esc_attr_e('Columns', 'wecoza-agents-plugin'); ?>">
                            <i class="bi bi-list-ul"></i>
                            <span class="caret"></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <?php 
                            $all_columns = array(
                                'first_name' => __('First Name', 'wecoza-agents-plugin'),
                                'initials' => __('Initials', 'wecoza-agents-plugin'),
                                'last_name' => __('Surname', 'wecoza-agents-plugin'),
                                'gender' => __('Gender', 'wecoza-agents-plugin'),
                                'race' => __('Race', 'wecoza-agents-plugin'),
                                'phone' => __('Tel Number', 'wecoza-agents-plugin'),
                                'email' => __('Email Address', 'wecoza-agents-plugin'),
                                'city' => __('City/Town', 'wecoza-agents-plugin'),
                            );
                            $col_index = 0;
                            foreach ($all_columns as $col_key => $col_label) : 
                                $is_checked = isset($columns[$col_key]) ? 'checked="checked"' : '';
                            ?>
                            <label class="dropdown-item dropdown-item-marker">
                                <input type="checkbox" data-field="<?php echo $col_index; ?>" value="<?php echo $col_index; ?>" <?php echo $is_checked; ?>> 
                                <span><?php echo esc_html($col_label); ?></span>
                            </label>
                            <?php 
                                $col_index++;
                            endforeach; 
                            ?>
                            <?php if ($atts['show_actions']) : ?>
                            <label class="dropdown-item dropdown-item-marker">
                                <input type="checkbox" data-field="actions" value="actions" checked="checked"> 
                                <span><?php esc_html_e('Actions', 'wecoza-agents-plugin'); ?></span>
                            </label>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($atts['show_search']) : ?>
                <div class="float-right search btn-group">
                    <form method="get" action="" class="d-flex">
                        <input class="form-control search-input" type="search" name="search" value="<?php echo esc_attr($search_query); ?>" aria-label="<?php esc_attr_e('Search', 'wecoza-agents-plugin'); ?>" placeholder="<?php esc_attr_e('Search', 'wecoza-agents-plugin'); ?>" autocomplete="off">
                        <?php if (!empty($search_query)) : ?>
                        <a href="<?php echo remove_query_arg('search'); ?>" class="btn btn-secondary ms-2">
                            <?php esc_html_e('Clear', 'wecoza-agents-plugin'); ?>
                        </a>
                        <?php endif; ?>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Table Container -->
            <div class="fixed-table-container" style="padding-bottom: 0px;">
                <div class="fixed-table-body">
                    <table id="agents-display-data" class="table table-bordered ydcoza-compact-table table-hover borderless-table">
                        <thead>
                            <tr>
                                <?php foreach ($columns as $col_key => $col_label) : ?>
                                <th data-field="<?php echo esc_attr($col_key); ?>">
                                    <div class="th-inner sortable both">
                                        <?php if ($atts['show_filters']) : ?>
                                        <a href="<?php echo esc_url($this->get_sort_url($col_key)); ?>">
                                            <?php echo esc_html($col_label); ?>
                                            <?php if ($sort_column === $col_key) : ?>
                                                <i class="bi bi-arrow-<?php echo ($sort_order === 'ASC') ? 'up' : 'down'; ?>"></i>
                                            <?php endif; ?>
                                        </a>
                                        <?php else : ?>
                                            <?php echo esc_html($col_label); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="fht-cell"></div>
                                </th>
                                <?php endforeach; ?>
                                
                                <?php if ($atts['show_actions']) : ?>
                                <th class="text-nowrap text-center ydcoza-width-150" data-field="actions">
                                    <div class="th-inner"><?php esc_html_e('Actions', 'wecoza-agents-plugin'); ?></div>
                                    <div class="fht-cell"></div>
                                </th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($agents)) : ?>
                                <?php foreach ($agents as $index => $agent) : ?>
                                <tr data-index="<?php echo $index; ?>" data-agent-id="<?php echo esc_attr($agent['id']); ?>">
                                    <?php foreach ($columns as $col_key => $col_label) : ?>
                                    <td>
                                        <?php 
                                        $value = isset($agent[$col_key]) ? $agent[$col_key] : '';
                                        if ($col_key === 'email') {
                                            echo '<a href="mailto:' . esc_attr($value) . '">' . esc_html($value) . '</a>';
                                        } elseif ($col_key === 'phone') {
                                            echo '<a href="tel:' . esc_attr($value) . '">' . esc_html($value) . '</a>';
                                        } else {
                                            echo esc_html($value);
                                        }
                                        ?>
                                    </td>
                                    <?php endforeach; ?>
                                    
                                    <?php if ($atts['show_actions']) : ?>
                                    <td class="text-nowrap text-center ydcoza-width-150">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn bg-discovery-subtle view-agent-details" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#agentModal"
                                                    data-agent-id="<?php echo esc_attr($agent['id']); ?>">
                                                <?php esc_html_e('View', 'wecoza-agents-plugin'); ?>
                                            </button>
                                            <?php if ($can_manage) : ?>
                                            <a href="<?php echo esc_url($this->get_edit_url($agent['id'])); ?>" class="btn bg-warning-subtle">
                                                <?php esc_html_e('Edit', 'wecoza-agents-plugin'); ?>
                                            </a>
                                            <button class="btn btn-sm bg-danger-subtle delete-agent-btn" 
                                                    data-id="<?php echo esc_attr($agent['id']); ?>">
                                                <?php esc_html_e('Delete', 'wecoza-agents-plugin'); ?>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="<?php echo count($columns) + ($atts['show_actions'] ? 1 : 0); ?>" class="text-center">
                                        <?php esc_html_e('No agents found.', 'wecoza-agents-plugin'); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <?php if ($atts['show_pagination'] && $total_pages > 1) : ?>
            <div class="fixed-table-pagination">
                <div class="float-left pagination-detail">
                    <span class="pagination-info">
                        <?php printf(
                            esc_html__('Showing %1$d to %2$d of %3$d rows', 'wecoza-agents-plugin'),
                            $start_index,
                            $end_index,
                            $total_agents
                        ); ?>
                    </span>
                    <div class="page-list">
                        <div class="btn-group dropdown dropup">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <span class="page-size"><?php echo esc_html($per_page); ?></span>
                                <span class="caret"></span>
                            </button>
                            <div class="dropdown-menu">
                                <?php 
                                $page_sizes = array(10, 25, 50);
                                foreach ($page_sizes as $size) : 
                                    $url = add_query_arg('per_page', $size, remove_query_arg('paged'));
                                ?>
                                <a class="dropdown-item <?php echo ($size == $per_page) ? 'active' : ''; ?>" 
                                   href="<?php echo esc_url($url); ?>"><?php echo $size; ?></a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php esc_html_e('rows per page', 'wecoza-agents-plugin'); ?>
                    </div>
                </div>
                
                <div class="float-right pagination">
                    <ul class="pagination">
                        <?php if ($current_page > 1) : ?>
                        <li class="page-item page-pre">
                            <a class="page-link" aria-label="<?php esc_attr_e('previous page', 'wecoza-agents-plugin'); ?>" 
                               href="<?php echo esc_url(add_query_arg('paged', $current_page - 1)); ?>">‹</a>
                        </li>
                        <?php else : ?>
                        <li class="page-item page-pre disabled">
                            <span class="page-link">‹</span>
                        </li>
                        <?php endif; ?>
                        
                        <?php 
                        // Page numbers
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        if ($start_page > 1) : ?>
                        <li class="page-item">
                            <a class="page-link" aria-label="<?php esc_attr_e('to page 1', 'wecoza-agents-plugin'); ?>" 
                               href="<?php echo esc_url(remove_query_arg('paged')); ?>">1</a>
                        </li>
                        <?php if ($start_page > 2) : ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                        <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $start_page; $i <= $end_page; $i++) : ?>
                        <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                            <?php if ($i == $current_page) : ?>
                            <span class="page-link"><?php echo $i; ?></span>
                            <?php else : ?>
                            <a class="page-link" aria-label="<?php printf(esc_attr__('to page %d', 'wecoza-agents-plugin'), $i); ?>" 
                               href="<?php echo esc_url(add_query_arg('paged', $i)); ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($end_page < $total_pages) : ?>
                        <?php if ($end_page < $total_pages - 1) : ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" aria-label="<?php printf(esc_attr__('to page %d', 'wecoza-agents-plugin'), $total_pages); ?>" 
                               href="<?php echo esc_url(add_query_arg('paged', $total_pages)); ?>"><?php echo $total_pages; ?></a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if ($current_page < $total_pages) : ?>
                        <li class="page-item page-next">
                            <a class="page-link" aria-label="<?php esc_attr_e('next page', 'wecoza-agents-plugin'); ?>" 
                               href="<?php echo esc_url(add_query_arg('paged', $current_page + 1)); ?>">›</a>
                        </li>
                        <?php else : ?>
                        <li class="page-item page-next disabled">
                            <span class="page-link">›</span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="clearfix"></div>
    </div>
</div>