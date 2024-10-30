<?php

if ( !current_user_can( get_option( $this->shared->get( 'slug' ) . '_juice_menu_required_capability' ) ) ) {
    wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.' ) );
}
?>

<!-- process data -->

<!-- output -->

<div class="wrap">

   
<div id="daext-menu-wrapper" class="daext-clearfix" style="width:100%">
    <?php 
$this->upgrade();
?>
<h2 class="h2-ila">InternalLink Audit - Reports</h2>

<div class="postbox ila-statistic" style="background: #43699F;">
    <div style="display: inline-block;margin: 10px;">
        <form action="" method="post" style="display: inline-block;">
            <?php 
echo  '<input id="ajax-request-status" type="hidden" value="inactive">' ;
echo  '<input class="button" id="update-archive" type="button" value="Generate Data" style="background:#FF8800;color:#ffffff">' ;
?>
        </form>&nbsp;&nbsp;
        <?php 
?>
    </div>
    <div style="display: inline-block;margin: 10px;float: right;">
        <form method="GET" action="admin.php">
            <input type="hidden" name="page" value="ila-audit">
            <input type="text" name="s" value="" autocomplete="off" maxlength="255" placeholder="Keyword or Title">
            <select name="sb" id="sb" style="vertical-align: inherit;">
                <option value="pd" <?php 
if ( !empty($_GET['sb']) and $_GET['sb'] == 'pd' ) {
    echo  "selected" ;
}
?>>Date</option>
                <option value="ti" <?php 
if ( !empty($_GET['sb']) and $_GET['sb'] == 'ti' ) {
    echo  "selected" ;
}
?>> Title</option>
                <option value="pt" <?php 
if ( !empty($_GET['sb']) and $_GET['sb'] == 'pt' ) {
    echo  "selected" ;
}
?>> Post Type</option>
                <option value="cl" <?php 
if ( !empty($_GET['sb']) and $_GET['sb'] == 'cl' ) {
    echo  "selected" ;
}
?>> Content LengthOrder</option>
            </select>
            <select name="or" id="or" style="vertical-align: inherit;">
                <option value="0" <?php 
if ( !empty($_GET['or']) and $_GET['or'] == '0' ) {
    echo  "selected" ;
}
?>> Ascending</option>
                <option value="1" <?php 
if ( !empty($_GET['or']) and $_GET['or'] == '1' ) {
    echo  "selected" ;
}
?>> Descending</option>
            </select>
            <input class="button" type="submit" value="Search"> </form>
    </div>
</div>
   

        <div class="interlinks-container" style="width:100%">

            <?php 
//optimization

if ( isset( $_GET['op'] ) and (trim( $_GET['op'] ) != 'all' and (intval( $_GET['op'], 10 ) == 0 or intval( $_GET['op'], 10 ) == 1)) and strlen( trim( $_GET['op'] ) ) > 0 ) {
    $filter = "WHERE optimization = '" . intval( sanitize_text_field($_GET['op']), 10 ) . "'";
} else {
    $filter = '';
}

//search

if ( isset( $_GET['s'] ) and strlen( trim( $_GET['s'] ) ) > 0 ) {
    $search_string = sanitize_text_field($_GET['s']);
    global  $wpdb ;
    
    if ( strlen( trim( $filter ) ) > 0 ) {
        $filter .= $wpdb->prepare( ' AND (post_title LIKE %s)', '%' . $search_string . '%' );
    } else {
        $filter .= $wpdb->prepare( 'WHERE (post_title LIKE %s)', '%' . $search_string . '%' );
    }

} else {
    $filter .= '';
}

//sort -------------------------------------------------
//sort by

if ( isset( $_GET['sb'] ) ) {
    /*
     * verify if the value is valid, if the value is invalid
     *  default to the "post_date"
     */
    switch ( $_GET['sb'] ) {
        case 'pd':
            $sort_by = 'post_date';
            break;
        case 'ti':
            $sort_by = 'post_title';
            break;
        case 'mi':
            $sort_by = 'manual_interlinks';
            break;
        case 'ai':
            $sort_by = 'auto_interlinks';
            break;
        case 'pt':
            $sort_by = 'post_type';
            break;
        case 'cl':
            $sort_by = 'content_length';
            break;
        case 'op':
            $sort_by = 'optimization';
            break;
        default:
            $sort_by = 'post_date';
            break;
    }
} else {
    $sort_by = 'post_date';
}

