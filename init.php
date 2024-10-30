<?php

/**
* Plugin Name: InternalLink Audit
* Plugin URI: https://www.onpageseo.tools/internallink-audit-wordpress-plugin/
* Description: Management of InternalLinks (Incoming & Outgoing)
* Version: 0.1.1
* Requires at least: 5.9.0
* Requires PHP: 7.1.0
* Author: OnPageSeoTools
* Author URI: https://www.onpageseo.tools
* Text Domain: internallink-audit
*
* InternalLink Audit is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 2 of the License, or
* any later version.
*
* InternalLink Audit is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with InternalLink Audit. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
//Prevent direct access to this file
if ( !defined( 'WPINC' ) ) {
    die;
}

if ( !function_exists( 'ila_fs' ) ) {
    // Create a helper function for easy SDK access.
    function ila_fs()
    {
        global  $ila_fs ;

        if ( !isset( $ila_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $ila_fs = fs_dynamic_init( array(
                'id'             => '10472',
                'slug'           => 'ila',
                'type'           => 'plugin',
                'public_key'     => 'pk_eceb6f4b76b65d78c85b52de5feba',
                'is_premium'     => false,
                'premium_suffix' => 'Premium',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => array(
                'slug'       => 'ila-dashboard',
                'first-path' => 'admin.php?page=ila-dashboard&welcome-message=true',
                'support'        => false,
                'network'    => true,
                'parent'     => array(
                'slug' => 'ila-dashboard',
            ),
            ),
                'is_live'        => true,
            ) );
        }

        return $ila_fs;
    }

    // Init Freemius.
    ila_fs();
    // Signal that SDK was initiated.
    do_action( 'ila_fs_loaded' );
    //Class shared across public and admin
    require_once plugin_dir_path( __FILE__ ) . 'shared/class-ila-shared.php';
    //Public
    require_once plugin_dir_path( __FILE__ ) . 'public/class-ila-public.php';
    add_action( 'plugins_loaded', array( 'Ila_Public', 'get_instance' ) );
    //Version
    if ( !defined( 'ILA_VERSION' ) ) {
        define( 'ILA_VERSION', '0.1.1' );
    }
    //Admin

    if ( is_admin() && (!defined( 'DOING_AJAX' ) || !DOING_AJAX) ) {
        //Admin
        require_once plugin_dir_path( __FILE__ ) . 'admin/class-ila-admin.php';
        add_action( 'plugins_loaded', array( 'Ila_Admin', 'get_instance' ) );
        //Activate
        register_activation_hook( __FILE__, array( Ila_Admin::get_instance(), 'ac_activate' ) );
    }

    //Ajax

    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        //Admin
        require_once plugin_dir_path( __FILE__ ) . 'class-ila-ajax.php';
        add_action( 'plugins_loaded', array( 'Ila_Ajax', 'get_instance' ) );
    }

    function ila_fs_uninstall_cleanup()
    {
        require_once plugin_dir_path( __FILE__ ) . 'shared/class-ila-shared.php';
        require_once plugin_dir_path( __FILE__ ) . 'admin/class-ila-admin.php';
        //delete options and tables
        Ila_Admin::un_delete();
    }

    ila_fs()->add_action( 'after_uninstall', 'ila_fs_uninstall_cleanup' );
    define( 'WILA_STORE_URL', 'https://www.onpageseo.tools' );
}
