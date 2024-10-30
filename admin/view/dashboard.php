<?php

if (!current_user_can(get_option($this->shared->get('slug') . '_dashboard_menu_required_capability'))) {
    wp_die(esc_attr__('You do not have sufficient permissions to access this page.'));
}

?>

<!-- process data -->

<!-- output -->

<div class="wrap">
    
    <?php $this->upgrade();?>

    <h2 class="h2-ila">InternalLink Audit - Dashboard</h2>
        <div class="ila-row">      
            <div class="col-9">
                    <div id="" class="daext-clearfix">
                        <!-- list of subscribers -->
                        <div class="dashboard-container">
                            <div class="postbox ila-statistic">
                                <h2 style="font-size:24px;text-align: center;">Plugin Related</h2>
                                <div class="inside">
                                    <p><strong>Installed version:</strong> <?php echo esc_attr($this->getVersion()); ?></p>
                                    <ul class="ila-ressources divide">
                                        <li><span class="dashicons dashicons-book-alt"></span><a href="https://www.onpageseo.tools/internallink-audit-wordpress-plugin/" target="_blank" rel="noopener"><strong>Docs &amp; How To</strong></a>&nbsp;&nbsp;<span>(Learn how to use the plugin)</span></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="">
                                <div class="ila-row postbox ila-statistic">

                                     <h2 style="font-size:24px;text-align: center;">Statistics</h2> 

                                     <?php  if ( ila_fs()->is_trial() ) { ?>
                                     <div class="col-12">
                                        <div class="inside">
                                            <?php
                                                   echo '<div class="error settings-error notice below-h2"><p>' . esc_attr__('There are no statistics to display', 'ila') . '</p></div>';
                                            ?>

                                        </div>
                                     </div>   
                                    <?php } ?>

                                     <?php
                                        //retrieve the total number of events
                                        global $wpdb;
                                        $table_name = $wpdb->prefix . $this->shared->get('slug') . "_archive";
                                       // $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name " . $filter);

                                     ?>
                                       <div class="col-6">
                                            <div class="inside">
                                                    <h3 style="font-size:20px;text-align: center;background:#43699F;color:#ffffff">Top 10 Outgoing Internal Links</h3>
                                                    <?php
                                                    // for outgoing links        
                                                    //  $query_limit = $pag->query_limit();
                                                    //   "SELECT * FROM $table_name " . $filter . " ORDER BY $sort_by $order $query_limit ";
                                                    $ol_results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY ol_count desc limit 10 ", ARRAY_A);
                                                    if (count($ol_results) > 0) : ?>


                                                    <table class="dash_table daext-items wp-list-table widefat striped table-view-list posts">
                                                            <thead>
                                                            <tr>
                                                                <th>
                                                                    <div><?php esc_attr_e('Post', 'ila'); ?></div>
                                                                </th>
                                                                <th>
                                                                    <div><?php esc_attr_e('Count', 'ila'); ?></div>
                                                                </th>
                                                                <th>
                                                                    <div><?php esc_attr_e('Action', 'ila'); ?>&nbsp;&nbsp;&nbsp;</div>
                                                                </th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>

                                                            <?php foreach ($ol_results as $i => $result) {
                                                                $outgoing_links = unserialize($result['manual_interlinks']);
                                                                $incoming_links = unserialize($result['recommended_interlinks']);
                                                                ?>
                                                                <tr>
                                                                    <td>
                                                                        <?php
                                                                            echo apply_filters('the_title', esc_attr($result['post_title']));
                                                                        ?>
                                                                    </td>
                                                                    <td><?php 
                                                                            echo esc_attr($result['ol_count']);
                                                                   ?></td>
                                                                   <td><?php 
                                                                            echo '<a href="post.php?post='.esc_attr($result['post_id']).'&action=edit" target="_blank"><img src="'.esc_url($this->shared->get('url')) . 'admin/assets/img/edit.png" title="Edit" alt="Edit"></a>&nbsp;';
                                                                            echo '<a href="'.esc_url(get_permalink($result['post_id'])).'" target="_blank"><img src="'.esc_url($this->shared->get('url')) . 'admin/assets/img/external.png" title="View" alt="View"></a>';

                                                                   ?></td>
                                                                </tr>
                                                            
                                                            <?php } ?>

                                                            </tbody>
                                                    </table>


                                                    <?php else : ?>

                                                    <?php

                                                        echo '<div class="error settings-error notice is-dismissible below-h2"><p>' . esc_attr__('There are no data at moment, click on the "Generate Data" button to generate data and statistics about the internal links of your blog.',
                                                                'ila') . '</p></div>';
                                                    ?>

                                                <?php endif; ?>
                                            </div>
                                        </div>


                                        <div class="col-6">

                                             <div class="inside">
                                                 <h3 style="font-size:20px;text-align: center;background:#43699F;color:#ffffff">Top 10 Incoming Internal Links</h3>


                                                <?php
                                                // for incoming links

                                               //     $query_limit = $pag->query_limit();
                                                 //   echo "SELECT * FROM $table_name " . $filter . " ORDER BY $sort_by $order $query_limit ";
                                                $ol_results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY il_count desc limit 10 ", ARRAY_A);
                                                if (count($ol_results) > 0) : ?>

                                                        <table class="dash_table daext-items wp-list-table widefat striped table-view-list posts">
                                                            <thead>
                                                            <tr>
                                                                <th>
                                                                    <div><?php esc_attr_e('Post', 'ila'); ?></div>
                                                                </th>
                                                                <th>
                                                                    <div><?php esc_attr_e('Count', 'ila'); ?></div>
                                                                </th>
                                                                <th>
                                                                    <div><?php esc_attr_e('Action', 'ila'); ?>&nbsp;&nbsp;&nbsp;</div>
                                                                </th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>

                                                            <?php foreach ($ol_results as $i => $result) {
                                                                ?>
                                                                <tr>
                                                                    <td>
                                                                        <?php
                                                                            echo apply_filters('the_title', esc_attr($result['post_title']));
                                                                        ?>
                                                                    </td>
                                                                    <td><?php 
                                                                            echo esc_attr($result['ol_count']);
                                                                   ?></td>
                                                                   <td><?php 

                                                                            echo '<a href="post.php?post='.esc_attr($result['post_id']).'&action=edit" target="_blank"><img src="'.esc_url($this->shared->get('url')) . 'admin/assets/img/edit.png" title="Edit" alt="Edit"></a>&nbsp;';

                                                                            echo '<a href="'.esc_url(get_permalink($result['post_id'])).'" target="_blank"><img src="'.esc_url($this->shared->get('url')) . 'admin/assets/img/external.png" title="View" alt="View"></a>';
                                                                   ?></td>
                                                                </tr>
                                                            
                                                            <?php } ?>

                                                            </tbody>
                                                        </table>

                                                <?php else : ?>

                                                    <?php

                                                        echo '<div class="error settings-error notice is-dismissible below-h2"><p>' . esc_attr__('There are no data at moment, click on the "Generate Data" button to generate data and statistics about the internal links of your blog.',
                                                                'ila') . '</p></div>';
                                                    ?>

                                                <?php endif; ?>
                                            </div>
                                        </div>   
                                       
                                </div>
                            </div>


                        </div>
                    </div>
            </div>
            
            <div class="col-3">

                <div class="sidebar-container">

                        <?php $this->renderGen();?>

                            <?php
                            
                                    if ( ila_fs()->is_free_plan() ) {
                                        $this->renderPromo();
                                    }

                                    $this->support();

                            ?>

                      <!--   <div class="daext-widget" id="filter-and-sort">

                            <h3 class="daext-widget-title"><?php esc_attr_e('Filter & Sort', 'ila'); ?></h3>

                           

                    </div>-->
                </div>

            </div>
        </div>
</div>