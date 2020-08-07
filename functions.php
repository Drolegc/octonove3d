<?php

/**
 * A file splitter function for php
 * Can split a file to number of parts depending on the buffer size given
 */

function fsplit($file,$buffer=1024,$dir_path="splits/"){
    //open file to read
    $file_handle = fopen($file,'r');
    //get file size
    $file_size = filesize($file);
    //no of parts to split
    $parts = $file_size / $buffer;
    
    //store all the file names
    $file_parts = array();
    
    //path to write the final files
    $store_path = $dir_path;
    
    //name of input file
    $file_name = basename($file);

    //Encrypted name with md5
    $file_name = md5($file_name);

    $i=0;
    for($i;$i<$parts;$i++){
        //read buffer sized amount from file
        $file_part = fread($file_handle, $buffer);
        $file_part = base64_encode($file_part);
        //the filename of the part
        $file_part_path = $store_path.$file_name."$i";
        //open the new file [create it] to write
        $file_new = fopen($file_part_path,'w+');
        //write the part of file
        fwrite($file_new, $file_part);
        //add the name of the file to part list [optional]
        array_push($file_parts, $file_part_path);
        //close the part file handle
        fclose($file_new);
    }    
    //close the main file handle
    
    fclose($file_handle);
    return [
        "file_name" => $file_name,
        "count" => $i
    ];
}

/** 
 * Delete directory with files inside
*/
function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }

    }

    return rmdir($dir);
}

function Encrypt($passphrase, $plain_text){

    $salt = openssl_random_pseudo_bytes(256);
    $iv = openssl_random_pseudo_bytes(16);

    $iterations = 999;  
    $key = hash_pbkdf2("sha512", $passphrase, $salt, $iterations, 64);

    $encrypted_data = openssl_encrypt($plain_text, 'aes-256-cbc', hex2bin($key), OPENSSL_RAW_DATA, $iv);

    $data = array("m" => base64_encode($encrypted_data), "iv" => bin2hex($iv), "salt" => bin2hex($salt));
    return $data;
}

function getModelData( WP_REST_Request $model){
    global $wpdb;


    if(!isset($_GET['m']) || !isset($_GET['p'])){
        return new WP_Error( 'no_model', 'Invalid model', array( 'status' => 404 ) );
    }

    $model_id = $_GET['m'];
    $model = $wpdb->get_row(
        "SELECT path_file, cant FROM octonove3d_safe WHERE octonove3d_safe.path_file LIKE '%$model_id%';"
    );

    if($model == null){
        return new WP_Error( 'no_model', 'Invalid model', array( 'status' => 404 ) );
    }

    if(!is_numeric($_GET['p'])){
        return new WP_Error( 'no_model', 'Invalid model', array( 'status' => 404 ) );
    }

    if($model->cant < $_GET['p']){
        return new WP_Error( 'no_model', 'Invalid model', array( 'status' => 404 ) );
    }

    $model = explode('uploads',$model->path_file);
    
    $upload_dir = wp_get_upload_dir();
    $file_path = $upload_dir['basedir'].end($model).$_GET['p']; 
    $open_file = fopen($file_path, "r");
    $content = fread($open_file,filesize($file_path));
    fclose($open_file);

    $encrypted_content = Encrypt('condiment coach hypnoses doornail',$content);

    return $encrypted_content;
}

?>