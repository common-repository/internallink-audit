<?php

/*
 * this class should be used to work with the administrative side of wordpress
 */
class Ila_Admin
{
    const  ILA_MENUPAGE_SLUG = 'ila-dashboard' ;
    protected static  $instance = null ;
    private  $shared = null ;
    private  $screen_id_dashboard = null ;
    private  $screen_id_settingsm = null ;
    private  $screen_id_juice = null ;
    private  $screen_id_anchors = null ;
    private  $screen_id_hits = null ;
    private  $screen_id_wizard = null ;
    private  $screen_id_autolinks = null ;
    private  $screen_id_categories = null ;
    private  $screen_id_maintenance = null ;
    private  $screen_id_options = null ;
    private function __construct()
    {
        //assign an instance of the plugin info
        $this->shared = Ila_Shared::get_instance();
        //Load admin stylesheets and JavaScript
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        //Write in back end head
        add_action( 'admin_head', array( $this, 'wr_admin_head' ) );
        //Add the admin menu
        add_action( 'admin_menu', array( $this, 'me_add_admin_menu' ) );
        //Load the options API registrations and callbacks
        add_action( 'admin_init', array( $this, 'op_register_options' ) );
        //Add the meta box
        add_action( 'add_meta_boxes', array( $this, 'create_meta_box' ) );
        //Save the meta box
        add_action( 'save_post', array( $this, 'ila_save_meta_interlinks_options' ) );
        //Export CSV controller
        add_action( 'init', array( $this, 'export_csv_controller' ) );
        //this hook is triggered during the creation of a new blog
        add_action(
            'wpmu_new_blog',
            array( $this, 'new_blog_create_options_and_tables' ),
            10,
            6
        );
        //this hook is triggered during the deletion of a blog
        add_action(
            'delete_blog',
            array( $this, 'delete_blog_delete_options_and_tables' ),
            10,
            1
        );
        if ( !empty($_POST['hidden_action']) ) {
            switch ( $_POST['hidden_action'] ) {
                case 'ila_save_settings':
                    ila_admin::ignorewords_save();
                    break;
            }
        }
    }

    /*
     * return an istance of this class
     */
    public static function get_instance()
    {
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /*
     * write in the admin head
     */
    public function wr_admin_head()
    {
        echo  '<script type="text/javascript">' ;
        echo  'var ila_ajax_url = "' . esc_url(admin_url( 'admin-ajax.php' )) . '";' ;
        echo  'var ila_nonce = "' . esc_attr(wp_create_nonce( "ila" )) . '";' ;
        echo  'var ila_admin_url ="' . esc_url(get_admin_url()) . '";' ;
        echo  'var ila_loader_url ="' . esc_url($this->shared->get( 'url' )) . 'admin/assets/img/ajax-loader.gif' . '";' ;
        echo  '</script>' ;
    }

    public function enqueue_admin_styles()
    {
        $screen = get_current_screen();
        //menu dashboard

        if ( $screen->id == $this->screen_id_dashboard ) {
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-framework-menu',
                $this->shared->get( 'url' ) . 'admin/assets/css/framework/menu.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-menu-dashboard',
                $this->shared->get( 'url' ) . 'admin/assets/css/menu-dashboard.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-jquery-ui-tooltip',
                $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-tooltip.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-ila_grid',
                $this->shared->get( 'url' ) . 'admin/assets/css/ila_grid.css',
                array(),
                $this->shared->get( 'ver' )
            );
            //Chosen
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-chosen',
                $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-chosen-custom',
                $this->shared->get( 'url' ) . 'admin/assets/css/chosen-custom.css',
                array(),
                $this->shared->get( 'ver' )
            );
        }

        //menu settings

