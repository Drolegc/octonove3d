<?php
  /*
  
  Plugin name: 3DMODEL OCTONOVE SAFE
  Plugin URI: http://PLUGIN_URI.com/
  Description: Babylon JS with Wordpress 
  Author: Octonove Agency
  Author URI: http://AUTHOR_URI.com
  Version: 1.5
  */
  if( ! defined('ABSPATH' )) die;

  $path = dirname(__FILE__).'/three_js_wp_admin_class.php';
  if( !file_exists($path)) die("NO EXISTE EL ARCHIVO");
  require_once $path;

  if(!class_exists("AdminClass")) die("THERE'S NO HOPE");
  $var = new AdminClass();

  // Activation
  register_activation_hook( __FILE__, 'activate' );
  // Uninstall
  register_uninstall_hook( __FILE__, 'uninstall' );

  function activate(){

    
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE octonove3d_safe (
        user varchar(55) NOT NULL,
        models_name varchar(55) NOT NULL,
        path_file varchar(300) NOT NULL,
        cant int NOT NULL,
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
     $table_name = $wpdb->prefix . 'octonove3d_safe';
     $sql = "DROP TABLE IF EXISTS $table_name";
     $wpdb->query($sql);
  }

?>
