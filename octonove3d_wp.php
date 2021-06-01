<?php
  /*
  
  Plugin name: OCTONOVE 3D
  Plugin URI: http://PLUGIN_URI.com/
  Description: Plugin oficial de Octonove para mostrar modelos 3D mediante Babylon JS en Wordpress 
  Author: Octonove Agency
  Author URI: http://AUTHOR_URI.com
  Version: 1.5
  */
  if( ! defined('ABSPATH' )) die;

  $path = dirname(__FILE__).'/octonove3d_wp_admin_class.php';
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

    $sql = "CREATE TABLE octonove3d (
        user varchar(55) NOT NULL,
        models_name varchar(55) NOT NULL,
        path_file varchar(300) NOT NULL,
        cant int NOT NULL,
        izq_img varchar(255) NOT NULL,
        cntr_img varchar(255) NOT NULL,
        dir_img varchar(255) NOT NULL,
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
     $table_name = $wpdb->prefix . 'octonove3d';
     $sql = "DROP TABLE IF EXISTS $table_name";
     $wpdb->query($sql);
  }

?>