        if ( $screen->id == $this->screen_id_settingsm ) {
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-framework-menu',
                $this->shared->get( 'url' ) . 'admin/assets/css/framework/menu.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-menu-dashboard',
                $this->shared->get( 'url' ) . 'admin/assets/css/menu-dashboard.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-jquery-ui-tooltip',
                $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-tooltip.css',
                array(),
                $this->shared->get( 'ver' )
            );
            //Chosen
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-chosen',
                $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-chosen-custom',
                $this->shared->get( 'url' ) . 'admin/assets/css/chosen-custom.css',
                array(),
                $this->shared->get( 'ver' )
            );
        }

        //menu juice

        if ( $screen->id == $this->screen_id_juice ) {
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-framework-menu',
                $this->shared->get( 'url' ) . 'admin/assets/css/framework/menu.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-menu-dashboard',
                $this->shared->get( 'url' ) . 'admin/assets/css/menu-dashboard.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-jquery-ui-tooltip',
                $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-tooltip.css',
                array(),
                $this->shared->get( 'ver' )
            );
            //Chosen
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-chosen',
                $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-chosen-custom',
                $this->shared->get( 'url' ) . 'admin/assets/css/chosen-custom.css',
                array(),
                $this->shared->get( 'ver' )
            );
        }

        //menu hits

        if ( $screen->id == $this->screen_id_hits ) {
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-framework-menu',
                $this->shared->get( 'url' ) . 'admin/assets/css/framework/menu.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-menu-hits',
                $this->shared->get( 'url' ) . 'admin/assets/css/menu-hits.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-jquery-ui-tooltip',
                $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-tooltip.css',
                array(),
                $this->shared->get( 'ver' )
            );
        }

        //menu wizard

        if ( $screen->id == $this->screen_id_wizard ) {
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-framework-menu',
                $this->shared->get( 'url' ) . 'admin/assets/css/framework/menu.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-menu-wizard',
                $this->shared->get( 'url' ) . 'admin/assets/css/menu-wizard.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-jquery-ui-tooltip',
                $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-tooltip.css',
                array(),
                $this->shared->get( 'ver' )
            );
            //Chosen
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-chosen',
                $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-chosen-custom',
                $this->shared->get( 'url' ) . 'admin/assets/css/chosen-custom.css',
                array(),
                $this->shared->get( 'ver' )
            );
            //Handsontable
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-handsontable-full',
                $this->shared->get( 'url' ) . 'admin/assets/inc/handsontable/handsontable.full.min.css',
                array(),
                $this->shared->get( 'ver' )
            );
        }

        //menu autolinks

        if ( $screen->id == $this->screen_id_autolinks ) {
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-framework-menu',
                $this->shared->get( 'url' ) . 'admin/assets/css/framework/menu.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-menu-autolinks',
                $this->shared->get( 'url' ) . 'admin/assets/css/menu-autolinks.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-jquery-ui-tooltip',
                $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-tooltip.css',
                array(),
                $this->shared->get( 'ver' )
            );
            //Chosen
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-chosen',
                $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-chosen-custom',
                $this->shared->get( 'url' ) . 'admin/assets/css/chosen-custom.css',
                array(),
                $this->shared->get( 'ver' )
            );
        }

        //menu categories

        if ( $screen->id == $this->screen_id_categories ) {
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-framework-menu',
                $this->shared->get( 'url' ) . 'admin/assets/css/framework/menu.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-menu-categories',
                $this->shared->get( 'url' ) . 'admin/assets/css/menu-categories.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-jquery-ui-tooltip',
                $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-tooltip.css',
                array(),
                $this->shared->get( 'ver' )
            );
        }

        //Menu Maintenance

        if ( $screen->id == $this->screen_id_maintenance ) {
            //Framework Menu
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-framework-menu',
                $this->shared->get( 'url' ) . 'admin/assets/css/framework/menu.css',
                array(),
                $this->shared->get( 'ver' )
            );
            //jQuery UI Dialog
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-jquery-ui-dialog',
                $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-dialog.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-jquery-ui-dialog-custom',
                $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-dialog-custom.css',
                array(),
                $this->shared->get( 'ver' )
            );
            //jQuery UI Tooltip
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-jquery-ui-tooltip',
                $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-tooltip.css',
                array(),
                $this->shared->get( 'ver' )
            );
            //Chosen
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-chosen',
                $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-chosen-custom',
                $this->shared->get( 'url' ) . 'admin/assets/css/chosen-custom.css',
                array(),
                $this->shared->get( 'ver' )
            );
        }

        //menu options

        if ( $screen->id == $this->screen_id_options ) {
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-framework-options',
                $this->shared->get( 'url' ) . 'admin/assets/css/framework/options.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-jquery-ui-tooltip',
                $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-tooltip.css',
                array(),
                $this->shared->get( 'ver' )
            );
            //Chosen
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-chosen',
                $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-chosen-custom',
                $this->shared->get( 'url' ) . 'admin/assets/css/chosen-custom.css',
                array(),
                $this->shared->get( 'ver' )
            );
        }

        /*
         * Load the post editor CSS if at least one of the three meta box is
         * enabled with the current $screen->id
         */
        $load_post_editor_css = false;
        $interlinks_options_post_types = preg_replace( '/\\s+/', '', get_option( $this->shared->get( 'slug' ) . '_interlinks_options_post_types' ) );
        $interlinks_options_post_types_a = explode( ',', $interlinks_options_post_types );
        if ( in_array( $screen->id, $interlinks_options_post_types_a ) ) {
            $load_post_editor_css = true;
        }
        $interlinks_optimization_post_types = preg_replace( '/\\s+/', '', get_option( $this->shared->get( 'slug' ) . '_interlinks_optimization_post_types' ) );
        $interlinks_optimization_post_types_a = explode( ',', $interlinks_optimization_post_types );
        if ( in_array( $screen->id, $interlinks_optimization_post_types_a ) ) {
            $load_post_editor_css = true;
        }
        $interlinks_suggestions_post_types = preg_replace( '/\\s+/', '', get_option( $this->shared->get( 'slug' ) . '_interlinks_suggestions_post_types' ) );
        $interlinks_suggestions_post_types_a = explode( ',', $interlinks_suggestions_post_types );
        if ( in_array( $screen->id, $interlinks_suggestions_post_types_a ) ) {
            $load_post_editor_css = true;
        }

        if ( $load_post_editor_css ) {
            //JQuery UI Tooltips
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-jquery-ui-tooltip',
                $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-tooltip.css',
                array(),
                $this->shared->get( 'ver' )
            );
            //Post Editor CSS
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-post-editor',
                $this->shared->get( 'url' ) . 'admin/assets/css/post-editor.css',
                array(),
                $this->shared->get( 'ver' )
            );
            //Chosen
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-chosen',
                $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.css',
                array(),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_style(
                $this->shared->get( 'slug' ) . '-chosen-custom',
                $this->shared->get( 'url' ) . 'admin/assets/css/chosen-custom.css',
                array(),
                $this->shared->get( 'ver' )
            );
        }

    }

    /*
     * enqueue admin-specific javascript
     */
    public function enqueue_admin_scripts()
    {
        $wp_localize_script_data = array(
            'deleteText'             => esc_attr__( 'Delete', 'ila' ),
            'cancelText'             => esc_attr__( 'Cancel', 'ila' ),
            'chooseAnOptionText'     => esc_attr__( 'Choose an Option ...', 'ila' ),
            'wizardRows'             => intval( get_option( $this->shared->get( 'slug' ) . '_wizard_rows' ), 10 ),
            'closeText'              => esc_attr__( 'Close', 'ila' ),
            'postText'               => esc_attr__( 'Post', 'ila' ),
            'anchorTextText'         => esc_attr__( 'Anchor Text', 'ila' ),
            'juiceText'              => esc_attr__( 'Juice (Value)', 'ila' ),
            'juiceVisualText'        => esc_attr__( 'Juice (Visual)', 'ila' ),
            'postTooltipText'        => esc_attr__( 'The post that includes the link.', 'ila' ),
            'anchorTextTooltipText'  => esc_attr__( 'The anchor text of the link.', 'ila' ),
            'juiceTooltipText'       => esc_attr__( 'The link juice generated by the link.', 'ila' ),
            'juiceVisualTooltipText' => esc_attr__( 'The visual representation of the link juice generated by the link.', 'ila' ),
            'juiceModalTitleText'    => esc_attr__( 'Internal Inbound Links for', 'ila' ),
            'itemsText'              => esc_attr__( 'items', 'ila' ),
        );
        $screen = get_current_screen();
        //menu dashboard

        if ( $screen->id == $this->screen_id_dashboard ) {
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-menu-dashboard',
                $this->shared->get( 'url' ) . 'admin/assets/js/menu-dashboard.js',
                'jquery',
                $this->shared->get( 'ver' )
            );
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-sweetalert',
                $this->shared->get( 'url' ) . 'admin/assets/js/sweetalert.js',
                'jquery',
                $this->shared->get( 'ver' )
            );
            wp_enqueue_script( 'jquery-ui-tooltip' );
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-jquery-ui-tooltip-init',
                $this->shared->get( 'url' ) . 'admin/assets/js/jquery-ui-tooltip-init.js',
                'jquery',
                $this->shared->get( 'ver' )
            );
            //Chosen
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-chosen',
                $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.js',
                array( 'jquery' ),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-chosen-init',
                $this->shared->get( 'url' ) . 'admin/assets/js/chosen-init.js',
                array( 'jquery' ),
                $this->shared->get( 'ver' )
            );
            wp_localize_script( $this->shared->get( 'slug' ) . '-chosen-init', 'objectL10n', $wp_localize_script_data );
        }

         // setting js
        if($screen->id == $this->screen_id_settingsm)
        {
           wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-menu-setting',
                $this->shared->get( 'url' ) . 'admin/assets/js/menu-dashboard.js',
                'jquery',
                $this->shared->get( 'ver' )
            );
             wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-sweetalert',
                $this->shared->get( 'url' ) . 'admin/assets/js/sweetalert.js',
                'jquery',
                $this->shared->get( 'ver' )
            );
        }

        //menu juice

        if ( $screen->id == $this->screen_id_juice ) {
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-menu-dashboard',
                $this->shared->get( 'url' ) . 'admin/assets/js/menu-dashboard.js',
                'jquery',
                $this->shared->get( 'ver' )
            );
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-sweetalert',
                $this->shared->get( 'url' ) . 'admin/assets/js/sweetalert.js',
                'jquery',
                $this->shared->get( 'ver' )
            );
            wp_enqueue_script( 'jquery-ui-tooltip' );
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-jquery-ui-tooltip-init',
                $this->shared->get( 'url' ) . 'admin/assets/js/jquery-ui-tooltip-init.js',
                'jquery',
                $this->shared->get( 'ver' )
            );
            //Chosen
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-chosen',
                $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.js',
                array( 'jquery' ),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-chosen-init',
                $this->shared->get( 'url' ) . 'admin/assets/js/chosen-init.js',
                array( 'jquery' ),
                $this->shared->get( 'ver' )
            );
            wp_localize_script( $this->shared->get( 'slug' ) . '-chosen-init', 'objectL10n', $wp_localize_script_data );
        }

        //menu anchors

        if ( $screen->id == $this->screen_id_anchors ) {
            wp_enqueue_script( 'jquery-ui-tooltip' );
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-jquery-ui-tooltip-init',
                $this->shared->get( 'url' ) . 'admin/assets/js/jquery-ui-tooltip-init.js',
                'jquery',
                $this->shared->get( 'ver' )
            );
        }

        //menu hits

        if ( $screen->id == $this->screen_id_hits ) {
            wp_enqueue_script( 'jquery-ui-tooltip' );
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-jquery-ui-tooltip-init',
                $this->shared->get( 'url' ) . 'admin/assets/js/jquery-ui-tooltip-init.js',
                'jquery',
                $this->shared->get( 'ver' )
            );
        }

        //menu wizard

        if ( $screen->id == $this->screen_id_wizard ) {
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-menu-wizard',
                $this->shared->get( 'url' ) . 'admin/assets/js/menu-wizard.js',
                'jquery',
                $this->shared->get( 'ver' )
            );
            wp_localize_script( $this->shared->get( 'slug' ) . '-menu-wizard', 'objectL10n', $wp_localize_script_data );
            wp_enqueue_script( 'jquery-ui-tooltip' );
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-jquery-ui-tooltip-init',
                $this->shared->get( 'url' ) . 'admin/assets/js/jquery-ui-tooltip-init.js',
                'jquery',
                $this->shared->get( 'ver' )
            );
            //Chosen
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-chosen',
                $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.js',
                array( 'jquery' ),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-chosen-init',
                $this->shared->get( 'url' ) . 'admin/assets/js/chosen-init.js',
                array( 'jquery' ),
                $this->shared->get( 'ver' )
            );
            wp_localize_script( $this->shared->get( 'slug' ) . '-chosen-init', 'objectL10n', $wp_localize_script_data );
            //Handsontable
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-handsontable-full',
                $this->shared->get( 'url' ) . 'admin/assets/inc/handsontable/handsontable.full.min.js',
                array( 'jquery' ),
                $this->shared->get( 'ver' )
            );
        }

        //menu autolinks

        if ( $screen->id == $this->screen_id_autolinks ) {
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-menu-autolinks',
                $this->shared->get( 'url' ) . 'admin/assets/js/menu-autolinks.js',
                'jquery',
                $this->shared->get( 'ver' )
            );
            wp_enqueue_script( 'jquery-ui-tooltip' );
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-jquery-ui-tooltip-init',
                $this->shared->get( 'url' ) . 'admin/assets/js/jquery-ui-tooltip-init.js',
                'jquery',
                $this->shared->get( 'ver' )
            );
            //Chosen
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-chosen',
                $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.js',
                array( 'jquery' ),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-chosen-init',
                $this->shared->get( 'url' ) . 'admin/assets/js/chosen-init.js',
                array( 'jquery' ),
                $this->shared->get( 'ver' )
            );
            wp_localize_script( $this->shared->get( 'slug' ) . '-chosen-init', 'objectL10n', $wp_localize_script_data );
        }

        //menu categories

        if ( $screen->id == $this->screen_id_categories ) {
            wp_enqueue_script( 'jquery-ui-tooltip' );
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-jquery-ui-tooltip-init',
                $this->shared->get( 'url' ) . 'admin/assets/js/jquery-ui-tooltip-init.js',
                'jquery',
                $this->shared->get( 'ver' )
            );
        }

        //Menu Maintenance

        if ( $screen->id == $this->screen_id_maintenance ) {
            //Maintenance Menu
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-menu-maintenance',
                $this->shared->get( 'url' ) . 'admin/assets/js/menu-maintenance.js',
                array( 'jquery', 'jquery-ui-dialog' ),
                $this->shared->get( 'ver' )
            );
            wp_localize_script( $this->shared->get( 'slug' ) . '-menu-maintenance', 'objectL10n', $wp_localize_script_data );
            //jQuery UI Tooltip
            wp_enqueue_script( 'jquery-ui-tooltip' );
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-jquery-ui-tooltip-init',
                $this->shared->get( 'url' ) . 'admin/assets/js/jquery-ui-tooltip-init.js',
                'jquery',
                $this->shared->get( 'ver' )
            );
            //Chosen
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-chosen',
                $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.js',
                array( 'jquery' ),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-jquery-ui-chosen-init',
                $this->shared->get( 'url' ) . 'admin/assets/js/chosen-init.js',
                array( 'jquery' ),
                $this->shared->get( 'ver' )
            );
            wp_localize_script( $this->shared->get( 'slug' ) . '-chosen-init', 'objectL10n', $wp_localize_script_data );
        }

        //menu options

        if ( $screen->id == $this->screen_id_options ) {
            wp_enqueue_script( 'jquery-ui-tooltip' );
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-jquery-ui-tooltip-init',
                $this->shared->get( 'url' ) . 'admin/assets/js/jquery-ui-tooltip-init.js',
                'jquery',
                $this->shared->get( 'ver' )
            );
            //Chosen
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-chosen',
                $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.js',
                array( 'jquery' ),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-chosen-init',
                $this->shared->get( 'url' ) . 'admin/assets/js/chosen-init.js',
                array( 'jquery' ),
                $this->shared->get( 'ver' )
            );
            wp_localize_script( $this->shared->get( 'slug' ) . '-chosen-init', 'objectL10n', $wp_localize_script_data );
        }

        /*
         * Load the post editor JS if at least one of the three meta box is
         * enabled with the current $screen->id
         */
        $load_post_editor_js = false;
        $interlinks_options_post_types = preg_replace( '/\\s+/', '', get_option( $this->shared->get( 'slug' ) . '_interlinks_options_post_types' ) );
        $interlinks_options_post_types_a = explode( ',', $interlinks_options_post_types );
        if ( in_array( $screen->id, $interlinks_options_post_types_a ) ) {
            $load_post_editor_js = true;
        }
        $interlinks_optimization_post_types = preg_replace( '/\\s+/', '', get_option( $this->shared->get( 'slug' ) . '_interlinks_optimization_post_types' ) );
        $interlinks_optimization_post_types_a = explode( ',', $interlinks_optimization_post_types );
        if ( in_array( $screen->id, $interlinks_optimization_post_types_a ) ) {
            $load_post_editor_js = true;
        }
        $interlinks_suggestions_post_types = preg_replace( '/\\s+/', '', get_option( $this->shared->get( 'slug' ) . '_interlinks_suggestions_post_types' ) );
        $interlinks_suggestions_post_types_a = explode( ',', $interlinks_suggestions_post_types );
        if ( in_array( $screen->id, $interlinks_suggestions_post_types_a ) ) {
            $load_post_editor_js = true;
        }

        if ( $load_post_editor_js ) {
            //JQuery UI Tooltips
            wp_enqueue_script( 'jquery-ui-tooltip' );
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-jquery-ui-tooltip-init',
                $this->shared->get( 'url' ) . 'admin/assets/js/jquery-ui-tooltip-init.js',
                'jquery',
                $this->shared->get( 'ver' )
            );
            //Post Editor Js
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-post-editor',
                $this->shared->get( 'url' ) . 'admin/assets/js/post-editor.js',
                'jquery',
                $this->shared->get( 'ver' )
            );
            //Chosen
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-chosen',
                $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.js',
                array( 'jquery' ),
                $this->shared->get( 'ver' )
            );
            wp_enqueue_script(
                $this->shared->get( 'slug' ) . '-chosen-init',
                $this->shared->get( 'url' ) . 'admin/assets/js/chosen-init.js',
                array( 'jquery' ),
                $this->shared->get( 'ver' )
            );
            wp_localize_script( $this->shared->get( 'slug' ) . '-chosen-init', 'objectL10n', $wp_localize_script_data );
        }

    }

    /*
     * plugin activation
     */
    public function ac_activate( $networkwide )
    {
        /*
         * delete options and tables for all the sites in the network
         */

        if ( function_exists( 'is_multisite' ) and is_multisite() ) {
            /*
             * if this is a "Network Activation" create the options and tables
             * for each blog
             */

            if ( $networkwide ) {
                //get the current blog id
                global  $wpdb ;
                $current_blog = $wpdb->blogid;
                //create an array with all the blog ids
                $blogids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
                //iterate through all the blogs
                foreach ( $blogids as $blog_id ) {
                    //swith to the iterated blog
                    switch_to_blog( $blog_id );
                    //create options and tables for the iterated blog
                    $this->ac_initialize_options();
                    $this->ac_create_database_tables();
                }
                //switch to the current blog
                switch_to_blog( $current_blog );
            } else {
                /*
                 * if this is not a "Network Activation" create options and
                 * tables only for the current blog
                 */
                $this->ac_initialize_options();
                $this->ac_create_database_tables();
            }

        } else {
            /*
             * if this is not a multisite installation create options and
             * tables only for the current blog
             */
            $this->ac_initialize_options();
            $this->ac_create_database_tables();
        }

    }

    //create the options and tables for the newly created blog
    public function new_blog_create_options_and_tables(
        $blog_id,
        $user_id,
        $domain,
        $path,
        $site_id,
        $meta
    )
    {
        global  $wpdb ;
        /*
         * if the plugin is "Network Active" create the options and tables for
         * this new blog
         */

        if ( is_plugin_active_for_network( 'interlinks-manager/init.php' ) ) {
            //get the id of the current blog
            $current_blog = $wpdb->blogid;
            //switch to the blog that is being activated
            switch_to_blog( $blog_id );
            //create options and database tables for the new blog
            $this->ac_initialize_options();
            $this->ac_create_database_tables();
            //switch to the current blog
            switch_to_blog( $current_blog );
        }

    }

    //delete options and tables for the deleted blog
    public function delete_blog_delete_options_and_tables( $blog_id )
    {
        global  $wpdb ;
        //get the id of the current blog
        $current_blog = $wpdb->blogid;
        //switch to the blog that is being activated
        switch_to_blog( $blog_id );
        //create options and database tables for the new blog
        $this->un_delete_options();
        $this->un_delete_database_tables();
        //switch to the current blog
        switch_to_blog( $current_blog );
    }

    /*
     * initialize plugin options
     */
    private function ac_initialize_options()
    {
        //database version -----------------------------------------------------
        add_option( $this->shared->get( 'slug' ) . "_database_version", "0" );
        //AIL ------------------------------------------------------------------
        add_option( $this->shared->get( 'slug' ) . '_default_category_id', "0" );
        add_option( $this->shared->get( 'slug' ) . '_default_title', "" );
        add_option( $this->shared->get( 'slug' ) . '_default_open_new_tab', "0" );
        add_option( $this->shared->get( 'slug' ) . '_default_use_nofollow', "0" );
        add_option( $this->shared->get( 'slug' ) . '_default_activate_post_types', "post, page" );
        add_option( $this->shared->get( 'slug' ) . '_default_case_insensitive_search', "0" );
        add_option( $this->shared->get( 'slug' ) . '_default_string_before', "1" );
        add_option( $this->shared->get( 'slug' ) . '_default_string_after', "1" );
        add_option( $this->shared->get( 'slug' ) . '_default_max_number_autolinks_per_keyword', "100" );
        add_option( $this->shared->get( 'slug' ) . '_default_priority', "0" );
        //suggestions
        add_option( $this->shared->get( 'slug' ) . '_suggestions_pool_post_types', "post, page" );
        add_option( $this->shared->get( 'slug' ) . '_suggestions_pool_size', 50 );
        add_option( $this->shared->get( 'slug' ) . '_suggestions_titles', "consider" );
        add_option( $this->shared->get( 'slug' ) . '_suggestions_categories', "consider" );
        add_option( $this->shared->get( 'slug' ) . '_suggestions_tags', "consider" );
        add_option( $this->shared->get( 'slug' ) . '_suggestions_post_type', "consider" );
        //optimization ---------------------------------------------------------
        add_option( $this->shared->get( 'slug' ) . '_optimization_num_of_characters', 1000 );
        add_option( $this->shared->get( 'slug' ) . '_optimization_delta', 2 );
        //juice ----------------------------------------------------------------
        add_option( $this->shared->get( 'slug' ) . '_default_seo_power', 1000 );
        add_option( $this->shared->get( 'slug' ) . '_penality_per_position_percentage', '1' );
        add_option( $this->shared->get( 'slug' ) . '_remove_link_to_anchor', "1" );
        add_option( $this->shared->get( 'slug' ) . '_remove_url_parameters', "0" );
        //tracking -------------------------------------------------------------
        add_option( $this->shared->get( 'slug' ) . '_track_internal_links', "1" );
        //analysis ----------------------------------------------------------
        add_option( $this->shared->get( 'slug' ) . '_set_max_execution_time', "1" );
        add_option( $this->shared->get( 'slug' ) . '_max_execution_time_value', "0" );
        add_option( $this->shared->get( 'slug' ) . '_set_memory_limit', "1" );
        add_option( $this->shared->get( 'slug' ) . '_memory_limit_value', "0" );
        add_option( $this->shared->get( 'slug' ) . '_limit_posts_analysis', "50" );
        add_option( $this->shared->get( 'slug' ) . '_dashboard_post_types', "post, page" );
        add_option( $this->shared->get( 'slug' ) . '_juice_post_types', "post, page" );
        //meta boxes -----------------------------------------------------------
        add_option( $this->shared->get( 'slug' ) . '_interlinks_options_post_types', "post, page" );
        add_option( $this->shared->get( 'slug' ) . '_interlinks_optimization_post_types', "post, page" );
        add_option( $this->shared->get( 'slug' ) . '_interlinks_suggestions_post_types', "post, page" );
        //capabilities ----------------------------------------------------------
        add_option( $this->shared->get( 'slug' ) . '_dashboard_menu_required_capability', "edit_others_posts" );
        add_option( $this->shared->get( 'slug' ) . '_juice_menu_required_capability', "edit_others_posts" );
        add_option( $this->shared->get( 'slug' ) . '_settingsm_menu_required_capability', "edit_others_posts" );
        add_option( $this->shared->get( 'slug' ) . '_hits_menu_required_capability', "edit_others_posts" );
        add_option( $this->shared->get( 'slug' ) . '_wizard_menu_required_capability', "edit_others_posts" );
        add_option( $this->shared->get( 'slug' ) . '_ail_menu_required_capability', "edit_others_posts" );
        add_option( $this->shared->get( 'slug' ) . '_categories_menu_required_capability', "edit_others_posts" );
        add_option( $this->shared->get( 'slug' ) . '_maintenance_menu_required_capability', "edit_others_posts" );
        add_option( $this->shared->get( 'slug' ) . '_interlinks_options_mb_required_capability', "edit_others_posts" );
        add_option( $this->shared->get( 'slug' ) . '_interlinks_optimization_mb_required_capability', "edit_posts" );
        add_option( $this->shared->get( 'slug' ) . '_interlinks_suggestions_mb_required_capability', "edit_posts" );
        //Advanced
        add_option( $this->shared->get( 'slug' ) . '_default_enable_ail_on_post', "1" );
        add_option( $this->shared->get( 'slug' ) . '_filter_priority', "1" );
        add_option( $this->shared->get( 'slug' ) . '_ail_test_mode', "0" );
        add_option( $this->shared->get( 'slug' ) . '_random_prioritization', "0" );
        add_option( $this->shared->get( 'slug' ) . '_ignore_self_ail', "1" );
        add_option( $this->shared->get( 'slug' ) . '_general_limit_mode', "1" );
        add_option( $this->shared->get( 'slug' ) . '_characters_per_autolink', "200" );
        add_option( $this->shared->get( 'slug' ) . '_max_number_autolinks_per_post', "100" );
        add_option( $this->shared->get( 'slug' ) . '_general_limit_subtract_mil', "0" );
        add_option( $this->shared->get( 'slug' ) . '_same_url_limit', "100" );
        add_option( $this->shared->get( 'slug' ) . '_wizard_rows', "500" );
        //By default the following HTML tags are protected:
        $protected_tags = array(
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'a',
            'img',
            'ul',
            'ol',
            'span',
            'pre',
            'code',
            'table',
            'iframe',
            'script'
        );
        add_option( $this->shared->get( 'slug' ) . '_protected_tags', $protected_tags );
        /*
         * By default all the Gutenberg Blocks except the following are protected:
         *
         * - Paragraph
         * - List
         * - Text Columns
         */
        $default_protected_gutenberg_blocks = array(
            //'paragraph',
            'image',
            'heading',
            'gallery',
            //'list',
            'quote',
            'audio',
            'cover-image',
            'subhead',
            'video',
            'code',
            'html',
            'preformatted',
            'pullquote',
            'table',
            'verse',
            'button',
            'columns',
            'more',
            'nextpage',
            'separator',
            'spacer',
            //'text-columns',
            'shortcode',
            'categories',
            'latest-posts',
            'embed',
            'core-embed/twitter',
            'core-embed/youtube',
            'core-embed/facebook',
            'core-embed/instagram',
            'core-embed/wordpress',
            'core-embed/soundcloud',
            'core-embed/spotify',
            'core-embed/flickr',
            'core-embed/vimeo',
            'core-embed/animoto',
            'core-embed/cloudup',
            'core-embed/collegehumor',
            'core-embed/dailymotion',
            'core-embed/funnyordie',
            'core-embed/hulu',
            'core-embed/imgur',
            'core-embed/issuu',
            'core-embed/kickstarter',
            'core-embed/meetup-com',
            'core-embed/mixcloud',
            'core-embed/photobucket',
            'core-embed/polldaddy',
            'core-embed/reddit',
            'core-embed/reverbnation',
            'core-embed/screencast',
            'core-embed/scribd',
            'core-embed/slideshare',
            'core-embed/smugmug',
            'core-embed/speaker',
            'core-embed/ted',
            'core-embed/tumblr',
            'core-embed/videopress',
            'core-embed/wordpress-tv',
        );
        add_option( $this->shared->get( 'slug' ) . '_protected_gutenberg_blocks', $default_protected_gutenberg_blocks );
        add_option( $this->shared->get( 'slug' ) . '_protected_gutenberg_custom_blocks', '' );
        add_option( $this->shared->get( 'slug' ) . '_protected_gutenberg_custom_void_blocks', '' );
        add_option( $this->shared->get( 'slug' ) . '_pagination_dashboard_menu', "10" );
        add_option( $this->shared->get( 'slug' ) . '_pagination_juice_menu', "100" );
        add_option( $this->shared->get( 'slug' ) . '_pagination_hits_menu', "10" );
        add_option( $this->shared->get( 'slug' ) . '_pagination_ail_menu', "10" );
        add_option( $this->shared->get( 'slug' ) . '_pagination_categories_menu', "10" );
    }

    /*
     * create the plugin database tables
     */
    private function ac_create_database_tables()
    {
        //check database version and create the database

        if ( intval( get_option( $this->shared->get( 'slug' ) . '_database_version' ), 10 ) < 3 ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            //create *prefix*_archive
            global  $wpdb ;
            $table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_archive";
            $sql = "CREATE TABLE {$table_name} (\r\n                id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,\r\n                post_id bigint(20) NOT NULL DEFAULT '0',\r\n                post_title text DEFAULT '',\r\n                post_type varchar(20) NOT NULL DEFAULT '',\r\n                post_date datetime DEFAULT NULL,\r\n                ol_count bigint(20) NOT NULL DEFAULT '0',\r\n                manual_interlinks TEXT,\r\n                auto_interlinks bigint(20) NOT NULL DEFAULT '0',\r\n                content_length bigint(20) NOT NULL DEFAULT '0',\r\n                il_count bigint(20) NOT NULL DEFAULT '0',\r\n                recommended_interlinks TEXT,\r\n                optimization tinyint(1) NOT NULL DEFAULT '0'\r\n            )\r\n            COLLATE = utf8_general_ci\r\n            ";
            dbDelta( $sql );
            //Update database version
            update_option( $this->shared->get( 'slug' ) . '_database_version', "2" );
        }

    }

    private function ac_create_ignore_database_tables()
    {
        //check database version and create the database

        if ( intval( get_option( $this->shared->get( 'slug' ) . '_database_version' ), 10 ) < 3 ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            //create *prefix*_archive
            global  $wpdb ;
            $table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_ignore_words";
            $sql = "CREATE TABLE {$table_name} (\r\n                id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,\r\n                words TEXT\r\n            )\r\n            COLLATE = utf8_general_ci\r\n            ";
            dbDelta( $sql );
            //Update database version
            update_option( $this->shared->get( 'slug' ) . '_database_version', "2" );
        }

    }

    /*
     * plugin delete
     */
    public static function un_delete()
    {
        /*
         * delete options and tables for all the sites in the network
         */

        if ( function_exists( 'is_multisite' ) and is_multisite() ) {
            //get the current blog id
            global  $wpdb ;
            $current_blog = $wpdb->blogid;
            //create an array with all the blog ids
            $blogids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
            //iterate through all the blogs
            foreach ( $blogids as $blog_id ) {
                //swith to the iterated blog
                switch_to_blog( $blog_id );
                //create options and tables for the iterated blog
                Ila_Admin::un_delete_options();
                Ila_Admin::un_delete_database_tables();
            }
            //switch to the current blog
            switch_to_blog( $current_blog );
        } else {
            /*
             * if this is not a multisite installation delete options and
             * tables only for the current blog
             */
            Ila_Admin::un_delete_options();
            Ila_Admin::un_delete_database_tables();
        }

    }

    /*
     * delete plugin options
     */
    public static function un_delete_options()
    {
        //assign an instance of Ila_Shared
        $shared = Ila_Shared::get_instance();
        //database version -----------------------------------------------------
        delete_option( $shared->get( 'slug' ) . "_database_version", "0" );
        //AIL ------------------------------------------------------------------
        delete_option( $shared->get( 'slug' ) . '_default_category_id' );
        delete_option( $shared->get( 'slug' ) . '_default_title' );
        delete_option( $shared->get( 'slug' ) . '_default_open_new_tab' );
        delete_option( $shared->get( 'slug' ) . '_default_use_nofollow' );
        delete_option( $shared->get( 'slug' ) . '_default_activate_post_types' );
        delete_option( $shared->get( 'slug' ) . '_default_case_insensitive_search' );
        delete_option( $shared->get( 'slug' ) . '_default_string_before' );
        delete_option( $shared->get( 'slug' ) . '_default_string_after' );
        delete_option( $shared->get( 'slug' ) . '_default_max_number_autolinks_per_keyword' );
        delete_option( $shared->get( 'slug' ) . '_default_priority' );
        //suggestions
        delete_option( $shared->get( 'slug' ) . '_suggestions_pool_post_types' );
        delete_option( $shared->get( 'slug' ) . '_suggestions_pool_size' );
        delete_option( $shared->get( 'slug' ) . '_suggestions_titles' );
        delete_option( $shared->get( 'slug' ) . '_suggestions_categories' );
        delete_option( $shared->get( 'slug' ) . '_suggestions_tags' );
        delete_option( $shared->get( 'slug' ) . '_suggestions_post_type' );
        //optimization ---------------------------------------------------------
        delete_option( $shared->get( 'slug' ) . '_optimization_num_of_characters' );
        delete_option( $shared->get( 'slug' ) . '_optimization_delta' );
        //juice ----------------------------------------------------------------
        delete_option( $shared->get( 'slug' ) . '_default_seo_power' );
        delete_option( $shared->get( 'slug' ) . '_penality_per_position_percentage' );
        delete_option( $shared->get( 'slug' ) . '_remove_link_to_anchor' );
        delete_option( $shared->get( 'slug' ) . '_remove_url_parameters' );
        //tracking -------------------------------------------------------------
        delete_option( $shared->get( 'slug' ) . '_track_internal_links' );
        //analysis -------------------------------------------------------------
        delete_option( $shared->get( 'slug' ) . '_set_max_execution_time' );
        delete_option( $shared->get( 'slug' ) . '_max_execution_time_value' );
        delete_option( $shared->get( 'slug' ) . '_set_memory_limit' );
        delete_option( $shared->get( 'slug' ) . '_memory_limit_value' );
        delete_option( $shared->get( 'slug' ) . '_limit_posts_analysis' );
        delete_option( $shared->get( 'slug' ) . '_dashboard_post_types' );
        delete_option( $shared->get( 'slug' ) . '_juice_post_types' );
        //meta boxes -----------------------------------------------------------
        delete_option( $shared->get( 'slug' ) . '_interlinks_options_post_types' );
        delete_option( $shared->get( 'slug' ) . '_interlinks_optimization_post_types' );
        delete_option( $shared->get( 'slug' ) . '_interlinks_suggestions_post_types' );
        //capabilities ----------------------------------------------------------
        delete_option( $shared->get( 'slug' ) . '_dashboard_menu_required_capability' );
        delete_option( $shared->get( 'slug' ) . '_juice_menu_required_capability' );
        delete_option( $shared->get( 'slug' ) . '_settingsm_menu_required_capability' );
        delete_option( $shared->get( 'slug' ) . '_hits_menu_required_capability' );
        delete_option( $shared->get( 'slug' ) . '_wizard_menu_required_capability' );
        delete_option( $shared->get( 'slug' ) . '_ail_menu_required_capability' );
        delete_option( $shared->get( 'slug' ) . '_categories_menu_required_capability' );
        delete_option( $shared->get( 'slug' ) . '_maintenance_menu_required_capability' );
        delete_option( $shared->get( 'slug' ) . '_interlinks_options_mb_required_capability' );
        delete_option( $shared->get( 'slug' ) . '_interlinks_optimization_mb_required_capability' );
        delete_option( $shared->get( 'slug' ) . '_interlinks_suggestions_mb_required_capability' );
        //advanced -----------------------------------------------------------------------------------------------------
        delete_option( $shared->get( 'slug' ) . '_default_enable_ail_on_post' );
        delete_option( $shared->get( 'slug' ) . '_filter_priority' );
        delete_option( $shared->get( 'slug' ) . '_ail_test_mode' );
        delete_option( $shared->get( 'slug' ) . '_random_prioritization' );
        delete_option( $shared->get( 'slug' ) . '_ignore_self_ail' );
        delete_option( $shared->get( 'slug' ) . '_general_limit_mode' );
        delete_option( $shared->get( 'slug' ) . '_characters_per_autolink' );
        delete_option( $shared->get( 'slug' ) . '_max_number_autolinks_per_post' );
        delete_option( $shared->get( 'slug' ) . '_general_limit_subtract_mil' );
        delete_option( $shared->get( 'slug' ) . '_same_url_limit' );
        delete_option( $shared->get( 'slug' ) . '_wizard_rows' );
        delete_option( $shared->get( 'slug' ) . '_protected_tags' );
        delete_option( $shared->get( 'slug' ) . '_protected_gutenberg_blocks' );
        delete_option( $shared->get( 'slug' ) . '_protected_gutenberg_custom_blocks' );
        delete_option( $shared->get( 'slug' ) . '_protected_gutenberg_custom_void_blocks' );
        delete_option( $shared->get( 'slug' ) . '_pagination_dashboard_menu' );
        delete_option( $shared->get( 'slug' ) . '_pagination_juice_menu' );
        delete_option( $shared->get( 'slug' ) . '_pagination_hits_menu' );
        delete_option( $shared->get( 'slug' ) . '_pagination_ail_menu' );
        delete_option( $shared->get( 'slug' ) . '_pagination_categories_menu' );
    }

    /*
     * delete plugin database tables
     */
    public static function un_delete_database_tables()
    {
        //assign an instance of Ila_Shared
        $shared = Ila_Shared::get_instance();
        global  $wpdb ;
        $table_name = $wpdb->prefix . $shared->get( 'slug' ) . "_archive";
        $sql = "DROP TABLE {$table_name}";
        $wpdb->query( $sql );
        // $table_name = $wpdb->prefix . $shared->get('slug') . "_juice";
        // $sql = "DROP TABLE $table_name";
        // $wpdb->query($sql);
        // $table_name = $wpdb->prefix . $shared->get('slug') . "_anchors";
        // $sql = "DROP TABLE $table_name";
        // $wpdb->query($sql);
        // $table_name = $wpdb->prefix . $shared->get('slug') . "_hits";
        // $sql = "DROP TABLE $table_name";
        // $wpdb->query($sql);
        // $table_name = $wpdb->prefix . $shared->get('slug') . "_autolinks";
        // $sql = "DROP TABLE $table_name";
        // $wpdb->query($sql);
        // $table_name = $wpdb->prefix . $shared->get('slug') . "_category";
        // $sql = "DROP TABLE $table_name";
        // $wpdb->query($sql);
    }

    /*
     * register the admin menu
     */
    public function me_add_admin_menu()
    {
        add_menu_page(
            esc_attr__( 'InternalLink Audit', 'ila' ),
            esc_attr__( 'InternalLink Audit', 'ila' ),
            get_option( $this->shared->get( 'slug' ) . "_dashboard_menu_required_capability" ),
            $this->shared->get( 'slug' ) . '-dashboard',
            array( $this, 'me_display_menu_dashboard' ),
            'dashicons-admin-links'
        );
        $this->screen_id_dashboard = add_submenu_page(
            $this->shared->get( 'slug' ) . '-dashboard',
            esc_attr__( 'InternalLink Audit - Dashboard', 'ila' ),
            esc_attr__( 'Dashboard', 'ila' ),
            get_option( $this->shared->get( 'slug' ) . '_dashboard_menu_required_capability' ),
            $this->shared->get( 'slug' ) . '-dashboard',
            array( $this, 'me_display_menu_dashboard' )
        );
        $this->screen_id_juice = add_submenu_page(
            $this->shared->get( 'slug' ) . '-dashboard',
            esc_attr__( 'InternalLink Audit - Audit', 'ila' ),
            esc_attr__( 'Audit', 'ila' ),
            get_option( $this->shared->get( 'slug' ) . '_juice_menu_required_capability' ),
            $this->shared->get( 'slug' ) . '-audit',
            array( $this, 'me_display_menu_juice' )
        );
        $this->screen_id_settingsm = add_submenu_page(
            $this->shared->get( 'slug' ) . '-dashboard',
            esc_attr__( 'InternalLink Audit - Settings', 'ila' ),
            esc_attr__( 'Settings', 'ila' ),
            get_option( $this->shared->get( 'slug' ) . '_settingsm_menu_required_capability' ),
            $this->shared->get( 'slug' ) . '-settings',
            array( $this, 'me_display_menu_settingsm' )
        );
        // add_submenu_page(
        //     $this->shared->get('slug') . '-dashboard',
        //     esc_attr__('IM - Upgrade', 'ila'),
        //     esc_attr__('Upgrade', 'ila'),
        //     // get_admin_url(null, 'admin.php?page=' . Ila_Admin::ILA_MENUPAGE_SLUG . '-pricing') . '" class="fs-submenu-item pricing upgrade-mode">&raquo; ' . __('Upgrade Now', 'internal-links') ,
        //     'manage_categories',
        //     'admin.php?page=' . Ila_Admin::ILA_MENUPAGE_SLUG . '-pricing'
        // );
    }

    /*
     * includes the dashboard view
     */
    public function me_display_menu_dashboard()
    {
        include_once 'view/dashboard.php';
    }

    /*
     * includes the juice view
     */
    public function me_display_menu_juice()
    {
        include_once 'view/audits.php';
    }

    public function me_display_menu_settingsm()
    {
        include_once 'view/settings.php';
    }

    /*
     * includes the anchors view
     */
    public function me_display_menu_anchors()
    {
        include_once 'view/anchors.php';
    }

    /*
     * includes the hits view
     */
    public function me_display_menu_hits()
    {
        include_once 'view/hits.php';
    }

    /*
     * includes the wizard view
     */
    public function me_display_menu_wizard()
    {
        include_once 'view/wizard.php';
    }

    /*
     * includes the autolinks view
     */
    public function me_display_menu_autolinks()
    {
        include_once 'view/autolinks.php';
    }

    /*
     * includes the categories view
     */
    public function me_display_menu_categories()
    {
        include_once 'view/categories.php';
    }

    /*
     * includes the maintenance view
     */
    public function me_display_menu_maintenance()
    {
        include_once 'view/maintenance.php';
    }

    /*
     * includes the options view
     */
    public function me_display_menu_options()
    {
        include_once 'view/options.php';
    }

    /*
     * register options
     */
    public function op_register_options()
    {
        //section ail ----------------------------------------------------------
        add_settings_section(
            'ila_ail_settings_section',
            NULL,
            NULL,
            'ila_ail_options'
        );
        add_settings_field(
            'default_category_id',
            esc_attr__( 'Category', 'ila' ),
            array( $this, 'default_category_id_callback' ),
            'ila_ail_options',
            'ila_ail_settings_section'
        );
        register_setting( 'ila_ail_options', 'ila_default_category_id', array( $this, 'default_category_id_validation' ) );
        add_settings_field(
            'default_title',
            esc_attr__( 'Title', 'ila' ),
            array( $this, 'default_title_callback' ),
            'ila_ail_options',
            'ila_ail_settings_section'
        );
        register_setting( 'ila_ail_options', 'ila_default_title', array( $this, 'default_title_validation' ) );
        add_settings_field(
            'default_open_new_tab',
            esc_attr__( 'Open New Tab', 'ila' ),
            array( $this, 'default_open_new_tab_callback' ),
            'ila_ail_options',
            'ila_ail_settings_section'
        );
        register_setting( 'ila_ail_options', 'ila_default_open_new_tab', array( $this, 'default_open_new_tab_validation' ) );
        add_settings_field(
            'default_use_nofollow',
            esc_attr__( 'Use Nofollow', 'ila' ),
            array( $this, 'default_use_nofollow_callback' ),
            'ila_ail_options',
            'ila_ail_settings_section'
        );
        register_setting( 'ila_ail_options', 'ila_default_use_nofollow', array( $this, 'default_use_nofollow_validation' ) );
        add_settings_field(
            'default_activate_post_types',
            esc_attr__( 'Post Types', 'ila' ),
            array( $this, 'default_activate_post_types_callback' ),
            'ila_ail_options',
            'ila_ail_settings_section'
        );
        register_setting( 'ila_ail_options', 'ila_default_activate_post_types', array( $this, 'default_activate_post_types_validation' ) );
        add_settings_field(
            'default_case_insensitive_search',
            esc_attr__( 'Case Insensitive Search', 'ila' ),
            array( $this, 'default_case_insensitive_search_callback' ),
            'ila_ail_options',
            'ila_ail_settings_section'
        );
        register_setting( 'ila_ail_options', 'ila_default_case_insensitive_search', array( $this, 'default_case_insensitive_search_validation' ) );
        add_settings_field(
            'default_string_before',
            esc_attr__( 'Left Boundary', 'ila' ),
            array( $this, 'default_string_before_callback' ),
            'ila_ail_options',
            'ila_ail_settings_section'
        );
        register_setting( 'ila_ail_options', 'ila_default_string_before', array( $this, 'default_string_before_validation' ) );
        add_settings_field(
            'default_string_after',
            esc_attr__( 'Right Boundary', 'ila' ),
            array( $this, 'default_string_after_callback' ),
            'ila_ail_options',
            'ila_ail_settings_section'
        );
        register_setting( 'ila_ail_options', 'ila_default_string_after', array( $this, 'default_string_after_validation' ) );
        add_settings_field(
            'default_max_number_autolinks_per_keyword',
            esc_attr__( 'Limit', 'ila' ),
            array( $this, 'default_max_number_autolinks_per_keyword_callback' ),
            'ila_ail_options',
            'ila_ail_settings_section'
        );
        register_setting( 'ila_ail_options', 'ila_default_max_number_autolinks_per_keyword', array( $this, 'default_max_number_autolinks_per_keyword_validation' ) );
        add_settings_field(
            'default_priority',
            esc_attr__( 'Priority', 'ila' ),
            array( $this, 'default_priority_callback' ),
            'ila_ail_options',
            'ila_ail_settings_section'
        );
        register_setting( 'ila_ail_options', 'ila_default_priority', array( $this, 'default_priority_validation' ) );
        //section suggestions --------------------------------------------------
        add_settings_section(
            'ila_suggestions_settings_section',
            NULL,
            NULL,
            'ila_suggestions_options'
        );
        add_settings_field(
            'suggestions_pool_post_types',
            esc_attr__( 'Source Post Types', 'ila' ),
            array( $this, 'suggestions_pool_post_types_callback' ),
            'ila_suggestions_options',
            'ila_suggestions_settings_section'
        );
        register_setting( 'ila_suggestions_options', 'ila_suggestions_pool_post_types', array( $this, 'suggestions_pool_post_types_validation' ) );
        add_settings_field(
            'suggestions_pool_size',
            esc_attr__( 'Results Pool Size', 'ila' ),
            array( $this, 'suggestions_pool_size_callback' ),
            'ila_suggestions_options',
            'ila_suggestions_settings_section'
        );
        register_setting( 'ila_suggestions_options', 'ila_suggestions_pool_size', array( $this, 'suggestions_pool_size_validation' ) );
        add_settings_field(
            'suggestions_titles',
            esc_attr__( 'Titles', 'ila' ),
            array( $this, 'suggestions_titles_callback' ),
            'ila_suggestions_options',
            'ila_suggestions_settings_section'
        );
        register_setting( 'ila_suggestions_options', 'ila_suggestions_titles', array( $this, 'suggestions_titles_validation' ) );
        add_settings_field(
            'suggestions_categories',
            esc_attr__( 'Categories', 'ila' ),
            array( $this, 'suggestions_categories_callback' ),
            'ila_suggestions_options',
            'ila_suggestions_settings_section'
        );
        register_setting( 'ila_suggestions_options', 'ila_suggestions_categories', array( $this, 'suggestions_categories_validation' ) );
        add_settings_field(
            'suggestions_tags',
            esc_attr__( 'Tags', 'ila' ),
            array( $this, 'suggestions_tags_callback' ),
            'ila_suggestions_options',
            'ila_suggestions_settings_section'
        );
        register_setting( 'ila_suggestions_options', 'ila_suggestions_tags', array( $this, 'suggestions_categories_validation' ) );
        add_settings_field(
            'suggestions_post_type',
            esc_attr__( 'Post Type', 'ila' ),
            array( $this, 'suggestions_post_type_callback' ),
            'ila_suggestions_options',
            'ila_suggestions_settings_section'
        );
        register_setting( 'ila_suggestions_options', 'ila_suggestions_post_type', array( $this, 'suggestions_categories_validation' ) );
        //section optimization -------------------------------------------------
        add_settings_section(
            'ila_optimization_settings_section',
            NULL,
            NULL,
            'ila_optimization_options'
        );
        add_settings_field(
            'optimization_num_of_characters',
            esc_attr__( 'Characters per Interlink', 'ila' ),
            array( $this, 'optimization_num_of_characters_callback' ),
            'ila_optimization_options',
            'ila_optimization_settings_section'
        );
        register_setting( 'ila_optimization_options', 'ila_optimization_num_of_characters', array( $this, 'optimization_num_of_characters_validation' ) );
        add_settings_field(
            'optimization_delta',
            esc_attr__( 'Optimization Delta', 'ila' ),
            array( $this, 'optimization_delta_callback' ),
            'ila_optimization_options',
            'ila_optimization_settings_section'
        );
        register_setting( 'ila_optimization_options', 'ila_optimization_delta', array( $this, 'optimization_delta_validation' ) );
        //section juice --------------------------------------------------------
        add_settings_section(
            'ila_juice_settings_section',
            NULL,
            NULL,
            'ila_juice_options'
        );
        add_settings_field(
            'default_seo_power',
            esc_attr__( 'SEO Power (Default)', 'ila' ),
            array( $this, 'default_seo_power_callback' ),
            'ila_juice_options',
            'ila_juice_settings_section'
        );
        register_setting( 'ila_juice_options', 'ila_default_seo_power', array( $this, 'default_seo_power_validation' ) );
        add_settings_field(
            'penality_per_position_percentage',
            esc_attr__( 'Penality per Position (%)', 'ila' ),
            array( $this, 'penality_per_position_percentage_callback' ),
            'ila_juice_options',
            'ila_juice_settings_section'
        );
        register_setting( 'ila_juice_options', 'ila_penality_per_position_percentage', array( $this, 'penality_per_position_percentage_validation' ) );
        add_settings_field(
            'remove_link_to_anchor',
            esc_attr__( 'Remove Link to Anchor', 'ila' ),
            array( $this, 'remove_link_to_anchor_callback' ),
            'ila_juice_options',
            'ila_juice_settings_section'
        );
        register_setting( 'ila_juice_options', 'ila_remove_link_to_anchor', array( $this, 'remove_link_to_anchor_validation' ) );
        add_settings_field(
            'remove_url_parameters',
            esc_attr__( 'Remove URL Parameters', 'ila' ),
            array( $this, 'remove_url_parameters_callback' ),
            'ila_juice_options',
            'ila_juice_settings_section'
        );
        register_setting( 'ila_juice_options', 'ila_remove_url_parameters', array( $this, 'remove_url_parameters_validation' ) );
        //section tracking -----------------------------------------------------
        add_settings_section(
            'ila_tracking_settings_section',
            NULL,
            NULL,
            'ila_tracking_options'
        );
        add_settings_field(
            'track_internal_links',
            esc_attr__( 'Track Internal Links', 'ila' ),
            array( $this, 'track_internal_links_callback' ),
            'ila_tracking_options',
            'ila_tracking_settings_section'
        );
        register_setting( 'ila_tracking_options', 'ila_track_internal_links', array( $this, 'track_internal_links_validation' ) );
        //section analysis --------------------------------------------------
        add_settings_section(
            'ila_analysis_settings_section',
            NULL,
            NULL,
            'ila_analysis_options'
        );
        add_settings_field(
            'set_max_execution_time',
            esc_attr__( 'Set Max Execution Time', 'ila' ),
            array( $this, 'set_max_execution_time_callback' ),
            'ila_analysis_options',
            'ila_analysis_settings_section'
        );
        register_setting( 'ila_analysis_options', 'ila_set_max_execution_time', array( $this, 'set_max_execution_time_validation' ) );
        add_settings_field(
            'max_execution_time_value',
            esc_attr__( 'Max Execution Time Value', 'ila' ),
            array( $this, 'max_execution_time_value_callback' ),
            'ila_analysis_options',
            'ila_analysis_settings_section'
        );
        register_setting( 'ila_analysis_options', 'ila_max_execution_time_value', array( $this, 'max_execution_time_value_validation' ) );
        add_settings_field(
            'set_memory_limit',
            esc_attr__( 'Set Memory Limit', 'ila' ),
            array( $this, 'set_memory_limit_callback' ),
            'ila_analysis_options',
            'ila_analysis_settings_section'
        );
        register_setting( 'ila_analysis_options', 'ila_set_memory_limit', array( $this, 'set_memory_limit_validation' ) );
        add_settings_field(
            'memory_limit_value',
            esc_attr__( 'Memory Limit Value', 'ila' ),
            array( $this, 'memory_limit_value_callback' ),
            'ila_analysis_options',
            'ila_analysis_settings_section'
        );
        register_setting( 'ila_analysis_options', 'ila_memory_limit_value', array( $this, 'memory_limit_value_validation' ) );
        add_settings_field(
            'limit_posts_analysis',
            esc_attr__( 'Limit Posts Analysis', 'ila' ),
            array( $this, 'limit_posts_analysis_callback' ),
            'ila_analysis_options',
            'ila_analysis_settings_section'
        );
        register_setting( 'ila_analysis_options', 'ila_limit_posts_analysis', array( $this, 'limit_posts_analysis_validation' ) );
        add_settings_field(
            'dashboard_post_types',
            esc_attr__( 'Dashboard Post Types', 'ila' ),
            array( $this, 'dashboard_post_types_callback' ),
            'ila_analysis_options',
            'ila_analysis_settings_section'
        );
        register_setting( 'ila_analysis_options', 'ila_dashboard_post_types', array( $this, 'dashboard_post_types_validation' ) );
        add_settings_field(
            'juice_post_types',
            esc_attr__( 'Juice Post Types', 'ila' ),
            array( $this, 'juice_post_types_callback' ),
            'ila_analysis_options',
            'ila_analysis_settings_section'
        );
        register_setting( 'ila_analysis_options', 'ila_juice_post_types', array( $this, 'juice_post_types_validation' ) );
        //meta boxes -----------------------------------------------------------
        add_settings_section(
            'ila_metaboxes_settings_section',
            NULL,
            NULL,
            'ila_metaboxes_options'
        );
        add_settings_field(
            'interlinks_options_post_types',
            esc_attr__( 'Interlinks Options Post Types', 'ila' ),
            array( $this, 'interlinks_options_post_types_callback' ),
            'ila_metaboxes_options',
            'ila_metaboxes_settings_section'
        );
        register_setting( 'ila_metaboxes_options', 'ila_interlinks_options_post_types', array( $this, 'interlinks_options_post_types_validation' ) );
        add_settings_field(
            'interlinks_optimization_post_types',
            esc_attr__( 'Interlinks Optimization Post Types', 'ila' ),
            array( $this, 'interlinks_optimization_post_types_callback' ),
            'ila_metaboxes_options',
            'ila_metaboxes_settings_section'
        );
        register_setting( 'ila_metaboxes_options', 'ila_interlinks_optimization_post_types', array( $this, 'interlinks_optimization_post_types_validation' ) );
        add_settings_field(
            'interlinks_suggestions_post_types',
            esc_attr__( 'Interlinks Suggestions Post Types', 'ila' ),
            array( $this, 'interlinks_suggestions_post_types_callback' ),
            'ila_metaboxes_options',
            'ila_metaboxes_settings_section'
        );
        register_setting( 'ila_metaboxes_options', 'ila_interlinks_suggestions_post_types', array( $this, 'interlinks_suggestions_post_types_validation' ) );
        //capabilities ----------------------------------------------------------
        add_settings_section(
            'ila_capabilities_settings_section',
            NULL,
            NULL,
            'ila_capabilities_options'
        );
        add_settings_field(
            'dashboard_menu_required_capability',
            esc_attr__( 'Dashboard Menu', 'ila' ),
            array( $this, 'dashboard_menu_required_capability_callback' ),
            'ila_capabilities_options',
            'ila_capabilities_settings_section'
        );
        register_setting( 'ila_capabilities_options', 'ila_dashboard_menu_required_capability', array( $this, 'dashboard_menu_required_capability_validation' ) );
        add_settings_field(
            'juice_menu_required_capability',
            esc_attr__( 'Juice Menu', 'ila' ),
            array( $this, 'juice_menu_required_capability_callback' ),
            'ila_capabilities_options',
            'ila_capabilities_settings_section'
        );
        register_setting( 'ila_capabilities_options', 'ila_juice_menu_required_capability', array( $this, 'juice_menu_required_capability_validation' ) );
        add_settings_field(
            'settings_menu_required_capability',
            esc_attr__( 'Juice Menu', 'ila' ),
            array( $this, 'settingsm_menu_required_capability_callback' ),
            'ila_capabilities_options',
            'ila_capabilities_settings_section'
        );
        register_setting( 'ila_capabilities_options', 'ila_settingsm_menu_required_capability', array( $this, 'settingsm_menu_required_capability_validation' ) );
        add_settings_field(
            'hits_menu_required_capability',
            esc_attr__( 'Hits Menu', 'ila' ),
            array( $this, 'hits_menu_required_capability_callback' ),
            'ila_capabilities_options',
            'ila_capabilities_settings_section'
        );
        register_setting( 'ila_capabilities_options', 'ila_hits_menu_required_capability', array( $this, 'hits_menu_required_capability_validation' ) );
        add_settings_field(
            'wizard_menu_required_capability',
            esc_attr__( 'Wizard Menu', 'ila' ),
            array( $this, 'wizard_menu_required_capability_callback' ),
            'ila_capabilities_options',
            'ila_capabilities_settings_section'
        );
        register_setting( 'ila_capabilities_options', 'ila_wizard_menu_required_capability', array( $this, 'wizard_menu_required_capability_validation' ) );
        add_settings_field(
            'ail_menu_required_capability',
            esc_attr__( 'AIL Menu', 'ila' ),
            array( $this, 'ail_menu_required_capability_callback' ),
            'ila_capabilities_options',
            'ila_capabilities_settings_section'
        );
        register_setting( 'ila_capabilities_options', 'ila_ail_menu_required_capability', array( $this, 'ail_menu_required_capability_validation' ) );
        add_settings_field(
            'categories_menu_required_capability',
            esc_attr__( 'Categories Menu', 'ila' ),
            array( $this, 'categories_menu_required_capability_callback' ),
            'ila_capabilities_options',
            'ila_capabilities_settings_section'
        );
        register_setting( 'ila_capabilities_options', 'ila_categories_menu_required_capability', array( $this, 'categories_menu_required_capability_validation' ) );
        add_settings_field(
            'maintenance_menu_required_capability',
            esc_attr__( 'Maintenance Menu', 'ila' ),
            array( $this, 'maintenance_menu_required_capability_callback' ),
            'ila_capabilities_options',
            'ila_capabilities_settings_section'
        );
        register_setting( 'ila_capabilities_options', 'ila_maintenance_menu_required_capability', array( $this, 'maintenance_menu_required_capability_validation' ) );
        add_settings_field(
            'interlinks_options_mb_required_capability',
            esc_attr__( 'Interlinks Options Meta Box', 'ila' ),
            array( $this, 'interlinks_options_mb_required_capability_callback' ),
            'ila_capabilities_options',
            'ila_capabilities_settings_section'
        );
        register_setting( 'ila_capabilities_options', 'ila_interlinks_options_mb_required_capability', array( $this, 'interlinks_options_mb_required_capability_validation' ) );
        add_settings_field(
            'interlinks_optimization_mb_required_capability',
            esc_attr__( 'Interlinks Optimization Meta Box', 'ila' ),
            array( $this, 'interlinks_optimization_mb_required_capability_callback' ),
            'ila_capabilities_options',
            'ila_capabilities_settings_section'
        );
        register_setting( 'ila_capabilities_options', 'ila_interlinks_optimization_mb_required_capability', array( $this, 'interlinks_optimization_mb_required_capability_validation' ) );
        add_settings_field(
            'interlinks_suggestions_mb_required_capability',
            esc_attr__( 'Interlinks Suggestions Meta Box', 'ila' ),
            array( $this, 'interlinks_suggestions_mb_required_capability_callback' ),
            'ila_capabilities_options',
            'ila_capabilities_settings_section'
        );
        register_setting( 'ila_capabilities_options', 'ila_interlinks_suggestions_mb_required_capability', array( $this, 'interlinks_suggestions_mb_required_capability_validation' ) );
        //advanced -----------------------------------------------------------------------------------------------------
        add_settings_section(
            'ila_advanced_settings_section',
            NULL,
            NULL,
            'ila_advanced_options'
        );
        add_settings_field(
            'default_enable_ail_on_post',
            esc_attr__( 'Enable AIL', 'ila' ),
            array( $this, 'default_enable_ail_on_post_callback' ),
            'ila_advanced_options',
            'ila_advanced_settings_section'
        );
        register_setting( 'ila_advanced_options', 'ila_default_enable_ail_on_post', array( $this, 'default_enable_ail_on_post_validation' ) );
        add_settings_field(
            'filter_priority',
            esc_attr__( 'Filter Priority', 'ila' ),
            array( $this, 'filter_priority_callback' ),
            'ila_advanced_options',
            'ila_advanced_settings_section'
        );
        register_setting( 'ila_advanced_options', 'ila_filter_priority', array( $this, 'filter_priority_validation' ) );
        add_settings_field(
            'ail_test_mode',
            esc_attr__( 'Test Mode', 'ila' ),
            array( $this, 'ail_test_mode_callback' ),
            'ila_advanced_options',
            'ila_advanced_settings_section'
        );
        register_setting( 'ila_advanced_options', 'ila_ail_test_mode', array( $this, 'ail_test_mode_validation' ) );
        add_settings_field(
            'random_prioritization',
            esc_attr__( 'Random Prioritization', 'ila' ),
            array( $this, 'random_prioritization_callback' ),
            'ila_advanced_options',
            'ila_advanced_settings_section'
        );
        register_setting( 'ila_advanced_options', 'ila_random_prioritization', array( $this, 'random_prioritization_validation' ) );
        add_settings_field(
            'ignore_self_ail',
            esc_attr__( 'Ignore Self AIL', 'ila' ),
            array( $this, 'ignore_self_ail_callback' ),
            'ila_advanced_options',
            'ila_advanced_settings_section'
        );
        register_setting( 'ila_advanced_options', 'ila_ignore_self_ail', array( $this, 'ignore_self_ail_validation' ) );
        add_settings_field(
            'general_limit_mode',
            esc_attr__( 'General Limit Mode', 'ila' ),
            array( $this, 'general_limit_mode_callback' ),
            'ila_advanced_options',
            'ila_advanced_settings_section'
        );
        register_setting( 'ila_advanced_options', 'ila_general_limit_mode', array( $this, 'general_limit_mode_validation' ) );
        add_settings_field(
            'characters_per_autolink',
            esc_attr__( 'General Limit (Characters per AIL)', 'ila' ),
            array( $this, 'characters_per_autolink_callback' ),
            'ila_advanced_options',
            'ila_advanced_settings_section'
        );
        register_setting( 'ila_advanced_options', 'ila_characters_per_autolink', array( $this, 'characters_per_autolink_validation' ) );
        add_settings_field(
            'max_number_autolinks_per_post',
            esc_attr__( 'General Limit (Amount)', 'ila' ),
            array( $this, 'max_number_autolinks_per_post_callback' ),
            'ila_advanced_options',
            'ila_advanced_settings_section'
        );
        register_setting( 'ila_advanced_options', 'ila_max_number_autolinks_per_post', array( $this, 'max_number_autolinks_per_post_validation' ) );
        add_settings_field(
            'general_limit_subtract_mil',
            esc_attr__( 'General Limit (Subtract MIL)', 'ila' ),
            array( $this, 'general_limit_subtract_mil_callback' ),
            'ila_advanced_options',
            'ila_advanced_settings_section'
        );
        register_setting( 'ila_advanced_options', 'ila_general_limit_subtract_mil', array( $this, 'general_limit_subtract_mil_validation' ) );
        add_settings_field(
            'same_url_limit',
            esc_attr__( 'Same URL Limit', 'ila' ),
            array( $this, 'same_url_limit_callback' ),
            'ila_advanced_options',
            'ila_advanced_settings_section'
        );
        register_setting( 'ila_advanced_options', 'ila_same_url_limit', array( $this, 'same_url_limit_validation' ) );
        add_settings_field(
            'wizard_rows',
            esc_attr__( 'Wizard Rows', 'ila' ),
            array( $this, 'wizard_rows_callback' ),
            'ila_advanced_options',
            'ila_advanced_settings_section'
        );
        register_setting( 'ila_advanced_options', 'ila_wizard_rows', array( $this, 'wizard_rows_validation' ) );
        add_settings_field(
            'protected_tags',
            esc_attr__( 'Protected Tags', 'ila' ),
            array( $this, 'protected_tags_callback' ),
            'ila_advanced_options',
            'ila_advanced_settings_section'
        );
        register_setting( 'ila_advanced_options', 'ila_protected_tags', array( $this, 'protected_tags_validation' ) );
        add_settings_field(
            'protected_gutenberg_blocks',
            esc_attr__( 'Protected Gutenberg Blocks', 'ila' ),
            array( $this, 'protected_gutenberg_blocks_callback' ),
            'ila_advanced_options',
            'ila_advanced_settings_section'
        );
        register_setting( 'ila_advanced_options', 'ila_protected_gutenberg_blocks', array( $this, 'protected_gutenberg_blocks_validation' ) );
        add_settings_field(
            'protected_gutenberg_custom_blocks',
            esc_attr__( 'Protected Gutenberg Custom Blocks', 'ila' ),
            array( $this, 'protected_gutenberg_custom_blocks_callback' ),
            'ila_advanced_options',
            'ila_advanced_settings_section'
        );
        register_setting( 'ila_advanced_options', 'ila_protected_gutenberg_custom_blocks', array( $this, 'protected_gutenberg_custom_blocks_validation' ) );
        add_settings_field(
            'protected_gutenberg_custom_void_blocks',
            esc_attr__( 'Protected Gutenberg Custom Void Blocks', 'ila' ),
            array( $this, 'protected_gutenberg_custom_void_blocks_callback' ),
            'ila_advanced_options',
            'ila_advanced_settings_section'
        );
        register_setting( 'ila_advanced_options', 'ila_protected_gutenberg_custom_void_blocks', array( $this, 'protected_gutenberg_custom_void_blocks_validation' ) );
        add_settings_field(
            'pagination_dashboard_menu',
            esc_attr__( 'Pagination Dashboard Menu', 'ila' ),
            array( $this, 'pagination_dashboard_menu_callback' ),
            'ila_advanced_options',
            'ila_advanced_settings_section'
        );
        register_setting( 'ila_advanced_options', 'ila_pagination_dashboard_menu', array( $this, 'pagination_dashboard_menu_validation' ) );
        add_settings_field(
            'pagination_juice_menu',
            esc_attr__( 'Pagination Juice Menu', 'ila' ),
            array( $this, 'pagination_juice_menu_callback' ),
            'ila_advanced_options',
            'ila_advanced_settings_section'
        );
        register_setting( 'ila_advanced_options', 'ila_pagination_juice_menu', array( $this, 'pagination_juice_menu_validation' ) );
        add_settings_field(
            'pagination_hits_menu',
            esc_attr__( 'Pagination Hits Menu', 'ila' ),
            array( $this, 'pagination_hits_menu_callback' ),
            'ila_advanced_options',
            'ila_advanced_settings_section'
        );
        register_setting( 'ila_advanced_options', 'ila_pagination_hits_menu', array( $this, 'pagination_hits_menu_validation' ) );
        add_settings_field(
            'pagination_ail_menu',
            esc_attr__( 'Pagination AIL Menu', 'ila' ),
            array( $this, 'pagination_ail_menu_callback' ),
            'ila_advanced_options',
            'ila_advanced_settings_section'
        );
        register_setting( 'ila_advanced_options', 'ila_pagination_ail_menu', array( $this, 'pagination_ail_menu_validation' ) );
        add_settings_field(
            'pagination_categories_menu',
            esc_attr__( 'Pagination Categories Menu', 'ila' ),
            array( $this, 'pagination_categories_menu_callback' ),
            'ila_advanced_options',
            'ila_advanced_settings_section'
        );
        register_setting( 'ila_advanced_options', 'ila_pagination_categories_menu', array( $this, 'pagination_categories_menu_validation' ) );
    }

    //ail options callbacks and validations ------------------------------------
    public function default_category_id_callback( $args )
    {
        $html = '<select id="ila_default_category_id" name="ila_default_category_id" class="daext-display-none">';
        $html .= '<option value="0" ' . esc_attr(selected( intval( get_option( "ila_defaults_category_id" ) ), 0, false )) . '>' . esc_attr__( 'None', 'ila' ) . '</option>';
        global  $wpdb ;
        $table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_category";
        $sql = "SELECT category_id, name FROM {$table_name} ORDER BY category_id DESC";
        $category_a = $wpdb->get_results( $sql, ARRAY_A );
        foreach ( $category_a as $key => $category ) {
            $html .= '<option value="' . $category['category_id'] . '" ' .esc_attr(selected( intval( get_option( "ila_default_category_id" ) )), $category['category_id'], false ) . '>' . esc_attr( stripslashes( $category['name'] ) ) . '</option>';
        }
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'The category of the AIL. This option determines the default value of the "Category" field available in the "AIL" menu and in the "Wizard" menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function default_category_id_validation( $input )
    {
        return intval( $input, 10 );
    }

    public function default_title_callback( $args )
    {
        $html = '<input type="text" id="ila_default_title" name="ila_default_title" class="regular-text" value="' . esc_attr( get_option( "ila_default_title" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'The title attribute of the link automatically generated on the keyword. This option determines the default value of the "Title" field available in the AIL menu and is also used for the AIL generated with the "Wizard" menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function default_title_validation( $input )
    {

        if ( mb_strlen( $input ) > 1024 ) {
            add_settings_error( 'ila_default_title', 'ila_default_title', esc_attr( 'Please enter a valid capability in the "Wizard Menu" option.') );
            $output = get_option( 'ila_default_title' );
        } else {
            $output = $input;
        }

        return trim( $output );
    }

    public function default_open_new_tab_callback( $args )
    {
        $html = '<select id="ila_default_open_new_tab" name="ila_default_open_new_tab" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_default_open_new_tab" ) )), 0, false ) . ' value="0">' . esc_attr( 'No' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_default_open_new_tab" ) )), 1, false ) . ' value="1">' . esc_attr( 'Yes') . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'If you select "Yes" the link generated on the defined keyword opens the linked document in a new tab. This option determines the default value of the "Open New Tab" field available in the AIL menu and is also used for the AIL generated with the "Wizard" menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function default_open_new_tab_validation( $input )
    {
        return ( intval( $input, 10 ) == 1 ? '1' : '0' );
    }

    public function default_use_nofollow_callback( $args )
    {
        $html = '<select id="ila_default_use_nofollow" name="ila_default_use_nofollow" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_default_use_nofollow" ) ), 0, false )) . ' value="0">' . esc_attr__( 'No', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_default_use_nofollow" ) ), 1, false )) . ' value="1">' . esc_attr__( 'Yes', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'If you select "Yes" the link generated on the defined keyword will include the rel="nofollow" attribute. This option determines the default value of the "Use Nofollow" field available in the AIL menu and is also used for the AIL generated with the "Wizard" menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function default_use_nofollow_validation( $input )
    {
        return ( intval( $input, 10 ) == 1 ? '1' : '0' );
    }

    public function default_activate_post_types_callback( $args )
    {
        $html = '<input type="text" id="ila_default_activate_post_types" name="ila_default_activate_post_types" class="regular-text" value="' . esc_attr( get_option( "ila_default_activate_post_types" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'Enter a list of post types separated by a comma. This option determines the default value of the "Post Types" field available in the AIL menu and is also used for the AIL generated with the "Wizard" menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function default_activate_post_types_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_list_of_post_types, $input ) ) {
            add_settings_error( 'ila_default_activate_post_types', 'ila_default_activate_post_types', esc_attr( 'Please enter a valid list of post types separated by a comma in the "Post Types" option.') );
            $output = get_option( 'ila_default_activate_post_types' );
        } else {
            $output = $input;
        }

        return $output;
    }

    public function default_case_insensitive_search_callback( $args )
    {
        $html = '<select id="ila_default_case_insensitive_search" name="ila_default_case_insensitive_search" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_default_case_insensitive_search" ) )), 0, false ) . ' value="0">' . esc_attr( 'No') . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_default_case_insensitive_search" ) )), 1, false ) . ' value="1">' . esc_attr( 'Yes') . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'If you select "Yes" your keyword will match both lowercase and uppercase variations. This option determines the default value of the "Case Insensitive Search" field available in the AIL menu and is also used for the AIL generated with the "Wizard" menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function default_case_insensitive_search_validation( $input )
    {
        return ( intval( $input, 10 ) == 1 ? '1' : '0' );
    }

    public function default_string_before_callback( $args )
    {
        $html = '<select id="ila_default_string_before" name="ila_default_string_before" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_default_string_before" ) ), 1, false )) . ' value="1">' . esc_attr__( 'Generic', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_default_string_before" ) ), 2, false )) . ' value="2">' . esc_attr__( 'White Space', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_default_string_before" ) ), 3, false )) . ' value="3">' . esc_attr__( 'Comma', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_default_string_before" ) ), 4, false )) . ' value="4">' . esc_attr__( 'Point', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_default_string_before" ) ), 5, false )) . ' value="5">' . esc_attr__( 'None', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr__( 'Use this option to match keywords preceded by a generic boundary or by a specific character. This option determines the default value of the "Left Boundary" field available in the "AIL" menu and is also used for the AIL generated with the "Wizard" menu.', 'ila' ) . '"></div>';
        echo  esc_html($html) ;
    }

    public function default_string_before_validation( $input )
    {

        if ( intval( $input, 10 ) >= 1 and intval( $input, 10 ) <= 5 ) {
            return intval( $input, 10 );
        } else {
            return intval( get_option( 'ila_default_string_before' ), 10 );
        }

    }

    public function default_string_after_callback( $args )
    {
        $html = '<select id="ila_default_string_after" name="ila_default_string_after" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_default_string_after" ) ), 1, false )) . ' value="1">' . esc_attr__( 'Generic', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_default_string_after" ) ), 2, false )) . ' value="2">' . esc_attr__( 'White Space', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_default_string_after" ) ), 3, false )). ' value="3">' . esc_attr__( 'Comma', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_default_string_after" ) ), 4, false )) . ' value="4">' . esc_attr__( 'Point', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_default_string_after" ) ), 5, false )) . ' value="5">' . esc_attr__( 'None', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr__( 'Use this option to match keywords followed by a generic boundary or by a specific character. This option determines the default value of the "Right Boundary" field available in the "AIL" menu and is also used for the AIL generated with the "Wizard" menu.', 'ila' ) . '"></div>';
        echo  esc_html($html) ;
    }

    public function default_string_after_validation( $input )
    {

        if ( intval( $input, 10 ) >= 1 and intval( $input, 10 ) <= 5 ) {
            return intval( $input, 10 );
        } else {
            return intval( get_option( 'ila_default_string_after' ), 10 );
        }

    }

    public function default_max_number_autolinks_per_keyword_callback( $args )
    {
        $html = '<input type="text" id="ila_default_max_number_autolinks_per_keyword" name="ila_default_max_number_autolinks_per_keyword" class="regular-text" value="' . esc_attr(intval( get_option( "ila_default_max_number_autolinks_per_keyword" ), 10 ) ). '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'With this option you can determine the maximum number of matches of the defined keyword automatically converted to a link. This option determines the default value of the "Limit" field available in the AIL menu and is also used for the AIL generated with the "Wizard" menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function default_max_number_autolinks_per_keyword_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_number_ten_digits, $input ) or intval( $input, 10 ) < 1 or intval( $input, 10 ) > 1000000 ) {
            add_settings_error( 'ila_default_max_number_autolinks_per_keyword', 'ila_default_max_number_autolinks_per_keyword', esc_attr__( 'Please enter a number from 1 to 1000000 in the "Limit" option.', 'ila' ) );
            $output = get_option( 'ila_default_max_number_autolinks_per_keyword' );
        } else {
            $output = $input;
        }

        return intval( $output, 10 );
    }

    public function default_priority_callback( $args )
    {
        $html = '<input type="text" id="ila_default_priority" name="ila_default_priority" class="regular-text" value="' . esc_attr(intval( get_option( "ila_default_priority" ), 10 )) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'The priority value determines the order used to apply the AIL on the post. This option determines the default value of the "Priority" field available in the AIL menu and is also used for the AIL generated with the "Wizard" menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function default_priority_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_number_ten_digits, $input ) or intval( $input, 10 ) > 1000000 ) {
            add_settings_error( 'ila_default_priority', 'ila_default_priority', esc_attr__( 'Please enter a number from 0 to 1000000 in the "Priority" option.', 'ila' ) );
            $output = get_option( 'ila_default_priority' );
        } else {
            $output = $input;
        }

        return intval( $output, 10 );
    }

    //suggestions options callbacks and validations ----------------------------
    public function suggestions_pool_post_types_callback( $args )
    {
        $html = '<input type="text" id="ila_suggestions_pool_post_types" name="ila_suggestions_pool_post_types" class="regular-text" value="' . esc_attr( get_option( "ila_suggestions_pool_post_types" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'A list of post types, separated by comma, where the algorithm available in the "Interlinks Suggestions" meta box should look for suggestions.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function suggestions_pool_post_types_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_list_of_post_types, $input ) ) {
            add_settings_error( 'ila_suggestions_pool_post_types', 'ila_suggestions_pool_post_types', esc_attr__( 'Please enter a v\\alid list of post types separated by a comma in the "Pool Post Types" option.', 'ila' ) );
            $output = get_option( 'ila_suggestions_pool_post_types' );
        } else {
            $output = $input;
        }

        return $output;
    }

    public function suggestions_pool_size_callback( $args )
    {
        $html = '<input type="text" id="ila_suggestions_pool_size" name="ila_suggestions_pool_size" class="regular-text" value="' . esc_attr( get_option( "ila_suggestions_pool_size" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'This option determines the maximum number of results returned by the algorithm available in the "Interlinks Suggestions" meta box. (The five results shown for each iteration are retrieved from a pool of results which has, as a maximum size, the value defined with this option.)') . '"></div>';
        echo  esc_html($html) ;
    }

    public function suggestions_pool_size_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_number_ten_digits, $input ) or intval( $input, 10 ) < 5 or intval( $input ) > 1000000 ) {
            add_settings_error( 'ila_suggestions_pool_size', 'ila_suggestions_pool_size', esc_attr__( 'Please enter a number from 5 to 1000000 in the "Pool Size" option.', 'ila' ) );
            $output = get_option( 'ila_suggestions_pool_size' );
        } else {
            $output = $input;
        }

        return intval( $output, 10 );
    }

    public function suggestions_titles_callback( $args )
    {
        $html = '<select id="ila_suggestions_titles" name="ila_suggestions_titles" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( get_option( "ila_suggestions_titles" ), 'consider', false )) . ' value="consider">' . esc_attr__( 'Consider', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( get_option( "ila_suggestions_titles" ), 'ignore', false ) ). ' value="ignore">' . esc_attr__( 'Ignore', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'Select if the algorithm available in the "Interlinks Suggestions" meta box should consider the posts, pages and custom post types titles.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function suggestions_titles_validation( $input )
    {
        return $input;
    }

    public function suggestions_categories_callback( $args )
    {
        $html = '<select id="ila_suggestions_categories" name="ila_suggestions_categories" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( get_option( "ila_suggestions_categories" ), 'require', false ) ). ' value="require">' . esc_attr__( 'Require', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( get_option( "ila_suggestions_categories" ), 'consider', false ) ). ' value="consider">' . esc_attr__( 'Consider', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( get_option( "ila_suggestions_categories" ), 'ignore', false ) ). ' value="ignore">' . esc_attr__( 'Ignore', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'Select if the algorithm available in the "Interlinks Suggestions" meta box should consider the post categories. If "Required" is selected the algorithm will return only posts that have at least one category in common with the edited post.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function suggestions_categories_validation( $input )
    {
        return $input;
    }

    public function suggestions_tags_callback( $args )
    {

        $html = '<select id="ila_suggestions_tags" name="ila_suggestions_tags" class="daext-display-none">';
        $html .= '<option ' .esc_attr(selected( get_option( "ila_suggestions_tags" ), 'require', false ) ). ' value="require">' . esc_attr__( 'Require', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( get_option( "ila_suggestions_tags" ), 'consider', false )) . ' value="consider">' . esc_attr__( 'Consider', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( get_option( "ila_suggestions_tags" ), 'ignore', false )) . ' value="ignore">' . esc_attr__( 'Ignore', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'Select if the algorithm available in the "Interlinks Suggestions" meta box should consider the post tags. If "Required" is selected the algorithm will return only posts that have at least one tag in common with the edited post.') . '"></div>';
        echo  esc_html($html) ;

    }

    public function suggestions_tags_validation( $input )
    {
        return $input;
    }

    public function suggestions_post_type_callback( $args )
    {
        $html = '<select id="ila_suggestions_post_type" name="ila_suggestions_post_type" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( get_option( "ila_suggestions_post_type" ), 'require', false )) . ' value="require">' . esc_attr__( 'Require', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( get_option( "ila_suggestions_post_type" ), 'consider', false )) . ' value="consider">' . esc_attr__( 'Consider', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( get_option( "ila_suggestions_post_type" ), 'ignore', false ) ). ' value="ignore">' . esc_attr__( 'Ignore', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'Select if the algorithm available in the "Interlinks Suggestions" meta box should consider the post type. If "Required" is selected the algorithm will return only posts that belong to the same post type of the edited post.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function suggestions_post_type_validation( $input )
    {
        return $input;
    }

    //optimization options callbacks and validations ---------------------------
    public function optimization_num_of_characters_callback( $args )
    {
        $html = '<input type="text" id="ila_optimization_num_of_characters" name="ila_optimization_num_of_characters" class="regular-text" value="' . esc_attr(intval( get_option( "ila_optimization_num_of_characters" ), 10 )) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'The "Recommended Interlinks" value available in the "Dashboard" menu and in the "Interlinks Optimization" meta box is based on the defined "Characters per Interlink" and on the content length of the post. For example if you define 500 "Characters per Interlink", in the "Dashboard" menu, with a post that has a content length of 2000 characters you will get 4 as the value for the "Recommended Interlinks".') . '"></div>';
        echo  esc_html($html) ;
    }

    public function optimization_num_of_characters_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_number_ten_digits, $input ) or intval( $input, 10 ) < 1 or intval( $input, 10 ) > 1000000 ) {
            add_settings_error( 'ila_optimization_num_of_characters', 'ila_optimization_num_of_characters', esc_attr__( 'Please enter a number from 1 to 1000000 in the "Characters per Interlink" option.', 'ila' ) );
            $output = get_option( 'ila_optimization_num_of_characters' );
        } else {
            $output = $input;
        }

        return intval( $output, 10 );
    }

    public function optimization_delta_callback( $args )
    {
        $html = '<input type="text" id="ila_optimization_delta" name="ila_optimization_delta" class="regular-text" value="' .esc_attr(intval( get_option( "ila_optimization_delta" ), 10 )) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'The "Optimization Delta" is used to generate the "Optimization Flag" available in the "Dashboard" menu and the text message diplayed in the "Interlinks Optimization" meta box. This option determines how different can be the actual number of interlinks in a post from the calculated "Recommended Interlinks". This option defines a range, so for example in a post with 10 "Recommended Interlinks" and this option value equal to 4, the post will be considered optimized when it includes from 8 to 12 interlinks.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function optimization_delta_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_number_ten_digits, $input ) or intval( $input, 10 ) > 1000000 ) {
            add_settings_error( 'ila_optimization_delta', 'ila_optimization_delta', esc_attr__( 'Please enter a number from 0 to 1000000 in the "Optimization Delta" option.', 'ila' ) );
            $output = get_option( 'ila_optimization_delta' );
        } else {
            $output = $input;
        }

        return intval( $output, 10 );
    }

    //juice options callbacks and validations ----------------------------------
    public function default_seo_power_callback( $args )
    {
        $html = '<input type="text" id="ila_default_seo_power" name="ila_default_seo_power" class="regular-text" value="' . esc_attr(intval( get_option( "ila_default_seo_power" ), 10 )) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'The "SEO Power" is the base value used to calculate the flow of "Link Juice" and this option determines the default "SEO Power" value of a post. You can override this value for specific posts in the "Interlinks Options" meta box.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function default_seo_power_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_number_ten_digits, $input ) or intval( $input, 10 ) < 100 or intval( $input, 10 ) > 1000000 ) {
            add_settings_error( 'ila_default_seo_power', 'ila_default_seo_power', esc_attr__( 'Please enter a number from 100 to 1000000 in the "SEO Power (Default)" option.', 'ila' ) );
            $output = get_option( 'ila_default_seo_power' );
        } else {
            $output = $input;
        }

        return intval( $output, 10 );
    }

    public function penality_per_position_percentage_callback( $args )
    {
        $html = '<input type="text" id="ila_penality_per_position_percentage" name="ila_penality_per_position_percentage" class="regular-text" value="' . esc_attr(intval( get_option( "ila_penality_per_position_percentage" ), 10 )) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'With multiple links in an article, the algorithm that calculates the "Link Juice" passed by each link removes a percentage of the passed "Link Juice" based on the position of a link compared to the other links.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function penality_per_position_percentage_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_number_ten_digits, $input ) or intval( $input, 10 ) > 100 ) {
            add_settings_error( 'ila_penality_per_position_percentage', 'ila_penality_per_position_percentage', esc_attr__( 'Please enter a number from 0 to 100 in the "Penality per position" option.', 'ila' ) );
            $output = get_option( 'ila_penality_per_position_percentage' );
        } else {
            $output = $input;
        }

        return intval( $output, 10 );
    }

    public function remove_link_to_anchor_callback( $args )
    {
        $html = '<select id="ila_remove_link_to_anchor" name="ila_remove_link_to_anchor" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_remove_link_to_anchor" ) ), 0, false )) . ' value="0">' . esc_attr__( 'No', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_remove_link_to_anchor" ) ), 1, false )) . ' value="1">' . esc_attr__( 'Yes', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'Select "Yes" to automatically remove links to anchors from every URL used to calculate the link juice. With this option enabled "http://example.com" and "http://example.com#myanchor" will both contribute to generate link juice only for a single URL, that is "http://example.com".') . '"></div>';
        echo  esc_html($html) ;
    }

    public function remove_link_to_anchor_validation( $input )
    {
        return ( intval( $input, 10 ) == 1 ? '1' : '0' );
    }

    public function remove_url_parameters_callback( $args )
    {
        $html = '<select id="ila_remove_url_parameters" name="ila_remove_url_parameters" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_remove_url_parameters" ) ), 0, false ) ). ' value="0">' . esc_attr__( 'No', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_remove_url_parameters" ) ), 1, false ) ). ' value="1">' . esc_attr__( 'Yes', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'Select "Yes" to automatically remove the URL parameters from every URL used to calculate the link juice. With this option enabled "http://example.com" and "http://example.com?param=1" will both contribute to generate link juice only for a single URL, that is "http://example.com". Please note that this option should not be enabled if your website is using URL parameters to actually identify specific pages. (for example with pretty permalinks not enabled)') . '"></div>';
        echo  esc_html($html) ;
    }

    public function remove_url_parameters_validation( $input )
    {
        return ( intval( $input, 10 ) == 1 ? '1' : '0' );
    }

    //tracking options callbacks and validations -------------------------------
    public function track_internal_links_callback( $args )
    {
        $html = '<select id="ila_track_internal_links" name="ila_track_internal_links" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_track_internal_links" ) ), 0, false )) . ' value="0">' . esc_attr__( 'No', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_track_internal_links" ) ), 1, false )) . ' value="1">' . esc_attr__( 'Yes', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'With this option enabled every click on the manual and auto internal links will be tracked. The collected data will be available in the "Hits" menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function track_internal_links_validation( $input )
    {
        return ( intval( $input, 10 ) == 1 ? '1' : '0' );
    }

    //analysis options callbacks and validations ----------------------------
    public function set_max_execution_time_callback( $args )
    {
        $html = '<select id="ila_set_max_execution_time" name="ila_set_max_execution_time" class="daext-display-none">';
        $html .= '<option ' .esc_attr(selected( intval( get_option( "ila_set_max_execution_time" ) ), 0, false ) ). ' value="0">' . esc_attr__( 'No', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_set_max_execution_time" ) ), 1, false )) . ' value="1">' . esc_attr__( 'Yes', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'Select "Yes" to enable your custom "Max Execution Time Value" on the scripts used to analyze your posts.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function set_max_execution_time_validation( $input )
    {
        return ( intval( $input, 10 ) == 1 ? '1' : '0' );
    }

    public function max_execution_time_value_callback( $args )
    {
        $html = '<input type="text" id="ila_max_execution_time_value" name="ila_max_execution_time_value" class="regular-text" value="' . esc_attr(intval( get_option( "ila_max_execution_time_value" ), 10 )) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'This value determines the maximum number of seconds allowed to execute the scripts used to analyze your posts.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function max_execution_time_value_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_number_ten_digits, $input ) or intval( $input, 10 ) < 1 or intval( $input, 10 ) > 1000000 ) {
            add_settings_error( 'ila_max_execution_time_value', 'ila_max_execution_time_value', esc_attr__( 'Please enter a number from 1 to 1000000 in the "Max Execution Time Value" option.', 'ila' ) );
            $output = get_option( 'ila_max_execution_time_value' );
        } else {
            $output = $input;
        }

        return intval( $output, 10 );
    }

    public function set_memory_limit_callback( $args )
    {
        $html = '<select id="ila_set_memory_limit" name="ila_set_memory_limit" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_set_memory_limit" ) ), 0, false )) . ' value="0">' . esc_attr__( 'No', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_set_memory_limit" ) ), 1, false )) . ' value="1">' . esc_attr__( 'Yes', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'Select "Yes" to enable your custom "Memory Limit Value" on the scripts used to analyze your posts.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function set_memory_limit_validation( $input )
    {
        return ( intval( $input, 10 ) == 1 ? '1' : '0' );
    }

    public function memory_limit_value_callback( $args )
    {
        $html = '<input type="text" id="ila_memory_limit_value" name="ila_memory_limit_value" class="regular-text" value="' . esc_attr (intval( get_option( "ila_memory_limit_value" ), 10 ) ). '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'This value determines the PHP memory limit in megabytes allowed to execute the scripts used to analyze your posts.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function memory_limit_value_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_number_ten_digits, $input ) or intval( $input, 10 ) < 1 or intval( $input, 10 ) > 1000000 ) {
            add_settings_error( 'ila_memory_limit_value', 'ila_memory_limit_value', esc_attr__( 'Please enter a number from 1 to 1000000 in the "Memory Limit Value" option.', 'ila' ) );
            $output = get_option( 'ila_memory_limit_value' );
        } else {
            $output = $input;
        }

        return intval( $output, 10 );
    }

    public function limit_posts_analysis_callback( $args )
    {
        $html = '<input type="text" id="ila_limit_posts_analysis" name="ila_limit_posts_analysis" class="regular-text" value="' .esc_attr(intval( get_option( "ila_limit_posts_analysis" ), 10 ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'With this options you can determine the maximum number of posts analyzed to get information about your internal links, to get information about the internal links juice and to get suggestions in the "Interlinks Suggestions" meta box. If you select for example "1000", the analysis performed by the plugin will use your latest "1000" posts.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function limit_posts_analysis_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_number_ten_digits, $input ) or intval( $input, 10 ) < 1 or intval( $input, 10 ) > 100000 ) {
            add_settings_error( 'ila_limit_posts_analysis', 'ila_limit_posts_analysis', esc_attr__( 'Please enter a number from 1 to 100000 in the "Limit Posts Analysis" option.', 'ila' ) );
            $output = get_option( 'ila_limit_posts_analysis' );
        } else {
            $output = $input;
        }

        return intval( $output, 10 );
    }

    public function dashboard_post_types_callback( $args )
    {
        $html = '<input type="text" id="ila_dashboard_post_types" name="ila_dashboard_post_types" class="regular-text" value="' . esc_attr( get_option( "ila_dashboard_post_types" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'A list of post types, separated by comma, used to determine the post types analyzed in the Dashboard menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function dashboard_post_types_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_list_of_post_types, $input ) ) {
            add_settings_error( 'ila_dashboard_post_types', 'ila_dashboard_post_types', esc_attr__( 'Please enter a valid list of post types separated by a comma in the "Dashboard Post Types" option.', 'ila' ) );
            $output = get_option( 'ila_dashboard_post_types' );
        } else {
            $output = $input;
        }

        return $output;
    }

    public function juice_post_types_callback( $args )
    {
        $html = '<input type="text" id="ila_juice_post_types" name="ila_juice_post_types" class="regular-text" value="' . esc_attr( get_option( "ila_juice_post_types" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'A list of post types, separated by comma, used to determine the post types analyzed in the Juice menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function juice_post_types_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_list_of_post_types, $input ) ) {
            add_settings_error( 'ila_juice_post_types', 'ila_juice_post_types', esc_attr__( 'Please enter a valid list of post types separated by a comma in the "Juice Post Types" option.', 'ila' ) );
            $output = get_option( 'ila_juice_post_types' );
        } else {
            $output = $input;
        }

        return $output;
    }

    //metaboxes options callbacks and validation -------------------------------
    public function interlinks_options_post_types_callback( $args )
    {
        $html = '<input type="text" id="ila_interlinks_options_post_types" name="ila_interlinks_options_post_types" class="regular-text" value="' . esc_attr( get_option( "ila_interlinks_options_post_types" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'A list of post types, separated by comma, where the "Interlinks Options" meta box should be loaded.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function interlinks_options_post_types_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_list_of_post_types, $input ) ) {
            add_settings_error( 'ila_interlinks_options_post_types', 'ila_interlinks_options_post_types', esc_attr__( 'Please enter a valid list of post types separated by a comma in the "Interlinks Options Post Types" option.', 'ila' ) );
            $output = get_option( 'ila_interlinks_options_post_types' );
        } else {
            $output = $input;
        }

        return $output;
    }

    public function interlinks_optimization_post_types_callback( $args )
    {
        $html = '<input type="text" id="ila_interlinks_optimization_post_types" name="ila_interlinks_optimization_post_types" class="regular-text" value="' . esc_attr( get_option( "ila_interlinks_optimization_post_types" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'A list of post types, separated by comma, where the "Interlinks Optimization" meta box should be loaded.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function interlinks_optimization_post_types_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_list_of_post_types, $input ) ) {
            add_settings_error( 'ila_interlinks_optimization_post_types', 'ila_interlinks_optimization_post_types', esc_attr__( 'Please enter a valid list of post types separated by a comma in the "Interlinks Optimization Post Types" option.', 'ila' ) );
            $output = get_option( 'ila_interlinks_optimization_post_types' );
        } else {
            $output = $input;
        }

        return $output;
    }

    public function interlinks_suggestions_post_types_callback( $args )
    {
        $html = '<input type="text" id="ila_interlinks_suggestions_post_types" name="ila_interlinks_suggestions_post_types" class="regular-text" value="' . esc_attr( get_option( "ila_interlinks_suggestions_post_types" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'A list of post types, separated by comma, where the "Interlinks Suggestions" meta box should be loaded.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function interlinks_suggestions_post_types_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_list_of_post_types, $input ) ) {
            add_settings_error( 'ila_interlinks_suggestions_post_types', 'ila_interlinks_suggestions_post_types', esc_attr__( 'Please enter a valid list of post types separated by a comma in the "Interlinks Suggestions Post Types" option.', 'ila' ) );
            $output = get_option( 'ila_interlinks_suggestions_post_types' );
        } else {
            $output = $input;
        }

        return $output;
    }

    public function dashboard_menu_required_capability_callback( $args )
    {
        $html = '<input type="text" id="ila_dashboard_menu_required_capability" name="ila_dashboard_menu_required_capability" class="regular-text" value="' . esc_attr( get_option( "ila_dashboard_menu_required_capability" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'The capability required to get access on the "Dashboard" Menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function dashboard_menu_required_capability_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_capability, $input ) ) {
            add_settings_error( 'ila_dashboard_menu_required_capability', 'ila_dashboard_menu_required_capability', esc_attr__( 'Please enter a valid capability in the "Dashboard Menu" option.', 'ila' ) );
            $output = get_option( 'ila_dashboard_menu_required_capability' );
        } else {
            $output = $input;
        }

        return trim( $output );
    }

    public function juice_menu_required_capability_callback( $args )
    {
        $html = '<input type="text" id="ila_juice_menu_required_capability" name="ila_juice_menu_required_capability" class="regular-text" value="' . esc_attr( get_option( "ila_juice_menu_required_capability" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'The capability required to get access on the "Juice" Menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function juice_menu_required_capability_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_capability, $input ) ) {
            add_settings_error( 'ila_juice_menu_required_capability', 'ila_juice_menu_required_capability', esc_attr__( 'Please enter a valid capability in the "Juice Menu" option.', 'ila' ) );
            $output = get_option( 'ila_juice_menu_required_capability' );
        } else {
            $output = $input;
        }

        return trim( $output );
    }

    public function settingsm_menu_required_capability_callback( $args )
    {
        $html = '<input type="text" id="ila_settingsm_menu_required_capability" name="ila_settingsm_menu_required_capability" class="regular-text" value="' . esc_attr( get_option( "ila_settingsm_menu_required_capability" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'The capability required to get access on the "Juice" Menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function settingsm_menu_required_capability_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_capability, $input ) ) {
            add_settings_error( 'ila_settingsm_menu_required_capability', 'ila_settingsm_menu_required_capability', esc_attr__( 'Please enter a valid capability in the "Juice Menu" option.', 'ila' ) );
            $output = get_option( 'ila_settingsm_menu_required_capability' );
        } else {
            $output = $input;
        }

        return trim( $output );
    }

    public function hits_menu_required_capability_callback( $args )
    {
        $html = '<input type="text" id="ila_hits_menu_required_capability" name="ila_hits_menu_required_capability" class="regular-text" value="' . esc_attr( get_option( "ila_hits_menu_required_capability" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'The capability required to get access on the "Hits" Menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function hits_menu_required_capability_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_capability, $input ) ) {
            add_settings_error( 'ila_hits_menu_required_capability', 'ila_hits_menu_required_capability', esc_attr__( 'Please enter a valid capability in the "Hits Menu" option.', 'ila' ) );
            $output = get_option( 'ila_hits_menu_required_capability' );
        } else {
            $output = $input;
        }

        return trim( $output );
    }

    public function wizard_menu_required_capability_callback( $args )
    {
        $html = '<input type="text" id="ila_wizard_menu_required_capability" name="ila_wizard_menu_required_capability" class="regular-text" value="' . esc_attr( get_option( "ila_wizard_menu_required_capability" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'The capability required to get access on the "Wizard" Menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function wizard_menu_required_capability_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_capability, $input ) ) {
            add_settings_error( 'ila_wizard_menu_required_capability', 'ila_wizard_menu_required_capability', esc_attr__( 'Please enter a valid capability in the "Wizard Menu" option.', 'ila' ) );
            $output = get_option( 'ila_wizard_menu_required_capability' );
        } else {
            $output = $input;
        }

        return trim( $output );
    }

    public function ail_menu_required_capability_callback( $args )
    {
        $html = '<input type="text" id="ila_ail_menu_required_capability" name="ila_ail_menu_required_capability" class="regular-text" value="' . esc_attr( get_option( "ila_ail_menu_required_capability" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'The capability required to get access on the "AIL" Menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function ail_menu_required_capability_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_capability, $input ) ) {
            add_settings_error( 'ila_ail_menu_required_capability', 'ila_ail_menu_required_capability', esc_attr__( 'Please enter a valid capability in the "AIL Menu" option.', 'ila' ) );
            $output = get_option( 'ila_ail_menu_required_capability' );
        } else {
            $output = $input;
        }

        return trim( $output );
    }

    public function categories_menu_required_capability_callback( $args )
    {
        $html = '<input type="text" id="ila_categories_menu_required_capability" name="ila_categories_menu_required_capability" class="regular-text" value="' . esc_attr( get_option( "ila_categories_menu_required_capability" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'The capability required to get access on the "Categories" Menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function categories_menu_required_capability_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_capability, $input ) ) {
            add_settings_error( 'ila_categories_menu_required_capability', 'ila_categories_menu_required_capability', esc_attr__( 'Please enter a valid capability in the "Categories Menu" option.', 'ila' ) );
            $output = get_option( 'ila_categories_menu_required_capability' );
        } else {
            $output = $input;
        }

        return trim( $output );
    }

    public function maintenance_menu_required_capability_callback( $args )
    {
        $html = '<input type="text" id="ila_maintenance_menu_required_capability" name="ila_maintenance_menu_required_capability" class="regular-text" value="' . esc_attr( get_option( "ila_maintenance_menu_required_capability" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'The capability required to get access on the "Maintenance" Menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function maintenance_menu_required_capability_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_capability, $input ) ) {
            add_settings_error( 'ila_maintenance_menu_required_capability', 'ila_maintenance_menu_required_capability', esc_attr__( 'Please enter a valid capability in the "Maintenance Menu" option.', 'ila' ) );
            $output = get_option( 'ila_maintenance_menu_required_capability' );
        } else {
            $output = $input;
        }

        return trim( $output );
    }

    public function interlinks_options_mb_required_capability_callback( $args )
    {
        $html = '<input type="text" id="ila_interlinks_options_mb_required_capability" name="ila_interlinks_options_mb_required_capability" class="regular-text" value="' . esc_attr( get_option( "ila_interlinks_options_mb_required_capability" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'The capability required to get access on the "Interlinks Options" Meta Box.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function interlinks_options_mb_required_capability_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_capability, $input ) ) {
            add_settings_error( 'ila_interlinks_options_mb_required_capability', 'ila_interlinks_options_mb_required_capability', esc_attr__( 'Please enter a valid capability in the "Interlinks Options Meta Box" option.', 'ila' ) );
            $output = get_option( 'ila_interlinks_options_mb_required_capability' );
        } else {
            $output = $input;
        }

        return trim( $output );
    }

    public function interlinks_optimization_mb_required_capability_callback( $args )
    {
        $html = '<input type="text" id="ila_interlinks_optimization_mb_required_capability" name="ila_interlinks_optimization_mb_required_capability" class="regular-text" value="' . esc_attr( get_option( "ila_interlinks_optimization_mb_required_capability" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'The capability required to get access on the "Interlinks Optimization" Meta Box.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function interlinks_optimization_mb_required_capability_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_capability, $input ) ) {
            add_settings_error( 'ila_interlinks_optimization_mb_required_capability', 'ila_interlinks_optimization_mb_required_capability', esc_attr__( 'Please enter a valid capability in the "Interlinks Optimization Meta Box" option.', 'ila' ) );
            $output = get_option( 'ila_interlinks_optimization_mb_required_capability' );
        } else {
            $output = $input;
        }

        return trim( $output );
    }

    public function interlinks_suggestions_mb_required_capability_callback( $args )
    {
        $html = '<input type="text" id="ila_interlinks_suggestions_mb_required_capability" name="ila_interlinks_suggestions_mb_required_capability" class="regular-text" value="' . esc_attr( get_option( "ila_interlinks_suggestions_mb_required_capability" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'The capability required to get access on the "Interlinks Suggestions" Meta Box.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function interlinks_suggestions_mb_required_capability_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_capability, $input ) ) {
            add_settings_error( 'ila_interlinks_suggestions_mb_required_capability', 'ila_interlinks_suggestions_mb_required_capability', esc_attr__( 'Please enter a valid capability in the "Interlinks Suggestions Meta Box" option.', 'ila' ) );
            $output = get_option( 'ila_interlinks_suggestions_mb_required_capability' );
        } else {
            $output = $input;
        }

        return trim( $output );
    }

    //advanced ---------------------------------------------------------------------------------------------------------
    public function default_enable_ail_on_post_callback( $args )
    {
        $html = '<select id="ila_default_enable_ail_on_post" name="ila_default_enable_ail_on_post" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_default_enable_ail_on_post" ) ), 0, false )) . ' value="0">' . esc_attr__( 'No', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_default_enable_ail_on_post" ) ), 1, false )) . ' value="1">' . esc_attr__( 'Yes', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'This option determines the default status of the "Enable AIL" option available in the "Interlinks Options" meta box.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function default_enable_ail_on_post_validation( $input )
    {
        return ( intval( $input, 10 ) == 1 ? '1' : '0' );
    }

    public function filter_priority_callback( $args )
    {
        $html = '<input maxlength="11" type="text" id="ila_filter_priority" name="ila_filter_priority" class="regular-text" value="' . esc_attr(intval( get_option( "ila_filter_priority" ), 10 ) ). '" />';
        $html .= '<div class="help-icon" title="' . esc_attr__( 'This option determines the priority of the filter used to apply the AIL. A lower number corresponds with an earlier execution.', 'ila' ) . '"></div>';
        echo  esc_html($html) ;
    }

    public function filter_priority_validation( $input )
    {

        if ( intval( $input, 10 ) < -2147483648 or intval( $input, 10 ) > 2147483646 ) {
            add_settings_error( 'ila_filter_priority', 'ila_filter_priority', esc_attr__( 'Please enter a number from -2147483648 to 2147483646 in the "Filter Priority" option.', 'ila' ) );
            $output = get_option( 'ila_filter_priority' );
        } else {
            $output = $input;
        }

        return intval( $output, 10 );
    }

    public function ail_test_mode_callback( $args )
    {
        $html = '<select id="ila_ail_test_mode" name="ila_ail_test_mode" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_ail_test_mode" ) ), 0, false )) . ' value="0">' . esc_attr__( 'No', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_ail_test_mode" ) ), 1, false )) . ' value="1">' . esc_attr__( 'Yes', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'With the test mode enabled the AIL will be applied to your posts, pages or custom post types only if the user that is requesting the posts, pages or custom post types has the capability defined with the "AIL Menu" option.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function ail_test_mode_validation( $input )
    {
        return ( intval( $input, 10 ) == 1 ? '1' : '0' );
    }

    public function random_prioritization_callback( $args )
    {
        $html = '<select id="ila_random_prioritization" name="ila_random_prioritization" class="daext-display-none">';
        $html .= '<option ' . esc_attr (selected( intval( get_option( "ila_random_prioritization" ) ), 0, false ) ). ' value="0">' . esc_attr__( 'No', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr (selected( intval( get_option( "ila_random_prioritization" ) ), 1, false ) ). ' value="1">' . esc_attr__( 'Yes', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr__( "With this option enabled the order used to apply the AIL with the same priority is randomized on a per-post basis. With this option disabled the order used to apply the AIL with the same priority is the order used to add them in the back-end. It's recommended to enable this option for a better distribution of the AIL.", 'ila' ) . '"></div>';
        echo  esc_html($html) ;
    }

    public function random_prioritization_validation( $input )
    {
        return ( intval( $input, 10 ) == 1 ? '1' : '0' );
    }

    public function ignore_self_ail_callback( $args )
    {
        $html = '<select id="ila_ignore_self_ail" name="ila_ignore_self_ail" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_ignore_self_ail" ) ), 0, false )) . ' value="0">' . esc_attr__( 'No', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_ignore_self_ail" ) ), 1, false )) . ' value="1">' . esc_attr__( 'Yes', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'With this option enabled, the AIL, which have as a target the post where they should be applied, will be ignored.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function ignore_self_ail_validation( $input )
    {
        return ( intval( $input, 10 ) == 1 ? '1' : '0' );
    }

    public function general_limit_mode_callback( $args )
    {
        $html = '<select id="ila_general_limit_mode" name="ila_general_limit_mode" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_general_limit_mode" ) ), 0, false )) . ' value="0">' . esc_attr__( 'Auto', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_general_limit_mode" ) ), 1, false )) . ' value="1">' . esc_attr__( 'Manual', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr__( 'If "Auto" is selected the maximum number of AIL per post is automatically generated based on the length of the post, in this case the "General Limit (Characters per AIL)" option is used. If "Manual" is selected the maximum number of AIL per post is equal to the value of the "General Limit (Amount)" option.', 'ila' ) . '"></div>';
        echo  esc_html($html) ;
    }

    public function general_limit_mode_validation( $input )
    {
        return ( intval( $input, 10 ) == 1 ? '1' : '0' );
    }

    public function characters_per_autolink_callback( $args )
    {
        $html = '<input maxlength="7" type="text" id="ila_characters_per_autolink" name="ila_characters_per_autolink" class="regular-text" value="' . esc_attr(intval( get_option( "ila_characters_per_autolink" ), 10 )) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr__( 'This value is used to automatically determine the maximum number of AIL per post when the "General Limit Mode" option is set to "Auto".', 'ila' ) . '"></div>';
        echo  esc_html($html) ;
    }

    public function characters_per_autolink_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_number_ten_digits, $input ) or intval( $input, 10 ) < 1 or intval( $input, 10 ) > 1000000 ) {
            add_settings_error( 'ila_characters_per_autolink', 'ila_characters_per_autolink', esc_attr__( 'Please enter a number from 1 to 1000000 in the "General Limit (Characters per AIL)" option.', 'ila' ) );
            $output = get_option( 'ila_characters_per_autolink' );
        } else {
            $output = $input;
        }

        return intval( $output, 10 );
    }

    public function max_number_autolinks_per_post_callback( $args )
    {
        $html = '<input maxlength="7" type="text" id="ila_max_number_autolinks_per_post" name="ila_max_number_autolinks_per_post" class="regular-text" value="' . esc_attr(intval( get_option( "ila_max_number_autolinks_per_post" ), 10 )) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'This value determines the maximum number of AIL per post when the "General Limit Mode" option is set to "Manual".') . '"></div>';
        echo  esc_html($html) ;
    }

    public function max_number_autolinks_per_post_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_number_ten_digits, $input ) or intval( $input, 10 ) < 1 or intval( $input, 10 ) > 1000000 ) {
            add_settings_error( 'ila_max_number_autolinks_per_post', 'ila_max_number_autolinks_per_post', esc_attr__( 'Please enter a number from 1 to 1000000 in the "General Limit (Amount)" option.', 'ila' ) );
            $output = get_option( 'ila_max_number_autolinks_per_post' );
        } else {
            $output = $input;
        }

        return intval( $output, 10 );
    }

    public function general_limit_subtract_mil_callback( $args )
    {
        $html = '<select id="ila_general_limit_subtract_mil" name="ila_general_limit_subtract_mil" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_general_limit_subtract_mil" ), 10 ), 0, false )) . ' value="0">' . esc_attr__( 'No', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_general_limit_subtract_mil" ), 10 ), 1, false )) . ' value="1">' . esc_attr__( 'Yes', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr__( 'With this option enabled the number of MIL included in the post will be subtracted from the maximum number of AIL allowed in the post.', 'ila' ) . '"></div>';
        echo  esc_html($html) ;
    }

    public function general_limit_subtract_mil_validation( $input )
    {
        return ( intval( $input, 10 ) == 1 ? '1' : '0' );
    }

    public function same_url_limit_callback( $args )
    {
        $html = '<input maxlength="7" type="text" id="ila_same_url_limit" name="ila_same_url_limit" class="regular-text" value="' . esc_attr(intval( get_option( "ila_same_url_limit" ), 10 ) ). '" />';
        $html .= '<div class="help-icon" title="' . esc_attr__( 'This option limits the number of AIL with the same URL to a specific value.', 'ila' ) . '"></div>';
        echo  esc_html($html) ;
    }

    public function same_url_limit_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_number_ten_digits, $input ) or intval( $input, 10 ) < 1 or intval( $input, 10 ) > 1000000 ) {
            add_settings_error( 'ila_same_url_limit', 'ila_same_url_limit', esc_attr__( 'Please enter a number from 1 to 1000000 in the "Same URL Limit" option.', 'ila' ) );
            $output = get_option( 'ila_same_url_limit' );
        } else {
            $output = $input;
        }

        return intval( $output, 10 );
    }

    public function wizard_rows_callback( $args )
    {
        $html = '<input maxlength="7" type="text" id="ila_wizard_rows" name="ila_wizard_rows" class="regular-text" value="' .esc_attr(intval( get_option( "ila_wizard_rows" ), 10 ) ). '" />';
        $html .= '<div class="help-icon" title="' . esc_attr__( 'This option determines the number of rows available in the table of the Wizard menu.', 'ila' ) . '"></div>';
        echo  esc_html($html) ;
    }

    public function wizard_rows_validation( $input )
    {

        if ( !preg_match( $this->shared->regex_number_ten_digits, $input ) or intval( $input, 10 ) < 100 or intval( $input, 10 ) > 10000 ) {
            add_settings_error( 'ila_wizard_rows', 'ila_wizard_rows', esc_attr__( 'Please enter a number from 100 to 10000 in the "Wizard Rows" option.', 'ila' ) );
            $output = get_option( 'ila_wizard_rows' );
        } else {
            $output = $input;
        }

        return intval( $output, 10 );
    }

    public function protected_tags_callback( $args )
    {
        $protected_tags_a = $this->shared->get_protected_tags_option();
        $html = '<select id="ila-protected-tags" name="ila_protected_tags[]" class="daext-display-none" multiple>';
        $list_of_html_tags = array(
            'a',
            'abbr',
            'acronym',
            'address',
            'applet',
            'area',
            'article',
            'aside',
            'audio',
            'b',
            'base',
            'basefont',
            'bdi',
            'bdo',
            'big',
            'blockquote',
            'body',
            'br',
            'button',
            'canvas',
            'caption',
            'center',
            'cite',
            'code',
            'col',
            'colgroup',
            'datalist',
            'dd',
            'del',
            'details',
            'dfn',
            'dir',
            'div',
            'dl',
            'dt',
            'em',
            'embed',
            'fieldset',
            'figcaption',
            'figure',
            'font',
            'footer',
            'form',
            'frame',
            'frameset',
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'head',
            'header',
            'hgroup',
            'hr',
            'html',
            'i',
            'iframe',
            'img',
            'input',
            'ins',
            'kbd',
            'keygen',
            'label',
            'legend',
            'li',
            'link',
            'map',
            'mark',
            'menu',
            'meta',
            'meter',
            'nav',
            'noframes',
            'noscript',
            'object',
            'ol',
            'optgroup',
            'option',
            'output',
            'p',
            'param',
            'pre',
            'progress',
            'q',
            'rp',
            'rt',
            'ruby',
            's',
            'samp',
            'script',
            'section',
            'select',
            'small',
            'source',
            'span',
            'strike',
            'strong',
            'style',
            'sub',
            'summary',
            'sup',
            'table',
            'tbody',
            'td',
            'textarea',
            'tfoot',
            'th',
            'thead',
            'time',
            'title',
            'tr',
            'tt',
            'u',
            'ul',
            'var',
            'video',
            'wbr'
        );
        foreach ( $list_of_html_tags as $key => $tag ) {
            $html .= '<option value="' . $tag . '" ' . $this->shared->selected_array( $protected_tags_a, $tag ) . '>' . $tag . '</option>';
        }
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr__( 'With this option you are able to determine in which HTML tags the AIL should not be applied.', 'ila' ) . '"></div>';
        echo  esc_html($html) ;
    }

    public function protected_tags_validation( $input )
    {

        if ( is_array( $input ) ) {
            return $input;
        } else {
            return '';
        }

    }

    public function protected_gutenberg_blocks_callback( $args )
    {
        $protected_gutenberg_blocks_a = get_option( "ila_protected_gutenberg_blocks" );

        // Escaping
        $protected_gutenberg_blocks_a = esc_attr($protected_gutenberg_blocks_a);

        $html = '<select id="ila-protected-gutenberg-blocks" name="ila_protected_gutenberg_blocks[]" class="daext-display-none" multiple>';
        $html .= '<option value="paragraph" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'paragraph' )) . '>' . esc_attr__( 'Paragraph', 'ila' ) . '</option>';
        $html .= '<option value="image" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'image' ) ). '>' . esc_attr__( 'Image', 'ila' ) . '</option>';
        $html .= '<option value="heading" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'heading' ) ). '>' . esc_attr__( 'Heading', 'ila' ) . '</option>';
        $html .= '<option value="gallery" ' . esc_attr( $this->shared->selected_array( $protected_gutenberg_blocks_a, 'gallery' ) ). '>' . esc_attr__( 'Gallery', 'ila' ) . '</option>';
        $html .= '<option value="list" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'list' )) . '>' . esc_attr__( 'List', 'ila' ) . '</option>';
        $html .= '<option value="quote" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'quote' )) . '>' . esc_attr__( 'Quote', 'ila' ) . '</option>';
        $html .= '<option value="audio" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'audio' )) . '>' . esc_attr__( 'Audio', 'ila' ) . '</option>';
        $html .= '<option value="cover-image" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'cover-image' ) ). '>' . esc_attr__( 'Cover Image', 'ila' ) . '</option>';
        $html .= '<option value="subhead" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'subhead' ) ). '>' . esc_attr__( 'Subhead', 'ila' ) . '</option>';
        $html .= '<option value="video" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'video' ) ). '>' . esc_attr__( 'Video', 'ila' ) . '</option>';
        $html .= '<option value="code" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'code' ) ). '>' . esc_attr__( 'Code', 'ila' ) . '</option>';
        $html .= '<option value="html" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'html' ) ). '>' . esc_attr__( 'Custom HTML', 'ila' ) . '</option>';
        $html .= '<option value="preformatted" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'preformatted' ) ). '>' . esc_attr__( 'Preformatted', 'ila' ) . '</option>';
        $html .= '<option value="pullquote" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'pullquote' ) ). '>' . esc_attr__( 'Pullquote', 'ila' ) . '</option>';
        $html .= '<option value="table" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'table' ) ). '>' . esc_attr__( 'Table', 'ila' ) . '</option>';
        $html .= '<option value="verse" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'verse' ) ). '>' . esc_attr__( 'Verse', 'ila' ) . '</option>';
        $html .= '<option value="button" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'button' ) ). '>' . esc_attr__( 'Button', 'ila' ) . '</option>';
        $html .= '<option value="columns" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'columns' ) ). '>' . esc_attr__( 'Columns (Experimentals)', 'ila' ) . '</option>';
        $html .= '<option value="more" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'more' ) ). '>' . esc_attr__( 'More', 'ila' ) . '</option>';
        $html .= '<option value="nextpage" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'nextpage' ) ). '>' . esc_attr__( 'Page Break', 'ila' ) . '</option>';
        $html .= '<option value="separator" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'separator' ) ). '>' . esc_attr__( 'Separator', 'ila' ) . '</option>';
        $html .= '<option value="spacer" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'spacer' ) ). '>' . esc_attr__( 'Spacer', 'ila' ) . '</option>';
        $html .= '<option value="text-columns" ' .esc_attr( $this->shared->selected_array( $protected_gutenberg_blocks_a, 'text-columns' ) ). '>' . esc_attr__( 'Text Columnns', 'ila' ) . '</option>';
        $html .= '<option value="shortcode" ' .esc_attr( $this->shared->selected_array( $protected_gutenberg_blocks_a, 'shortcode' ) ). '>' . esc_attr__( 'Shortcode', 'ila' ) . '</option>';
        $html .= '<option value="categories" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'categories' ) ). '>' . esc_attr__( 'Categories', 'ila' ) . '</option>';
        $html .= '<option value="latest-posts" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'latest-posts' ) ). '>' . esc_attr__( 'Latest Posts', 'ila' ) . '</option>';
        $html .= '<option value="embed" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'embed' ) ). '>' . esc_attr__( 'Embed', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/twitter" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/twitter' )) . '>' . esc_attr__( 'Twitter', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/youtube" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/youtube' )) . '>' . esc_attr__( 'YouTube', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/facebook" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/facebook' )) . '>' . esc_attr__( 'Facebook', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/instagram" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/instagram' )) . '>' . esc_attr__( 'Instagram', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/wordpress" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/wordpress' )) . '>' . esc_attr__( 'WordPress', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/soundcloud" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/soundcloud' )) . '>' . esc_attr__( 'SoundCloud', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/spotify" ' .esc_attr( $this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/spotify' ) ). '>' . esc_attr__( 'Spotify', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/flickr" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/flickr' ) ). '>' . esc_attr__( 'Flickr', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/vimeo" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/vimeo' )) . '>' . esc_attr__( 'Vimeo', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/animoto" ' .esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/animoto' )) . '>' . esc_attr__( 'Animoto', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/cloudup" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/cloudup' )) . '>' . esc_attr__( 'Cloudup', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/collegehumor" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/collegehumor' ) ). '>' . esc_attr__( 'CollegeHumor', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/dailymotion" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/dailymotion' ) ). '>' . esc_attr__( 'DailyMotion', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/funnyordie" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/funnyordie' ) ). '>' . esc_attr__( 'Funny or Die', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/hulu" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/hulu' ) ). '>' . esc_attr__( 'Hulu', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/imgur" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/imgur' ) ). '>' . esc_attr__( 'Imgur', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/issuu" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/issuu' ) ). '>' . esc_attr__( 'Issuu', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/kickstarter" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/kickstarter' ) ) . '>' . esc_attr__( 'Kickstarter', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/meetup-com" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/meetup-com' ) ). '>' . esc_attr__( 'Meetup.com', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/mixcloud" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/mixcloud' ) ). '>' . esc_attr__( 'Mixcloud', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/photobucket" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/photobucket' ) ) . '>' . esc_attr__( 'Photobucket', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/polldaddy" ' .esc_attr( $this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/polldaddy' ) ). '>' . esc_attr__( 'Polldaddy', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/reddit" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/reddit' ) ) . '>' . esc_attr__( 'Reddit', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/reverbnation" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/reverbnation' ) ). '>' . esc_attr__( 'ReverbNation', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/screencast" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/screencast' ) ). '>' . esc_attr__( 'Screencast', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/scribd" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/scribd' )) . '>' . esc_attr__( 'Scribd', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/slideshare" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/slideshare' ) ). '>' . esc_attr__( 'Slideshare', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/smugmug" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/smugmug' ) ) . '>' . esc_attr__( 'SmugMug', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/speaker" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/speaker' ) ) . '>' . esc_attr__( 'Speaker', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/ted" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/ted' ) ) . '>' . esc_attr__( 'Ted', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/tumblr" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/tumblr' ) ). '>' . esc_attr__( 'Tumblr', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/videopress" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/videopress' ) ). '>' . esc_attr__( 'VideoPress', 'ila' ) . '</option>';
        $html .= '<option value="core-embed/wordpress-tv" ' . esc_attr($this->shared->selected_array( $protected_gutenberg_blocks_a, 'core-embed/wordpress-tv' ) ). '>' . esc_attr__( 'WordPress.tv', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr('With this option you are able to determine in which Gutenberg blocks the AIL should not be applied.' ) . '"></div>';
        echo  esc_html($html) ;
    }

    public function protected_gutenberg_blocks_validation( $input )
    {

        if ( is_array( $input ) ) {
            return $input;
        } else {
            return '';
        }

    }

    public function protected_gutenberg_custom_blocks_callback( $args )
    {
        $html = '<input type="text" id="ila_protected_gutenberg_custom_blocks" name="ila_protected_gutenberg_custom_blocks" class="regular-text" value="' . esc_attr( get_option( "ila_protected_gutenberg_custom_blocks" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'Enter a list of Gutenberg custom blocks, separated by a comma.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function protected_gutenberg_custom_blocks_validation( $input )
    {
        if ( strlen( trim( $input ) ) > 0 and !preg_match( $this->shared->regex_list_of_gutenberg_blocks, $input ) ) {
            add_settings_error( 'ila_protected_gutenberg_custom_blocks', 'ila_protected_gutenberg_custom_blocks', __( 'Please enter a valid list of Gutenberg custom blocks separated by a comma in the "Protected Gutenberg Custom Blocks" option.', 'ila' ) );
            $output = get_option( 'ila_protected_gutenberg_custom_blocks' );
        } else {
            $output = $input;
        }

        return $output;
    }

    public function protected_gutenberg_custom_void_blocks_callback( $args )
    {
        $html = '<input type="text" id="ila_protected_gutenberg_custom_void_blocks" name="ila_protected_gutenberg_custom_void_blocks" class="regular-text" value="' . esc_attr( get_option( "ila_protected_gutenberg_custom_void_blocks" ) ) . '" />';
        $html .= '<div class="help-icon" title="' . esc_attr( 'Enter a list of Gutenberg custom void blocks, separated by a comma.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function protected_gutenberg_custom_void_blocks_validation( $input )
    {

        if ( strlen( trim( $input ) ) > 0 and !preg_match( $this->shared->regex_list_of_gutenberg_blocks, $input ) ) {
            add_settings_error( 'ila_protected_gutenberg_custom_void_blocks', 'ila_protected_gutenberg_custom_void_blocks', __( 'Please enter a valid list of Gutenberg custom void blocks separated by a comma in the "Protected Gutenberg Custom Void Blocks" option.', 'ila' ) );
            $output = get_option( 'ila_protected_gutenberg_custom_void_blocks' );
        } else {
            $output = $input;
        }

        return $output;
    }

    public function pagination_dashboard_menu_callback( $args )
    {
        $html = '<select id="ila_pagination_dashboard_menu" name="ila_pagination_dashboard_menu" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_dashboard_menu" ) ), 10, false )) . ' value="10">' . esc_attr__( '10', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_dashboard_menu" ) ), 20, false )). ' value="20">' . esc_attr__( '20', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_dashboard_menu" ) ), 30, false )) . ' value="30">' . esc_attr__( '30', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_dashboard_menu" ) ), 40, false )) . ' value="40">' . esc_attr__( '40', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_dashboard_menu" ) ), 50, false )) . ' value="50">' . esc_attr__( '50', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_dashboard_menu" ) ), 60, false )) . ' value="60">' . esc_attr__( '60', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_dashboard_menu" ) ), 70, false )) . ' value="70">' . esc_attr__( '70', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_dashboard_menu" ) ), 80, false )) . ' value="80">' . esc_attr__( '80', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_dashboard_menu" ) ), 90, false )) . ' value="90">' . esc_attr__( '90', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_dashboard_menu" ) ), 100, false )) . ' value="100">' . esc_attr__( '100', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'This options determines the number of elements per page displayed in the "Dashboard" menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function pagination_dashboard_menu_validation( $input )
    {
        return intval( $input, 10 );
    }

    public function pagination_juice_menu_callback( $args )
    {
        $html = '<select id="ila_pagination_juice_menu" name="ila_pagination_juice_menu" class="daext-display-none">';
        $html .= '<option ' .esc_attr( selected( intval( get_option( "ila_pagination_juice_menu" ) ), 10, false ) ). ' value="10">' . esc_attr__( '10', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_juice_menu" ) ), 20, false ) ). ' value="20">' . esc_attr__( '20', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_juice_menu" ) ), 30, false ) ). ' value="30">' . esc_attr__( '30', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_juice_menu" ) ), 40, false ) ). ' value="40">' . esc_attr__( '40', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_juice_menu" ) ), 50, false ) ). ' value="50">' . esc_attr__( '50', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_juice_menu" ) ), 60, false ) ). ' value="60">' . esc_attr__( '60', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_juice_menu" ) ), 70, false ) ). ' value="70">' . esc_attr__( '70', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_juice_menu" ) ), 80, false ) ). ' value="80">' . esc_attr__( '80', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_juice_menu" ) ), 90, false ) ). ' value="90">' . esc_attr__( '90', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_juice_menu" ) ), 100, false )) . ' value="100">' . esc_attr__( '100', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'This options determines the number of elements per page displayed in the "ILA" menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function pagination_juice_menu_validation( $input )
    {
        return intval( $input, 10 );
    }

    public function pagination_hits_menu_callback( $args )
    {
        $html = '<select id="ila_pagination_hits_menu" name="ila_pagination_hits_menu" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_hits_menu" ) ), 10, false )) . ' value="10">' . esc_attr__( '10', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_hits_menu" ) ), 20, false ) ). ' value="20">' . esc_attr__( '20', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_hits_menu" ) ), 30, false ) ). ' value="30">' . esc_attr__( '30', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_hits_menu" ) ), 40, false )) . ' value="40">' . esc_attr__( '40', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_hits_menu" ) ), 50, false )) . ' value="50">' . esc_attr__( '50', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_hits_menu" ) ), 60, false ) ). ' value="60">' . esc_attr__( '60', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_hits_menu" ) ), 70, false ) ). ' value="70">' . esc_attr__( '70', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_hits_menu" ) ), 80, false ) ). ' value="80">' . esc_attr__( '80', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_hits_menu" ) ), 90, false )) . ' value="90">' . esc_attr__( '90', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_hits_menu" ) ), 100, false ) ). ' value="100">' . esc_attr__( '100', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'This options determines the number of elements per page displayed in the "Hits" menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function pagination_hits_menu_validation( $input )
    {
        return intval( $input, 10 );
    }

    public function pagination_ail_menu_callback( $args )
    {
        $html = '<select id="ila_pagination_ail_menu" name="ila_pagination_ail_menu" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_ail_menu" ) ), 10, false ) ). ' value="10">' . esc_attr__( '10', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_ail_menu" ) ), 20, false ) ). ' value="20">' . esc_attr__( '20', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_ail_menu" ) ), 30, false ) ). ' value="30">' . esc_attr__( '30', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_ail_menu" ) ), 40, false ) ). ' value="40">' . esc_attr__( '40', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_ail_menu" ) ), 50, false ) ). ' value="50">' . esc_attr__( '50', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_ail_menu" ) ), 60, false ) ). ' value="60">' . esc_attr__( '60', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_ail_menu" ) ), 70, false ) ). ' value="70">' . esc_attr__( '70', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_ail_menu" ) ), 80, false ) ). ' value="80">' . esc_attr__( '80', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_ail_menu" ) ), 90, false ) ). ' value="90">' . esc_attr__( '90', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_ail_menu" ) ), 100, false )) . ' value="100">' . esc_attr__( '100', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'This options determines the number of elements per page displayed in the "AIL" menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function pagination_ail_menu_validation( $input )
    {
        return intval( $input, 10 );
    }

    public function pagination_categories_menu_callback( $args )
    {
        $html = '<select id="ila_pagination_categories_menu" name="ila_pagination_categories_menu" class="daext-display-none">';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_categories_menu" ) ), 10, false ) ). ' value="10">' . esc_attr__( '10', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_categories_menu" ) ), 20, false ) ). ' value="20">' . esc_attr__( '20', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_categories_menu" ) ), 30, false ) ). ' value="30">' . esc_attr__( '30', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_categories_menu" ) ), 40, false ) ). ' value="40">' . esc_attr__( '40', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_categories_menu" ) ), 50, false ) ). ' value="50">' . esc_attr__( '50', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_categories_menu" ) ), 60, false ) ). ' value="60">' . esc_attr__( '60', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_categories_menu" ) ), 70, false ) ). ' value="70">' . esc_attr__( '70', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_categories_menu" ) ), 80, false ) ). ' value="80">' . esc_attr__( '80', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_categories_menu" ) ), 90, false ) ). ' value="90">' . esc_attr__( '90', 'ila' ) . '</option>';
        $html .= '<option ' . esc_attr(selected( intval( get_option( "ila_pagination_categories_menu" ) ), 100, false ) ) . ' value="100">' . esc_attr__( '100', 'ila' ) . '</option>';
        $html .= '</select>';
        $html .= '<div class="help-icon" title="' . esc_attr( 'This options determines the number of elements per page displayed in the "Categories" menu.') . '"></div>';
        echo  esc_html($html) ;
    }

    public function pagination_categories_menu_validation( $input )
    {
        return intval( $input, 10 );
    }

    //meta box -----------------------------------------------------------------
    public function create_meta_box()
    {

        if ( current_user_can( get_option( $this->shared->get( 'slug' ) . "_interlinks_options_mb_required_capability" ) ) ) {
            /*
             * Load the "Interlinks Options" meta box only in the post types defined
             * with the "Interlinks Options Post Types" option
             */
            $interlinks_options_post_types = preg_replace( '/\\s+/', '', get_option( $this->shared->get( 'slug' ) . '_interlinks_options_post_types' ) );
            $interlinks_options_post_types_a = explode( ',', $interlinks_options_post_types );
            foreach ( $interlinks_options_post_types_a as $key => $post_type ) {
                // add_meta_box( 'ila-meta-options', esc_attr__('Interlinks Options', 'ila'), array($this, 'create_options_meta_box_callback'), $post_type, 'normal', 'high' );
            }
        }


        if ( current_user_can( get_option( $this->shared->get( 'slug' ) . "_interlinks_optimization_mb_required_capability" ) ) ) {
            /*
             * Load the "Interlinks Optimization" meta box only in the post types
             * defined with the "Interlinks Optimization Post Types" option
             */
            $interlinks_optimization_post_types = preg_replace( '/\\s+/', '', get_option( $this->shared->get( 'slug' ) . '_interlinks_optimization_post_types' ) );
            $interlinks_optimization_post_types_a = explode( ',', $interlinks_optimization_post_types );
            foreach ( $interlinks_optimization_post_types_a as $key => $post_type ) {
                add_meta_box(
                    'ila-meta-optimization',
                    esc_attr__( 'Interlinks Outgoing Links', 'ila' ),
                    array( $this, 'create_optimization_meta_box_callback' ),
                    $post_type,
                    'side',
                    'default'
                );
            }
        }


        if ( current_user_can( get_option( $this->shared->get( 'slug' ) . "_interlinks_suggestions_mb_required_capability" ) ) ) {
            /*
             * Load the "Interlinks Suggestions" meta box only in the post types
             * defined with the "Interlinks Suggestions Post Types" option
             */
            $interlinks_suggestions_post_types = preg_replace( '/\\s+/', '', get_option( $this->shared->get( 'slug' ) . '_interlinks_suggestions_post_types' ) );
            $interlinks_suggestions_post_types_a = explode( ',', $interlinks_suggestions_post_types );
            foreach ( $interlinks_suggestions_post_types_a as $key => $post_type ) {
                add_meta_box(
                    'ila-meta-suggestions',
                    esc_attr__( 'Interlinks Incoming Links', 'ila' ),
                    array( $this, 'create_suggestions_meta_box_callback' ),
                    $post_type,
                    'side',
                    'default'
                );
            }
        }

    }

    //display the Interlinks Options meta box content
    public function create_options_meta_box_callback( $post )
    {
        //retrieve the Interlinks Manager data values
        $seo_power = get_post_meta( $post->ID, '_ila_seo_power', true );
        if ( strlen( trim( $seo_power ) ) == 0 ) {
            $seo_power = (int) get_option( $this->shared->get( 'slug' ) . '_default_seo_power' );
        }
        $enable_ail = get_post_meta( $post->ID, '_ila_enable_ail', true );
        //if the $enable_ail is empty use the Enable AIL option as a default
        if ( strlen( trim( $enable_ail ) ) == 0 ) {
            $enable_ail = get_option( $this->shared->get( 'slug' ) . '_default_enable_ail_on_post' );
        }
        ?>

        <table class="form-table table-interlinks-options">
            <tbody>

            <tr>
                <th scope="row"><label><?php
        esc_attr_e( 'SEO Power', 'ila' );
        ?></label></th>
                <td>
                    <input type="text" name="ila_seo_power" value="<?php
        echo  esc_attr(intval( $seo_power, 10 )) ;
        ?>" class="regular-text" maxlength="7">
                    <div class="help-icon" title="<?php
        esc_attr_e( 'The SEO Power of this post.', 'ila' );
        ?>"></div>
                </td>
            </tr>

            <tr>
                <th scope="row"><label><?php
        esc_attr_e( 'Enable AIL', 'ila' );
        ?></label></th>
                <td>
                    <select id="ila-enable-ail" class="daext-display-none" name="ila_enable_ail">
                        <option <?php
        selected( intval( $enable_ail, 10 ), 0 );
        ?> value="0"><?php
        esc_attr_e( 'No', 'ila' );
        ?></option>
                        <option <?php
        selected( intval( $enable_ail, 10 ), 1 );
        ?>value="1"><?php
        esc_attr_e( 'Yes', 'ila' );
        ?></option>
                    </select>
                    <div class="help-icon" title="<?php
        esc_attr_e( 'Select "Yes" to enable the AIL in this post.', 'ila' );
        ?>"></div>

                </td>
            </tr>

            </tbody>
        </table>

        <?php
        // Use nonce for verification
        wp_nonce_field( plugin_basename( __FILE__ ), 'ila_nonce' );
    }

    //display the Interlinks Optimization meta box content
    public function create_optimization_meta_box_callback( $post )
    {
        ?>

        <div class="meta-box-body">
            <table class="form-table">
                <tbody>

                        <?php
        $id = $post->ID;
        // echo $this->shared->generate_interlinks_optimization_metabox_html($post);
        global  $wpdb ;
        $table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_archive";
        //   echo "SELECT * FROM $table_name ORDER BY post_date DESC WHERE post_id=$id";
        $results = $wpdb->get_results( "SELECT * FROM {$table_name} WHERE post_id={$id}", ARRAY_A );
        //  print_r($results);

        if ( !empty($results) > 0 ) {
            foreach ( $results as $result ) {
                $outgoing_links = unserialize( $result['manual_interlinks'] );
                $items = [];
                $current_item = "";
                $items = $outgoing_links;

                if ( !empty($items) ) {
                    foreach ( $items as $i => $link ) {
                        $o_link = "";
                        $o_text = "";
                        $i_link = "";
                        $i_text = "";

                        if ( isset( $outgoing_links[$i] ) ) {
                            $outgoing_links[$i] = str_replace( '&', '&amp;', $outgoing_links[$i] );
                            $a = new SimpleXMLElement( $outgoing_links[$i] );
                            $o_link = $a['href'];
                            $o_text = $a;
                        }


                        if ( !empty($o_link) ) {
                            $csv_content = $this->esc_csv( $o_link );
                            $csv_content = $this->esc_csv( $o_text );
                            echo  "<tr><td><a href=" . esc_attr($o_link) . " target='blank'>" . esc_attr($o_text) . "</a></td></tr>" ;
                        } else {
                            $csv_content = '"' . $this->esc_csv( "-" ) . '",';
                            $csv_content = '"' . $this->esc_csv( "-" ) . '",';
                            // echo "<tr><td>No </td></tr>";
                        }

                        //  echo $total = '"' . $this->esc_csv(count($outgoing_links)) . '"';
                    }
                } else {
                    echo  '<tr><td><p>' . esc_attr__( 'No outgoing links available', 'ila' ) . '</p></td></tr>' ;
                }

            }
        } else {
            echo  '<tr><td><p>' . esc_attr__( 'No outgoing links available', 'ila' ) . '</p></td></tr>' ;
        }

        ?>


                </tbody>
            </table>
        </div>

        <?php
    }

    //display the Interlinks Suggestions meta box content
    public function create_suggestions_meta_box_callback( $post )
    {
        ?>

        <div class="meta-box-body">
            <table class="form-table">
                <tbody>
                        <?php
        $id = $post->ID;
        // echo $this->shared->generate_interlinks_optimization_metabox_html($post);
        global  $wpdb ;
        $table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_archive";
        //   echo "SELECT * FROM $table_name ORDER BY post_date DESC WHERE post_id=$id";
        $results = $wpdb->get_results( "SELECT * FROM {$table_name} WHERE post_id={$id}", ARRAY_A );
        //  print_r($results);

        if ( !empty($results) > 0 ) {
            foreach ( $results as $result ) {
                $incoming_links = unserialize( $result['recommended_interlinks'] );
                $items = [];
                $current_item = "";
                $items = $incoming_links;

                if ( !empty($items) ) {
                    foreach ( $items as $i => $link ) {
                        $o_link = "";
                        $o_text = "";
                        $i_link = "";
                        $i_text = "";

                        if ( isset( $incoming_links[$i] ) ) {
                            $incoming_links[$i] = str_replace( '&', '&amp;', $incoming_links[$i] );
                            $a = new SimpleXMLElement( $incoming_links[$i] );
                            $o_link = $a['href'];
                            $o_text = $a;
                        }


                        if ( !empty($o_link) ) {
                            $csv_content = $this->esc_csv( $o_link );
                            $csv_content = $this->esc_csv( $o_text );
                            echo  "<tr><td><a href=" . esc_attr($o_link) . " target='blank'>" . esc_attr($o_text) . "</a></td></tr>" ;
                        } else {
                            $csv_content = '"' . $this->esc_csv( "-" ) . '",';
                            $csv_content = '"' . $this->esc_csv( "-" ) . '",';
                            // echo "<tr><td>No </td></tr>";
                        }

                        //  echo $total = '"' . $this->esc_csv(count($outgoing_links)) . '"';
                    }
                } else {
                    echo  '<tr><td><p>' . esc_attr__( 'No incoming links available', 'ila' ) . '</p></td></tr>' ;
                }

            }
        } else {
            echo  '<tr><td><p>' . esc_attr__( 'No incoming links available', 'ila' ) . '</p></td></tr>' ;
        }

        ?>
                </tbody>
            </table>
        </div>

        <?php
    }

    //Save the Interlinks Options meta data
    public function ila_save_meta_interlinks_options( $post_id )
    {
        //security verifications -----------------------------------------------
        // verify if this is an auto save routine.
        // If it is our form has not been submitted, so we dont want to do anything
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        /*
         * verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times
         */
        if ( !isset( $_POST['ila_nonce'] ) || !wp_verify_nonce( $_POST['ila_nonce'], plugin_basename( __FILE__ ) ) ) {
            return;
        }
        //verify the capability
        if ( !current_user_can( get_option( $this->shared->get( 'slug' ) . "_interlinks_options_mb_required_capability" ) ) ) {
            return;
        }
        //end security verifications -------------------------------------------
        //save the "SEO Power" only if it's included in the allowed values
        if ( intval( $_POST['ila_seo_power'], 10 ) != 0 and intval( $_POST['ila_seo_power'], 10 ) <= 1000000 ) {
            update_post_meta( $post_id, '_ila_seo_power', intval( sanitize_text_field($_POST['ila_seo_power'] )) );
        }
        //save the "Enable AIL"
        update_post_meta( $post_id, '_ila_enable_ail', intval( sanitize_text_field($_POST['ila_enable_ail']), 10 ) );
    }

    /*
     * The "Export CSV" buttons and/or icons available in the Dashboard, Juice
     * and Hits menus are intercepted and the proper method that generates on
     * the fly the specific downloadable CSV file is called
     */
    public function export_csv_controller()
    {
        /*
         * Intercept requests that come from the "Export CSV" button from the
         * "Dashboard" menu and generate the downloadable CSV file with the
         * dashboard_menu_export_csv() method
         */
        if ( isset( $_GET['page'] ) and $_GET['page'] == 'ila-dashboard' and isset( $_POST['export_csv'] ) ) {
            $this->dashboard_menu_export_csv();
        }
        if ( isset( $_GET['page'] ) and $_GET['page'] == 'ila-audit' and isset( $_POST['export_csv'] ) ) {
            $this->dashboard_menu_export_csv();
        }
    }

    /*
     * Generates the downloadable CSV file with all the items available in the
     * Dashboard menu
     */
    private function dashboard_menu_export_csv()
    {
    }

    /*
     * Escape the double quotes of the $content string, so the returned string
     * can be used in CSV fields enclosed by double quotes
     *
     * @param $content The unescape content ( Ex: She said "No!" )
     * @return string The escaped content ( Ex: She said ""No!"" )
     */
    private function esc_csv( $content )
    {
        return str_replace( '"', '""', $content );
    }

    protected function renderGen()
    {
        if ( function_exists( 'ila_fs' ) ) {
            echo  '<div class="daext-widget">' ;
            echo  '<h3 class="daext-widget-title">Generate InternalLink Audit</h3>' ;
            echo  '<div class="daext-widget-content">' ;
            echo  '<p>' . esc_attr_e( "This procedure allows you to generate data and statistics about the internal links of your blog.", "ila" ) . '</p>' ;
            echo  '</div>' ;
            echo  '<div class="daext-widget-submit">' ;
            echo  '<input id="ajax-request-status" type="hidden" value="inactive">' ;
            echo  '<input class="widget_btn" id="update-archive" type="button" value="Generate Data" style="background: #FF8800!important;color: #ffffff!important;">' ;
            echo  '<img id="ajax-loader" src=' . esc_url($this->shared->get( 'url' )) . 'admin/assets/img/ajax-loader.gif' . '>' ;
            echo  '</div></div>' ;
        }

    }

    protected function renderCSV()
    {
        if ( function_exists( 'ila_fs' ) ) {
        }
    }

    protected function ignore_words()
    {
        $ignore_words = '';
        global  $wpdb ;
        $table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_ignore_words";
        $results = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY id DESC", ARRAY_A );
        //if there are data generate the csv header and content

        if ( count( $results ) > 0 ) {
            foreach ( $results as $words ) {
                $word[] = $words['words'];
            }
            $ignore_words = implode( ', ', $word );
        }

        return $ignore_words;
    }

    protected function renderPromo()
    {
        echo  '<div class="daext-widget"><div class="postbox ila-postbox">' ;
        echo  '<h3 class="daext-widget-title">' . esc_attr__( 'Activate Pro Version', 'ila' ) . '</h3>' ;
        echo  '<div class="inside">' ;
        echo  '<h4>' . esc_attr__( '"Achieve even more with InternalLink Audit Pro:"', 'ila' ) . '</h4>' ;
        echo  '<ul>' ;
        echo  '<li><span>' . esc_attr__( 'Keep Records of All Link', 'ila' ) . '</span>: ' . esc_attr__( ' Count All internal links with a single click!', 'ila' ) . '</li>' ;
        echo  '<li><span>' . esc_attr__( 'CSV Export ', 'ila' ) . '</span>: ' . esc_attr__( 'No more copying and pasting data, just click and export ALL internal links in CSV format!', 'ila' ) . '</li>' ;
        echo  '<li><span>' . esc_attr__( 'Orphan Pages ', 'internal-links' ) . '</span>: ' . esc_attr__( 'Find all orphan pages with a single click!', 'ila' ) . '</li>' ;
        echo  '<li><span>' . esc_attr__( 'In-depth Report', 'internal-links' ) . '</span>: ' . esc_attr__( 'With the in-depth reports, take control of site structure and get the data to truly optimize your site.', 'ila' ) . '</li>' ;
        echo  '</ul>' ;
        echo  '<p><a href="' . esc_url(get_admin_url( null, 'admin.php?page=' . Ila_Admin::ILA_MENUPAGE_SLUG . '-pricing' )) . '" class="widget_btn">&raquo; ' . esc_attr__( 'Upgrade Now!', 'internal-links' ) . '</a></p>' ;
        echo  '</div>' ;
        echo  '</div></div>' ;
    }

    protected function support()
    {
        echo  '<div class="daext-widget">' ;
        echo  '<div class="postbox ilj-postbox info">' ;
        echo  '<h3 class="daext-widget-title">' . esc_attr__( 'Support us', 'internallinks-audit' ) . '</h3>' ;
        echo  '<div class="inside">' ;
        echo  '<p>' ;
        echo  esc_attr__( 'Do you like the plugin? Then please rate us or tell your friends about the InternalLink Audit.', 'internal-links' ) ;
        echo  '</p>' ;
        echo  '<p><a href="https://wordpress.org/support/plugin/internallink-audit/reviews/" class="button button-primary" target="_blank" rel="noopener">&raquo; ' . esc_attr__( 'Give us your review', 'internallinks-audit' ) . '</a></p>' ;
        echo  '<p class="divide">Are you looking for a <strong>new feature</strong> or have <strong>suggestions for improvement</strong>? Have you <strong>found a bug</strong>? Please tell us about</p>' ;
        echo  '<p><a href="' . esc_url(get_admin_url( null, 'admin.php?page=' . Ila_Admin::ILA_MENUPAGE_SLUG . '-contact' )) . '" class="button">&raquo; ' . __( 'Get in touch with us', 'internal-links-audit' ) . '</a></p>' ;
        echo  '<p class="divide"><strong>' . esc_attr__( 'Thank you for using the Internal Link Audit!', 'internal-links' ) . '</strong></p>' ;
        echo  '</div>' ;
        echo  '</div></div>' ;
    }

    protected function upgrade()
    {
       echo '<div class="postbox ila-statistic"><div class="inside"><h2 style="font-size:24px;color:#FF8800">Upgrade to Internal Link Audit Premium</h2><ul class="ila-ressources"><li style="font-size: 14px;font-weight: 600;line-height: 20px;width: 50%;float: left;">1) Count All internal links with a single click!</li>
                    <li style="font-size: 14px;font-weight: 600;line-height: 20px;width: 50%;float: left;">2) Find all orphan pages with a single click!</li>
                    <li style="font-size: 14px;font-weight: 600;line-height: 20px;width: 50%;float: left;">3) No more copying and pasting data, just click and export ALL internal links in CSV format!</li>
                    <li style="font-size: 14px;font-weight: 600;line-height: 20px;width: 50%;float: left;">4) Maintain Link Juice by with count of "incoming internal link" and "outgoing internal link" mentions</li>
                    <li style="font-size: 14px;font-weight: 600;line-height: 20px;width: 50%;float: left;">5) Save hours of time and gain more control over your incoming and outgoing internal links.</li>
                    <li style="font-size: 14px;font-weight: 600;line-height: 20px;width: 50%;float: left;">6) With the in-depth reports, take control of site structure and get the data to truly optimize your site.</li></ul><p style="padding-top:10px"><a href="' . esc_url(get_admin_url( null, 'admin.php?page=' . Ila_Admin::ILA_MENUPAGE_SLUG . '-pricing' )) . '" class="widget_btn">&raquo; ' . __( 'Upgrade Now!', 'internal-links' ) . '</a></p></div></div>';
    }
    public static function getIgnoreFile()
    {
        $file = plugin_dir_path( __FILE__ ) . '/inc/ignore_words_list.txt';
        return $file;
    }

    public static function strtolower( $string )
    {
        // if the wamania project is active, use their strtolower function

        if ( class_exists( 'Wamania\\Snowball\\Utf8' ) ) {
            return Wamania\Snowball\Utf8::strtolower( $string );
        } else {
            return strtolower( $string );
        }

    }

    public static function getAllIgnoreWordLists()
    {
        $all_ignore_lists = array();
        $ignore_words_file = plugin_dir_path( __FILE__ ) . '/inc/ignore_words_list.txt';
        $words = array();
        if ( file_exists( $ignore_words_file ) ) {
            $words = file( $ignore_words_file );
        }
        if ( empty($words) ) {
            $words = array();
        }
        foreach ( $words as $key => $word ) {
            $words[$key] = trim( self::strtolower( $word ) );
        }
        //print_r($words);
        $all_ignore_lists = $words;
        return $all_ignore_lists;
    }

    public function ignorewords_save()
    {

        if ( isset( $_POST['hidden_action'] ) and $_POST['hidden_action'] == 'ila_save_settings' ) {
            global  $wpdb ;
            $table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_ignore_words";
            $result = $wpdb->query( "TRUNCATE TABLE {$table_name}" );

            if ( !empty($_POST['ignore_words_textarea']) ) {
                $ignore_words = sanitize_textarea_field( stripslashes( trim( $_POST['ignore_words_textarea'] ) ) );
                $ignore_words = mb_split( "\\s", $ignore_words );
                $ignore_words = array_unique( $ignore_words );
                $ignore_words = array_filter( array_map( 'trim', $ignore_words ) );
                sort( $ignore_words );
                $ignore_words = implode( PHP_EOL, $ignore_words );
                $file_open = fopen( self::getIgnoreFile(), "w+" );
                fwrite( $file_open, $ignore_words );
                fclose( $file_open );
                header( 'Location:admin.php?page=ila-settings&success' );
                exit;
            } else {
                header( 'Location:admin.php?page=ila-settings&success' );
                exit;
            }

        } else {
            header( 'Location:admin.php?page=ila-settings&success' );
            exit;
        }

    }

    protected function getVersion()
    {
        return ILA_VERSION;
    }

}
