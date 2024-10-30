<?php
/*
 * this class should be used to include ajax actions
 */
class Ila_Ajax{
    
    protected static $instance = null;
    private $shared = null;
    
    private function __construct() {
        
        //assign an instance of the plugin info
        $this->shared = Ila_Shared::get_instance();
        
        //ajax requests --------------------------------------------------------
        
        //for logged-in and not-logged-in users --------------------------------
        // add_action( 'wp_ajax_track_internal_link', array( $this, 'track_internal_link' ) );
        // add_action( 'wp_ajax_nopriv_track_internal_link', array( $this, 'track_internal_link' ) );
        
        //for logged-in users --------------------------------------------------
        add_action( 'wp_ajax_update_interlinks_archive', array( $this, 'update_interlinks_archive' ) );
        add_action( 'wp_ajax_list_in_out_links', array( $this, 'list_in_out_links' ) );
        add_action( 'wp_ajax_generate_interlinks_suggestions', array( $this, 'generate_interlinks_suggestions' ) );
	    add_action( 'wp_ajax_generate_interlinks_optimization', array( $this, 'generate_interlinks_optimization' ) );
        
    }
    
    /*
     * return an istance of this class
     */
    public static function get_instance() {

        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
            
    }

    public function list_in_out_links(){

        $post_id = intval(sanitize_text_field($_POST['post_id']),10);

         if(isset($post_id) && $post_id != ""){
            global $wpdb;
            $table_name = $wpdb->prefix  . $this->shared->get('slug') . "_archive";
            $safe_sql = "SELECT post_id, manual_interlinks, recommended_interlinks FROM $table_name where post_id='".$post_id."'";
            $posts_a = $wpdb->get_results($safe_sql, ARRAY_A);
            $arr = [];
            foreach ($posts_a as $post){
                $out_links = unserialize($post['manual_interlinks']);
                $in_links = unserialize($post['recommended_interlinks']);
                $links = [];
                if(count($out_links) > count($in_links)){
                    $links = $out_links;
                } else {
                    $links = $in_links;
                }
                foreach($links as $i=>$li){
                    $temp = [];
                    if(isset($out_links[$i])){
                        $a = new SimpleXMLElement($out_links[$i]);
                        $temp['out_link'] = (String) $a['href'];
                        if(!empty( end($a)))
                        {
                            $a=end($a);
                        }
                        $temp['out_text'] = (String) $a;
                    } else {
                        $temp['out_link'] = (String) "N/A";
                        $temp['out_text'] = (String) "N/A";
                    }

                    if(isset($in_links[$i])){
                        $a = new SimpleXMLElement($in_links[$i]);
                        //print_r($a);
                        $temp['in_link'] = (String) $a['href'];
                        if(!empty($a[0]))
                        {
                            $a=end($a);
                        }

                        $temp['in_text'] = (String) $a;
                    } else {
                        $temp['in_link'] = (String) "N/A";
                        $temp['in_text'] = (String) "N/A";
                    }
                    array_push($arr, $temp);

                }
            }
            echo(json_encode($arr));
        }
        die();
    }
    
