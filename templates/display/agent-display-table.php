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
         <!-- Table Container -->
         <div class="fixed-table-container" style="padding-bottom: 0px;">
            <div class="card shadow-none border my-4" data-component-card="data-component-card">
               <div class="card-header p-3 border-bottom">
                  <div class="row g-3 justify-content-between align-items-center mb-3">
                     <div class="col-12 col-md">
                        <h4 class="text-body mb-0" data-anchor="data-anchor" id="classes-table-header">
                           All Agents
                           <i class="bi bi-calendar-event ms-2"></i>
                        </h4>
                     </div>
                     <div class="search-box col-auto">
                        <form method="get" action="" class="position-relative d-flex">
                           <input class="form-control search-input search form-control-sm" type="search" placeholder="Search" aria-label="Search">
                           <svg class="svg-inline--fa fa-magnifying-glass search-box-icon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="magnifying-glass" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg="">
                              <path fill="currentColor" d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"></path>
                           </svg>
                           <!-- <span class="fas fa-search search-box-icon"></span> Font Awesome fontawesome.com -->
                        </form>
                     </div>
                     <div class="col-auto">
                        <div class="d-flex gap-2">
                           <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.location.reload();">
                           Refresh
                           <i class="bi bi-arrow-clockwise ms-1"></i>
                           </button>
                           <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportClasses()">
                           Export
                           <i class="bi bi-download ms-1"></i>
                           </button>
                        </div>
                     </div>
                  </div>
                  <!-- Summary strip -->
                  <div class="col-12">
                     <div class="scrollbar">
                        <div class="row g-0 flex-nowrap">
                           <?php foreach ($statistics as $stat_key => $stat_data) : ?>
                           <div class="col-auto <?php echo $stat_key === 'total_agents' ? 'pe-4' : 'px-4'; ?>">
                              <h6 class="text-body-tertiary">
                                 <?php echo esc_html($stat_data['label']); ?> : <?php echo esc_html($stat_data['count']); ?>
                                 <?php if (!empty($stat_data['badge'])) : ?>
                                 <div class="badge badge-phoenix fs-10 badge-phoenix-<?php echo esc_attr($stat_data['badge_type']); ?>">
                                    <?php echo esc_html($stat_data['badge']); ?>
                                 </div>
                                 <?php endif; ?>
                              </h6>
                           </div>
                           <?php endforeach; ?>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="card-body p-4 py-2">
                  <div class="fixed-table-body mb-3">
                     <table id="agents-display-data" class="table table-hover table-sm fs-9 mb-0">
                        <thead class="border-bottom">
                           <tr>
                              <?php foreach ($columns as $col_key => $col_label) : ?>
                              <th class="sort" data-field="<?php echo esc_attr($col_key); ?>">
                                 <div class="th-inner sortable both">
                                    <?php if ($atts['show_filters']) : ?>
                                    <a href="<?php echo esc_url($this->get_sort_url($col_key)); ?>">
                                    <?php echo esc_html($col_label); ?>
                                    <?php 
                                    // Add appropriate icon based on column type
                                    $icon_class = '';
                                    switch($col_key) {
                                        case 'first_name':
                                            $icon_class = 'bi bi-person';
                                            break;
                                        case 'initials':
                                            $icon_class = 'bi bi-type-underline';
                                            break;
                                        case 'last_name':
                                            $icon_class = 'bi bi-person-badge';
                                            break;
                                        case 'gender':
                                            $icon_class = 'bi bi-gender-ambiguous';
                                            break;
                                        case 'race':
                                            $icon_class = 'bi bi-people';
                                            break;
                                        case 'phone':
                                            $icon_class = 'bi bi-telephone';
                                            break;
                                        case 'email':
                                            $icon_class = 'bi bi-envelope';
                                            break;
                                        case 'city':
                                            $icon_class = 'bi bi-geo-alt';
                                            break;
                                        default:
                                            $icon_class = 'bi bi-list-ul';
                                            break;
                                    }
                                    ?>
                                    <i class="<?php echo esc_attr($icon_class); ?> ms-1"></i>
                                    <?php if ($sort_column === $col_key) : ?>
                                    <i class="bi bi-arrow-<?php echo ($sort_order === 'ASC') ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                    </a>
                                    <?php else : ?>
                                    <?php echo esc_html($col_label); ?>
                                    <?php 
                                    // Add appropriate icon based on column type
                                    $icon_class = '';
                                    switch($col_key) {
                                        case 'first_name':
                                            $icon_class = 'bi bi-person';
                                            break;
                                        case 'initials':
                                            $icon_class = 'bi bi-type-underline';
                                            break;
                                        case 'last_name':
                                            $icon_class = 'bi bi-person-badge';
                                            break;
                                        case 'gender':
                                            $icon_class = 'bi bi-gender-ambiguous';
                                            break;
                                        case 'race':
                                            $icon_class = 'bi bi-people';
                                            break;
                                        case 'phone':
                                            $icon_class = 'bi bi-telephone';
                                            break;
                                        case 'email':
                                            $icon_class = 'bi bi-envelope';
                                            break;
                                        case 'city':
                                            $icon_class = 'bi bi-geo-alt';
                                            break;
                                        default:
                                            $icon_class = 'bi bi-list-ul';
                                            break;
                                    }
                                    ?>
                                    <i class="<?php echo esc_attr($icon_class); ?> ms-1"></i>
                                    <?php endif; ?>
                                 </div>
                                 <div class="fht-cell"></div>
                              </th>
                              <?php endforeach; ?>
                              <?php if ($atts['show_actions']) : ?>
                              <th class="text-nowrap text-center ydcoza-width-150" data-field="actions">
                                 <div class="th-inner">
                                    <?php esc_html_e('Actions', 'wecoza-agents-plugin'); ?>
                                    <i class="bi bi-gear ms-1"></i>
                                 </div>
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
                              <td class="text-body ps-1">
                                 <?php 
                                    $value = isset($agent[$col_key]) ? $agent[$col_key] : '';
                                    if ($col_key === 'email') {
                                        echo '<a href="mailto:' . esc_attr($value) . '" class="text-primary">' . esc_html($value) . '</a>';
                                    } elseif ($col_key === 'phone') {
                                        echo '<a href="tel:' . esc_attr($value) . '" class="text-primary">' . esc_html($value) . '</a>';
                                    } else {
                                        echo esc_html($value);
                                    }
                                    ?>
                              </td>
                              <?php endforeach; ?>
                              <?php if ($atts['show_actions']) : ?>
                              <td class="text-center">
                                 <div class="dropdown">
                                    <button class="btn btn-link text-body btn-sm dropdown-toggle" style="text-decoration: none;" type="button" id="dropdownMenuButton<?php echo esc_attr($agent['id']); ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                       <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo esc_attr($agent['id']); ?>">
                                       <li>
                                          <button class="dropdown-item view-agent-details" 
                                             data-bs-toggle="modal" 
                                             data-bs-target="#agentModal"
                                             data-agent-id="<?php echo esc_attr($agent['id']); ?>">
                                             <?php esc_html_e('View Details', 'wecoza-agents-plugin'); ?>
                                             <i class="bi bi-eye ms-2"></i>
                                          </button>
                                       </li>
                                       <?php if ($can_manage) : ?>
                                       <li>
                                          <a class="dropdown-item" href="<?php echo esc_url($this->get_edit_url($agent['id'])); ?>">
                                             <?php esc_html_e('Edit Agent', 'wecoza-agents-plugin'); ?>
                                             <i class="bi bi-pencil ms-2"></i>
                                          </a>
                                       </li>
                                       <li><hr class="dropdown-divider"></li>
                                       <li>
                                          <button class="dropdown-item text-danger delete-agent-btn" 
                                             data-id="<?php echo esc_attr($agent['id']); ?>">
                                             <?php esc_html_e('Delete Agent', 'wecoza-agents-plugin'); ?>
                                             <i class="bi bi-trash ms-2"></i>
                                          </button>
                                       </li>
                                       <?php endif; ?>
                                    </ul>
                                 </div>
                              </td>
                              <?php endif; ?>
                           </tr>
                           <?php endforeach; ?>
                           <?php else : ?>
                           <tr>
                              <td colspan="<?php echo count($columns) + ($atts['show_actions'] ? 1 : 0); ?>" class="text-center text-muted">
                                 <?php esc_html_e('No agents found.', 'wecoza-agents-plugin'); ?>
                              </td>
                           </tr>
                           <?php endif; ?>
                        </tbody>
                     </table>
                  </div>
               </div>
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