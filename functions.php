<?

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
    echo "\n :::fsplit ::: ".$file_name;

    $i=0;
    for($i;$i<$parts;$i++){
        //read buffer sized amount from file
        $file_part = fread($file_handle, $buffer);
        //the filename of the part
        $file_part_path = $store_path.$file_name."00$i";
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

?>