    /*
     * Ajax handler used to generate the interlinks archive in the "Dashboard"
     * menu
     */
    public function update_interlinks_archive(){

        //check the referer
         if(!check_ajax_referer( 'ila', 'security', false )){echo esc_attr("Invalid AJAX Request"); die();}

        //check the capability
        if(!current_user_can(get_option( $this->shared->get('slug') . "_dashboard_menu_required_capability"))){echo esc_attr("Invalid Capability"); die();}

        // if(!current_user_can(get_option( $this->shared->get('slug') . "_audit_menu_required_capability"))){echo "Invalid Capability"; die();}
        
        /*
         * Set the custom "Max Execution Time Value" defined in the options if
         * the 'Set Max Execution Time' option is set to "Yes"
         */
        // if( intval( get_option( $this->shared->get('slug') . '_set_max_execution_time') , 10) == 1 ){
        //     ini_set('max_execution_time', intval(get_option("ila_max_execution_time_value"), 10));
        // }
        ini_set("max_execution_time", "0");
        ini_set("memory_limit", "-1");

        /*
         * Set the custom "Memory Limit Value" ( in megabytes ) defined in the
         * options if the 'Set Memory Limit' option is set to "Yes"
         */

        // if( intval( get_option( $this->shared->get('slug') . '_set_memory_limit') , 10) == 1 ){
        //     ini_set('memory_limit', intval(get_option("ila_memory_limit_value"), 10) . 'M');
        // }
        
        /*
         * Create a query used to consider in the analysis only the post types
         * selected with the 'dashboard_post_types' option
         */
        $dashboard_post_types = preg_replace('/\s+/', '', get_option($this->shared->get('slug') . '_dashboard_post_types' ));
        $dashboard_post_types_a = explode(',', $dashboard_post_types);
        $post_types_query = '';
        foreach($dashboard_post_types_a as $key => $value){

            if (!preg_match("/[a-z0-9_-]+/", $value)) {continue;}

            $post_types_query .= "post_type = '" . $value . "'";
            if($key != ( count($dashboard_post_types_a) - 1 )){$post_types_query .= ' OR ';} 

        }
        
        /*
         * get all the manual internal links and save them in the archive db
         * table
         */
        global $wpdb;
        $table_name = $wpdb->prefix . "posts";
        $limit_posts_analysis = intval(get_option($this->shared->get('slug') . '_limit_posts_analysis'), 10);
        $safe_sql = "SELECT ID, post_title, post_type, post_date, post_content FROM $table_name WHERE ($post_types_query) AND post_status = 'publish' ORDER BY post_date DESC LIMIT " . $limit_posts_analysis; 

        $posts_a = $wpdb->get_results($safe_sql, ARRAY_A);
        
        //delete the internal links archive database table content
            $table_name = $wpdb->prefix  . $this->shared->get('slug') . "_archive";
            $result = $wpdb->query("TRUNCATE TABLE $table_name");
        
        //init $archive_a
        $archive_a = array();

        foreach ($posts_a as $key => $single_post) {

            //set the post id
            $post_archive_post_id = $single_post['ID'];
            
            //get the post title
            $post_archive_post_title = strip_tags($single_post['post_title']);
            
            //set the post type
            $post_archive_post_type = $single_post['post_type'];
            
            //set the post date
            $post_archive_post_date = $single_post['post_date'];
            
            //set the post content
            $post_content = $single_post['post_content'];

            //Incoming Links
            $incoming_links = [];
            foreach($posts_a as $key_il => $single_post_il){
                $n_link = get_permalink($single_post_il['ID']);
                $links = $this->shared->get_manual_interlinks($single_post_il['post_content']);
                foreach($links as $li){
                    $li = str_replace('&', '&amp;', $li);
                    $lis = new SimpleXMLElement($li);
                    $post_id = url_to_postid($lis['href']);
                    if($post_archive_post_id == $post_id){
                        $inls = new SimpleXMLElement($li);
                        $ll = "<a href='".$n_link."'>".$inls."</a>";
                        array_push($incoming_links, $ll);
                    }
                }
            }
            
            $incoming_links = serialize($incoming_links);

            $il = unserialize($incoming_links);
            $il_count=count($il);
            //Incoming Links End

            //set the number of manual internal links
            $post_archive_manual_interlinks = serialize($this->shared->get_manual_interlinks($post_content));
            $ol = unserialize($post_archive_manual_interlinks);
            $ol_count=count($ol);

            //print_r($this->shared->get_manual_interlinks($post_content)); continue;
            
            //create a variable with the post content with autolinks included
            $post_content_with_autolinks='';
            $post_archive_auto_interlinks='';
          //  $post_content_with_autolinks = $this->shared->add_autolinks($post_content, false, $post_archive_post_type, $post_archive_post_id);
            
            //set the number of auto internal links

           // $post_archive_auto_interlinks = $this->shared->get_autolinks_number($post_content_with_autolinks);
            
            //set the post content length
            $post_archive_content_length = str_word_count(trim($post_content));
            
            //set the recommended interlinks
            $post_archive_recommended_interlinks = 0;
            
            //set the optimization flag
            $optimization = 0;
            
            /*
             * save data in the $archive_a array ( data will be later saved into
             * the archive db table )
             */
            $archive_a[] = array(
                'post_id' => $post_archive_post_id,
                'post_title' => $post_archive_post_title,
                'post_type' => $post_archive_post_type,
                'post_date' => $post_archive_post_date,
                'ol_count' => $ol_count,
                'manual_interlinks' => $post_archive_manual_interlinks,
                'auto_interlinks' => $post_archive_auto_interlinks,
                'content_length' => $post_archive_content_length,
                'il_count' => $il_count,
                'recommended_interlinks' => $post_archive_recommended_interlinks,
                'optimization' => $optimization,
                'incoming_links' => $incoming_links,
            );
            
        }
        // print_r($archive_a);
        // die();

        /*
         * Save data into the archive db table with multiple queries of 100
         * items each one.
         * It's a compromise for the following two reasons:
         * 1 - For performance, too many queries slow down the process
         * 2 - To avoid problem with queries too long the number of inserted
         * rows per query are limited to 100
         */
        $table_name = $wpdb->prefix  . $this->shared->get('slug') . "_archive";
        $archive_a_length = count($archive_a);
        $query_groups = array();
        $query_index = 0;
        foreach($archive_a as $key => $single_archive){
            
            $query_index = intval($key/100, 10);
            
            $query_groups[$query_index][] = $wpdb->prepare("( %d, %s, %s, %s, %s, %s, %s, %s, %s,%s, %s )",
                $single_archive['post_id'],
                $single_archive['post_title'],
                $single_archive['post_type'],
                $single_archive['post_date'],
                $single_archive['ol_count'],
                $single_archive['manual_interlinks'],
                $single_archive['auto_interlinks'],
                $single_archive['content_length'],
                $single_archive['il_count'],
                $single_archive['incoming_links'],
                $single_archive['optimization']
            );
            
        }
        /*
         * Each item in the $query_groups array includes a maximum of 100
         * assigned records. Here each group creates a query and the query is
         * executed
         */
        $query_start = "INSERT INTO $table_name (post_id, post_title, post_type, post_date,ol_count ,manual_interlinks, auto_interlinks, content_length,il_count, recommended_interlinks, optimization) VALUES ";
        $query_end = '';
        
        foreach($query_groups as $key => $query_values){
            
            $query_body = '';
            
            foreach($query_values as $single_query_value){
                
                $query_body .= $single_query_value . ',';
                
            }
            
            $safe_sql = $query_start . substr($query_body, 0, strlen($query_body) - 1) . $query_end;

            //save data into the archive db table
            $wpdb->query( $safe_sql );

        }
        
        //send output
        echo esc_attr('success');
        die();
        
    }
    