//order

if ( isset( $_GET['or'] ) and intval( $_GET['or'], 10 ) == 0 ) {
    $order = "ASC";
} else {
    $order = "DESC";
}

//retrieve the total number of events
global  $wpdb ;
$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_archive";
// echo "SELECT COUNT(*) FROM $table_name " . $filter;
$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} " . $filter );
//Initialize the pagination class
require_once $this->shared->get( 'dir' ) . '/admin/inc/class-ila-pagination.php';
$pag = new ila_pagination();
$pag->set_total_items( $total_items );
//Set the total number of items
$pag->set_record_per_page( intval( 100, 10 ) );
//Set records per page
$pag->set_target_page( "admin.php?page=" . $this->shared->get( 'slug' ) . "-audit" );
//Set target page
$pag->set_current_page();
//set the current page number from $_GET
?>

            <!-- Query the database -->
            <?php 
$query_limit = $pag->query_limit();
//   echo "SELECT * FROM $table_name " . $filter . " ORDER BY $sort_by $order $query_limit ";
$results = $wpdb->get_results( "SELECT * FROM {$table_name} " . $filter . " ORDER BY {$sort_by} {$order} {$query_limit} ", ARRAY_A );
?>

            <?php 

if ( count( $results ) > 0 ) {
    ?>

                <div class="daext-items-container">

                    <table class="daext-items wp-list-table widefat striped table-view-list posts">
                        <thead>
                        <tr style="background: #FF8800;">
                            <th style="color: #ffffff;">
                                <div><?php 
    esc_attr_e( 'Post', 'ila' );
    ?></div>
                                <div class="help-icon"
                                     title="<?php 
    esc_attr_e( 'The post, page or custom post type title.', 'ila' );
    ?>"></div>
                            </th>
                            <th style="color: #ffffff;">
                                <div><?php 
    esc_attr_e( 'URL', 'ila' );
    ?></div>
                                <div class="help-icon"
                                     title="<?php 
    esc_attr_e( 'Page URL', 'ila' );
    ?>"></div>
                            </th>
                          
                            <th style="color: #ffffff;">
                                <div><?php 
    esc_attr_e( 'Outgoing Internal Links', 'ila' );
    ?></div>
                                <div class="help-icon" title="<?php 
    esc_attr_e( 'Total Outgoing Links.', 'ila' );
    ?>"></div>
                            </th>
                            <th style="color: #ffffff;">
                                <div><?php 
    esc_attr_e( 'Incoming Internal Links', 'ila' );
    ?></div>
                                <div class="help-icon" title="<?php 
    esc_attr_e( 'Total Incoming Links.', 'ila' );
    ?>"></div>
                            </th>
                            <th style="color: #ffffff;">
                                <div><?php 
    esc_attr_e( 'WordCount', 'ila' );
    ?></div>
                                <div class="help-icon"
                                     title="<?php 
    esc_attr_e( 'The length of the raw (with filters not applied) post content.', 'ila' );
    ?>"></div>
                            </th>
                            <th style="color: #ffffff;">
                                <div><?php 
    esc_attr_e( 'Date', 'ila' );
    ?></div>
                                <div class="help-icon"
                                     title="<?php 
    esc_attr_e( 'The post, page or custom post type publishing date.', 'ila' );
    ?>"></div>
                            </th>
                             <th style="color: #ffffff;">
                                <div><?php 
    esc_attr_e( 'Action', 'ila' );
    ?></div>
                                <div class="help-icon"
                                     title="<?php 
    esc_attr_e( 'Action.', 'ila' );
    ?>"></div>
                            </th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php 
    foreach ( $results as $i => $result ) {
        $outgoing_links = unserialize( $result['manual_interlinks'] );
        $incoming_links = unserialize( $result['recommended_interlinks'] );
        ?>
                            <tr>
                                <td>
                                    <?php 
        
        if ( get_post_status( $result['post_id'] ) === false ) {
            echo  apply_filters( 'the_title', esc_attr($result['post_title']) ) ;
        } else {
            echo  '<a class="ila_tr" href="" data-pid="' . esc_attr($result['post_id']) . '">' . apply_filters( 'the_title', esc_attr($result['post_title']) ) . '</a>&nbsp;&nbsp;<span class="row-actions"><a href="post.php?post=' . esc_attr($result['post_id']) . '&action=edit" target="_blank">Edit Post</a></span>' ;
        }
        
        ?>
                                </td>
                                <td><?php 
        echo  esc_url(get_permalink( $result['post_id'] )) ;
        ?></td>
                                <td><?php 
        echo  '<a class="ila_tr" href="" data-pid="' . esc_attr($result['post_id']) . '">' . esc_attr( $result['ol_count'] ) . '</a>' ;
        ?></td>
                                <td><?php 
        echo  '<a class="ila_tr" href="" data-pid="' . esc_attr($result['post_id']) . '">' . esc_attr( $result['il_count'] ) . '</a>' ;
        ?></td>
                                <td><?php 
        echo  esc_attr($result['content_length']) ;
        ?></td>
                                 <td><?php 
        echo  esc_attr(mysql2date( get_option( 'date_format' ), $result['post_date'] )) ;
        ?></td>
                                <td><?php 
        echo  '<a class="ila_tr" href="" data-pid="' . esc_attr($result['post_id']) . '"><img src="' . esc_url($this->shared->get( 'url' )) . 'admin/assets/img/anchor.png" title="View" alt="Edit"></a>&nbsp;&nbsp;&nbsp;<span class=""><a href="post.php?post=' . esc_attr($result['post_id']) . '&action=edit&classic-editor__forget" target="_blank"><img src="' . esc_url($this->shared->get( 'url' )) . 'admin/assets/img/edit.png" title="Edit" alt="Edit"></a></span>' ;
        ?></td>


                            </tr>
                            <tr class="li_lists dct">
                                <td colspan="7">
                                    <table class="daext-items more_links wp-list-table widefat fixed striped table-view-list posts" data-lid="<?php echo  esc_attr($result['post_id']); ?>">
                                        <thead>
                                        <tr>
                                            <th class="bg-clr-s">Outgoing Links</th>
                                            <th class="bg-clr-s">Outgoing Anchor Text</th>
                                            <th>Incoming Link</th>
                                            <th>Incoming Anchor Text</th>
                                        </tr>
                                        </thead>
                                    </table>
                                </td>

                            </tr>
                        <?php 
    }
    ?>

                        </tbody>
                    </table>

                </div>

            <?php 
} else {
    ?>

                <?php 
    
    if ( strlen( trim( $filter ) ) > 0 ) {
        echo  '<div class="error settings-error notice is-dismissible below-h2"><p>' . esc_attr__( 'There are no results that match your filter.', 'ila' ) . '</p></div>' ;
    } else {
        echo  '<div class="error settings-error notice is-dismissible below-h2"><p>' . esc_attr__( 'There are no data at moment, click on the "Generate Data" button to generate data and statistics about the internal links of your blog.', 'ila' ) . '</p></div>' ;
    }
    
    ?>

            <?php 
}

?>

            <!-- Display the pagination -->
            <?php 

if ( $pag->total_items > 0 ) {
    ?>
                <div class="daext-tablenav daext-clearfix">
                    <div class="daext-tablenav-pages">
                        <span class="daext-displaying-num"><?php 
    echo  esc_attr($pag->total_items) ;
    esc_attr_e( 'items', 'ila' );
    ?></span>
                        <?php 
    $pag->show();
    ?>
                    </div>
                </div>
            <?php 
}

?>
        <?php 
$this->upgrade();
?>

        </div><!-- #subscribers-container -->


    </div>

</div>