<?php
  /*
  Plugin name: THREEJSWP OCTONOVE
  Plugin URI: http://PLUGIN_URI.com/
  Description: Three JS with Wordpress 
  Author: Octonove Agency
  Author URI: http://AUTHOR_URI.com
  Version: 1.5
  */

  include 'three_js_wp_admin_class.php';

  if( ! defined('ABSPATH' )) {
      die;
  }

  $var = new ThreeJSWPAdminClass();

  // Activation
  register_activation_hook( __FILE__, 'activate' );
  // Uninstall
  register_uninstall_hook( __FILE__, 'uninstall' );

  function activate(){

    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE json_models_path_free (
        models_name varchar(55) NOT NULL,
        path_file varchar(300) NOT NULL,
        UNIQUE KEY models_name (models_name)
    ) $charset_collate;";

    if ( ! function_exists('dbDelta') ) {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    }

    dbDelta( $sql );

    flush_rewrite_rules();

  }

  function uninstall(){
    global $wpdb;
     $table_name = $wpdb->prefix . 'json_models_path';
     $sql = "DROP TABLE IF EXISTS $table_name";
     $wpdb->query($sql);
  }


?>