    /*
     * Ajax handler used to track internal links in the front-end
     */
    public function track_internal_link(){

        //check the referer
        if(!check_ajax_referer( 'ila', 'security', false )){echo esc_attr("Invalid AJAX Request"); die();}

        //get data
        if($_POST['link_type'] == 'ail'){$link_type = 0;}else{$link_type = 1;}
        $source_post_id = intval(sanitize_text_field($_POST['source_post_id']), 10);
        $target_url = mb_substr(sanitize_text_field($_POST['target_url']),0, 2038);
        $date = current_time('mysql');
        $date_gmt = current_time('mysql', 1);
        
        /*
         * Remove all the filter associated with 'the_title' to get with the
         * function get_the_title() the raw title saved in the posts table
         */
        remove_all_filters('the_title');
        $post_title = get_the_title($source_post_id);
        
        //verify if the post with the link exists
        if ( get_post_status( $source_post_id ) === false ) { echo esc_attr('The post doesn\'t exists.'); die(); }
        
        //save into the database
        global $wpdb;
        $table_name = $wpdb->prefix . $this->shared->get('slug') . "_hits";
        $safe_sql = $wpdb->prepare("INSERT INTO $table_name SET 
            source_post_id = %d,
            post_title = %s,
            target_url = %s,
            link_type = %s,
            date = %s,
            date_gmt = %s",
            $source_post_id,
            $post_title,
            $target_url,
            $link_type,
            $date,
            $date_gmt
        );

        $query_result = $wpdb->query( $safe_sql );

        if($query_result === false){
            $result = 'error';
        }else{
            $result = 'success';
        }
        
        //send output
        echo esc_attr($result);
        die();
        
    }

  

    
    /*
     * Ajax handler used to generate a list of suggestions in the "Interlinks
     * Suggestions" meta box
     */
    public function generate_interlinks_suggestions(){

        //check the referer
        if(!check_ajax_referer( 'ila', 'security', false )){echo esc_attr("Invalid AJAX Request"); die();}
        
        //check the capability
      //  if(!current_user_can(get_option( $this->shared->get('slug') . "_interlinks_suggestions_mb_required_capability"))){echo "Invalid Capability"; die();}

        /*
         * Set the custom "Max Execution Time Value" defined in the options if
         * the 'Set Max Execution Time' option is set to "Yes"
         */
        if( intval( get_option( $this->shared->get('slug') . '_set_max_execution_time') , 10) == 1 ){
            ini_set('max_execution_time', intval(get_option("ila_max_execution_time_value"), 10));
        }
        
        /*
         * Set the custom "Memory Limit Value" ( in megabytes ) defined in the
         * options if the 'Set Memory Limit' option is set to "Yes"
         */
        if( intval( get_option( $this->shared->get('slug') . '_set_memory_limit') , 10) == 1 ){
            ini_set('memory_limit', intval(get_option("ila_memory_limit_value"), 10) . 'M');
        }
        
        //get the post id for which the suggestions should be generated
        $post_id = intval(sanitize_text_field($_POST['post_id']), 10);
        
        //get the options values
        $option_title = get_option( $this->shared->get('slug') . '_suggestions_titles');//consider, ignore
        $option_post_type = get_option( $this->shared->get('slug') . '_suggestions_post_type');//require, consider, ignore
        $option_categories = get_option( $this->shared->get('slug') . '_suggestions_categories');//require, consider, ignore
        $option_tags = get_option( $this->shared->get('slug') . '_suggestions_tags');//require, consider, ignore
        
        /*
         * Create a query to get the posts that belong to the selected
         * 'Pool Post Types'
         */
        $pool_post_types = preg_replace('/\s+/', '', get_option($this->shared->get('slug') . '_suggestions_pool_post_types' ));
        $post_types_a = explode(',', $pool_post_types);
        $pool_post_types_query = '';
        foreach($post_types_a as $key => $value){

            if (!preg_match("/[a-z0-9_-]+/", $value)) {continue;}

            $pool_post_types_query .= "post_type = '" . $value . "'";
            if($key != ( count($post_types_a) - 1 )){$pool_post_types_query .= ' or ';} 

        }
        if(strlen($pool_post_types_query) > 0){$pool_post_types_query = ' AND (' . $pool_post_types_query. ')';}
        
        /*
         * step1: $option_title
         * 
         * if $option_title is set to 'consider' compare each word that appears
         * in the current post title with the ones that appears in every other
         * available post and increase the score by 10 for each word
         * 
         * if $option_title is set to 'ignore' create an array with all the
         * posts and 0 as the score
         * 
         * The array that saves the score is the $posts_ranking_a array
         * 
         */
        if($option_title == 'consider'){
            
            //get the current post title
            $current_post_title = get_the_title($post_id);

            /*
             * extract all the words from the current post title and save them
             * in the $shared_words array
             */
            //$temp_post_title = $current_post_title;
            $shared_words = array();
                
            /*
             * Save in $shared_words all the single words available in the title
             * of the current post
             */
            $shared_words = explode(' ', $current_post_title);
            
            //remove empty elements from the array
            $shared_words = array_filter($shared_words);     
            
            /*
             * Execute the query to get the posts that belong to the selected
             * 'Pool Post Types'
             */
            global $wpdb;
            $table_name = $wpdb->prefix . "posts";
            $limit_posts_analysis = intval(get_option($this->shared->get('slug') . '_limit_posts_analysis'), 10);
            $results = $wpdb->get_results("SELECT ID, post_type, post_title FROM $table_name WHERE post_status = 'publish' $pool_post_types_query ORDER BY post_date DESC LIMIT $limit_posts_analysis", ARRAY_A);

            /*
            * Compare each word that appears in the current post title with the 
            * ones that appears in every other available post and increase the
            * score by 10 for each word
             */
            foreach($results as $key => $single_result){

                $score = 0;

                //assign 10 points for the word matches
                foreach($shared_words as $key => $needle){
                    if( strpos($single_result['post_title'], $needle) !== false ){$score = $score + 10;};
                }

                //save post data in the $posts_ranking_a array
                $posts_ranking_a[] = array(
                    'id' => $single_result['ID'],
                    'post_type' => $single_result['post_type'],
                    'score' => $score
                );

            }
            
        }else{
            
            //create an array with all the posts and 0 as score ----------------
            global $wpdb;
            $table_name = $wpdb->prefix . "posts";
            $limit_posts_analysis = intval(get_option($this->shared->get('slug') . '_limit_posts_analysis'), 10);
            $results = $wpdb->get_results("SELECT ID, post_type FROM $table_name WHERE post_status = 'publish' $pool_post_types_query ORDER BY post_date DESC LIMIT $limit_posts_analysis", ARRAY_A);

            //cycle through all the posts
            foreach($results as $key => $single_result){
                
                //save post data in the $posts_ranking_a array
                $posts_ranking_a[] = array(
                    'id' => $single_result['ID'],
                    'post_type' => $single_result['post_type'],
                    'score' => 0
                );
                
            }
        }
        
        /*
         * step2: $option_post_type
         * 
         * If $option_post_type is set to 'require' remove from the array
         * $posts_ranking_a all the posts that don't belong to this post type
         * 
         * If $option_post_type is set to 'consider' add 20 to all the posts
         * that belong to this post type on the $posts_ranking_a array
         * 
         * If $option_post_type is set to 'ignore' do nothing
         * 
         */
        
        //proceed with this step only if the $posts_ranking_a exists and it's not empty
        if( isset($posts_ranking_a) and ( count($posts_ranking_a) > 0 ) ){
        
            //get the post type of this post
            $current_post_type = get_post_type($post_id);

            switch ($option_post_type){

                case 'require':

                    foreach($posts_ranking_a as $pra_key => $pra_value){
                        if( $pra_value['post_type'] != $current_post_type ){
                            unset($posts_ranking_a[$pra_key]);
                        }
                    }

                    break;

                case 'consider':

                    foreach($posts_ranking_a as $pra_key => $pra_value){
                        if( $pra_value['post_type'] == $current_post_type ){
                            $posts_ranking_a[$pra_key]['score'] = $posts_ranking_a[$pra_key]['score'] + 20; 
                        }
                    }

                    break;

                case 'ignore':

                    break;

            }
            
        }
        
        /*
         * step3: $option_categories
         * 
         * If $option_categories is set to 'require' remove from the
         * $posts_ranking_a array all the posts that don't have any category 
         * that the current post have
         * 
         * If the $option_categories is set to 'consider' add 20 to all the
         * posts that have the category that the current post have ( add 20 for
         * each category found )
         * 
         * if $option_categories is set to 'ignore' do nothing
         * 
         * Please note that this option is applied only to the posts that have
         * the "category" taxonomy and that are associated with one or more
         * categories
         */
        
        //proceed with this step only if the $posts_ranking_a exists and it's not empty
        if( isset($posts_ranking_a) and ( count($posts_ranking_a) > 0 ) ){
        
            if( in_array( 'category', get_object_taxonomies( get_post_type($post_id) ) ) ){

                //get an array with a list of the id of the categories
                $current_post_categories = wp_get_post_categories($post_id);

                if( is_array($current_post_categories) and count($current_post_categories) > 0 ){

                    switch ($option_categories){

                        case 'require':

                            foreach($posts_ranking_a as $pra_key => $pra_value){
                                $found = false;
                                $iterated_post_categories = wp_get_post_categories($pra_value['id']);
                                foreach($current_post_categories as $cpc_key => $cpc_value){
                                    if(in_array($cpc_value, $iterated_post_categories)){
                                       $found = true; 
                                    }
                                }
                                if(!$found){
                                    unset($posts_ranking_a[$pra_key]);
                                }
                            }

                            break;

                        case 'consider':

                            foreach($posts_ranking_a as $pra_key => $pra_value){
                                $found = false;
                                $iterated_post_categories = wp_get_post_categories($pra_value['id']);
                                foreach($current_post_categories as $cpc_key => $cpc_value){
                                    if(in_array($cpc_value, $iterated_post_categories)){
                                       $found = true; 
                                    }
                                }
                                if($found){
                                    $posts_ranking_a[$pra_key]['score'] = $posts_ranking_a[$pra_key]['score'] + 20;
                                }
                            }

                            break;

                        case 'ignore':

                            break;

                    }

                }

            }
            
        }
        
        /*
         * step4: $option_tags
         * 
         * If $option_tags is set to 'require' remove from the $posts_ranking_a
         * array all posts that don't have any tag that the current post have
         * 
         * If the $option_tags is set to 'consider' add 20 to all the
         * posts that have the tag that the current post have ( add 20 for
         * each tag found )
         * 
         * if $option_tags is set to 'ignore' do nothing
         * 
         * Please note that this option is applied only to the posts that have
         * the "post_tag" taxonomy and that are associated with one or more
         * tags
         */
        
        //proceed with this step only if the $posts_ranking_a exists and it's not empty
        if( isset($posts_ranking_a) and ( count($posts_ranking_a) > 0 ) ){
        
            if( in_array( 'post_tag', get_object_taxonomies( get_post_type($post_id) ) ) ){

                //get an array with a list of the id of the categories
                $current_post_tags = wp_get_post_tags($post_id);

                if( is_array($current_post_tags) and count($current_post_tags) > 0 ){

                    switch ($option_tags){

                        case 'require':

                            foreach($posts_ranking_a as $pra_key => $pra_value){
                                $found = false;
                                $iterated_post_tags = wp_get_post_tags($pra_value['id']);
                                foreach($current_post_tags as $cpt_key => $cpt_value){
                                    if(in_array($cpt_value, $iterated_post_tags)){
                                       $found = true; 
                                    }
                                }
                                if(!$found){
                                    unset($posts_ranking_a[$pra_key]);
                                }
                            }

                            break;

                        case 'consider':

                            foreach($posts_ranking_a as $pra_key => $pra_value){
                                $found = false;
                                $iterated_post_tags = wp_get_post_tags($pra_value['id']);
                                foreach($current_post_tags as $cpt_key => $cpt_value){
                                    if(in_array($cpt_value, $iterated_post_tags)){
                                       $found = true; 
                                    }
                                }
                                if($found){
                                    $posts_ranking_a[$pra_key]['score'] = $posts_ranking_a[$pra_key]['score'] + 20;
                                }
                            }

                            break;

                        case 'ignore':

                            break;

                    }

                }

            }
            
        }
        
        if( !isset($posts_ranking_a) or count($posts_ranking_a) <= 5){
            
            $output = '<p>' . esc_attr__('There are no interlinks suggestions at the moment, please use this functionality when you have at least five posts (other than the current one) that match the criteria you defined in the "Suggestions" options.', 'ila') . '</p>';
            
            //send the output
            echo esc_html($output);
            die();
            
        }
        
        /*
         * Remove the current post from the $post_ranking_a ( The current post
         * obviously should not be displayed as a interlinks suggestion )
         */
        foreach($posts_ranking_a as $key => $value){
            if($value['id'] == $post_id){
                unset($posts_ranking_a[$key]);
            }
        }
        
        /*
         * Order the $post_ranking_a with descending order based on the 'score'
         */
        usort($posts_ranking_a, array($this->shared, 'usort_callback_1'));
        
        /*
         * Create the $id_list_a[] array with the reference to the first
         * $pool_size elements of $posts_ranking_a
         */
        $id_list_a = array();
        $counter = 1;
        $pool_size = intval(get_option( $this->shared->get('slug') . '_suggestions_pool_size'), 10);
        foreach($posts_ranking_a as $key => $value){
            if($counter > $pool_size){continue;}
            $id_list_a[] = $value['id'];
            $counter++;
        }
        
        /*
         * Get the post URLs and anchors and generate the HTML content of the list
         * based on the $id_list_a
         */
        
	//generate the list content and take 5 random posts from the pool $id_list_a
        $output = '';
        $random_id_a = array();
        for($i=1;$i<=5;$i++){
            
            /*
             * avoid to include the same id multiple times in the list of random
             * IDs taken from the pool
             */
            do{
                $rand_key = array_rand($id_list_a, 1);
                $random_id = $id_list_a[$rand_key];
            }while(in_array($random_id, $random_id_a));

            $output .= '<div class="ila-interlinks-suggestions-link"><a href="' . esc_url( get_permalink($random_id)) . '">' . esc_attr( get_the_title($random_id) ) . '</a></div>';
            $random_id_a[] = $random_id;
            
        }
            
        //send the output
        echo esc_html($output);
        die();
        
    }

	/*
	 * Ajax handler used to generate the content of the "Interlinks Optimization" meta box.
	 */
	public function generate_interlinks_optimization(){

		//check the referer
		if(!check_ajax_referer( 'ila', 'security', false )){echo esc_attr("Invalid AJAX Request"); die();}

		//check the capability
	//	if(!current_user_can(get_option( $this->shared->get('slug') . "_interlinks_optimization_mb_required_capability"))){echo "Invalid Capability"; die();}

		//get data
		$post_id = intval(sanitize_text_field($_POST['post_id']), 10);

		//generate the HTML of the meta-box
		//$output = $this->shared->generate_interlinks_optimization_metabox_html(get_post($post_id));

        $output = $this->shared->list_in_out_links($post_id);

		//send the output
		echo esc_html($output);
		die();

	}

    
}