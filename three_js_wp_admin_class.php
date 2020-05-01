<?

class ThreeJSWPAdminClass {

    public function __construct(){
        // Shortcode example    
        add_shortcode( 'threejs_octonove', array($this,'threejswp_shortcode') );

        // Add CSS
        add_action( 'wp_enqueue_scripts',array($this,'register_css'));
        add_action( 'wp_enqueue_scripts',array($this, 'load_css'));
        
        // Menus
        add_action( 'admin_menu', array($this,'menu_page') );
    }

    public function register_css(){
        wp_register_style( 'modelscss', plugin_dir_url( __FILE__ ).'includes/css/style.css' );
    }

    public function load_css(){
        wp_enqueue_style( 'modelscss' );
    }

    public function menu_page(){
        add_menu_page( 'ThreeJSWP', 'ThreeJSWP', 'manage_options', 'threejswp-admin-menu',array($this,'help'), '', 200 );
        add_submenu_page( 'threejswp-admin-menu', 'list-models', 'Models list', 'manage_options', 'list_models_slug', array($this,'list_models'), null);
        add_submenu_page( 'threejswp-admin-menu', 'new-model', 'New model', 'manage_options', 'new_model_slug', array($this,'new_model'), null);

    }

    public function threejswp_shortcode($atts){
        
        if (isset($atts['name']) && isset($atts['dist'])) {

            $model = $this->getModel($atts['name']);
            
            return
            "
            <div id='". $atts['name']."' class='model-canvas'>
                <canvas id='". $atts['name']."-canvas'></canvas>
            </div>
            <script src='https://cdn.babylonjs.com/babylon.max.js'></script>
            <script src='https://cdn.babylonjs.com/loaders/babylonjs.loaders.min.js'></script>
            <script src='".plugins_url( 'includes/build/axios.min.js',__FILE__ )."'></script>
            <script type='module'>
            import init from '".plugins_url( 'includes/js/main.js',__FILE__ )."';
            
            new init('".$model->path_file."','". $atts['name']."-canvas');
            </script>
            ";
        }
        else
            echo 'Error';
    }

    private function getModel($models_name){
        global $wpdb;

        $model = $wpdb->get_row(
            "SELECT * FROM json_models_path_free WHERE models_name = '$models_name';"
        );

        return $model;
    }

    public function list_models(){

        $this->delete_handle_post();

        global $wpdb;
        $models = $wpdb->get_results(
            "SELECT * FROM json_models_path_free"
        );

        echo "<h2> List of models </h2>";
        echo "<div>";
        if(empty($models)){
            echo "There is no models yet";
        }
        foreach($models as $model){
            $split = explode('uploads',$model->path_file);
            ?>
            <form action="" method='post' name='myform' enctype='multipart/form-data'>
            <label for='model_name'>Name </label>
        <input type='text' id='model_name' name='model_name' value="<?echo $model->models_name?>" />
        <label for='path'>Path </label>
        <input type='text' id='path' name='path' value="<?echo "uploads".end($split);?>" />
        <input type="submit" value="Delete">
            </form>
            <?
        }
        echo "</div>";
    }

    public function help(){
        ?>
        <div>
        <h2>About THREEJS Octonove</h2>
        <p>
        Fusce vulputate eleifend sapien. Fusce fermentum.
        Sed in libero ut nibh placerat accumsan. Sed in libero ut nibh placerat accumsan.
        </p>
        </div>
        <?
    }

    public function new_model(){
       
        $this->new_model_handle_post();

       ?>
       <div>
        <h2>
            New Model
        </h2>
        <form method='post' action='' name='myform' enctype='multipart/form-data'>
        <label for="model_name">Model's name </label>
        <input type="text" id="model_name" name="model_name">
        <input type="file" id='upload_json' name='upload_json' >
        <input type="submit" value="Upload">
        </form>
       </div>
       <?
    }

    private function delete_handle_post(){

        if(isset($_POST['model_name']) && isset($_POST['path'])){
            global $wpdb;
            try{

                $upload_info = wp_get_upload_dir();
                $model_file = explode('/',$_POST['path']);
                $file = $upload_info['basedir'] .'/'. $model_file[1];

                echo var_dump($file);
                wp_delete_file( $file );

                $wpdb->query(
                    "DELETE FROM json_models_path WHERE models_name = '".$_POST['model_name']."';"
                );

                echo "Models deleted";
            }catch (Exception $e){
                throw new Exception("Model ".$_POST['model_name']."does not exist.");
            }
        }
    }

    private function new_model_handle_post(){

        if(isset($_FILES['upload_json'])){
            // Obtenemos el archivo .json
            $model_json = $_FILES['upload_json'];
            $overrides = array( 'test_form' => false );
            // Subimos el archivo 
            $file_uploaded = wp_handle_upload($model_json,$overrides);
            // Obtenemos el path
            $path_file = $file_uploaded['file'];
            $path_file = explode('uploads',$path_file);
            $path_file = end($path_file);

            $models_name = $_POST['model_name'];
            // Guardamos nombre del modelo y path
            $this->upload_data_to_db($models_name,wp_upload_dir()['baseurl'].$path_file);
            
            // Error checking using WP functions
            if(is_wp_error($file_uploaded)){

                echo "Error uploading file: " . $file_uploaded->get_error_message();
            }else{
                
                echo "<bold> File upload successful! </bold>";
                wp_delete_file( $path_file );

            }
        }
    }

    private function upload_data_to_db($models_name,$url_file){
        global $wpdb;
        try{
         $wpdb->query(
             "INSERT INTO json_models_path_free VALUES ( '$models_name', '$url_file' );"
         );
        }catch (Exception $e){
            throw new Exception("Models name taken");
        }
    }

    private function delete_data_from_db($models_name){
        global $wpdb;
        
        $model = $wpdb->get_row(
            "SELECT * FROM json_models_path WHERE models_name = '$models_name';"
        );

        $table = "json_models_path";
        $wpdb->delete($table, array( 'models_name' => $models_name ));

    }



}

