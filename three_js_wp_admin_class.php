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
        
        if (isset($atts['name']) && isset($atts['dist'])) {//Chequeo si existen los parametros y no son nulos

            $model = $this->getModel($atts['name']);
            
            return
            "
            <div id='". $atts['name']."' class='model-canvas'></div>
            <script type='module'>
            import init from '".plugins_url( 'includes/js/main.js',__FILE__ )."';
            
            new init('". $atts['name']."','".$atts['dist']."','".$model->path_file."');
            </script>
            ";
        }
        else
            echo 'Error';
    }

    private function getModel($models_name){
        global $wpdb;

        $model = $wpdb->get_row(
            "SELECT * FROM json_models_path WHERE models_name = '$models_name';"
        );

        return $model;
    }

    public function list_models(){
        global $wpdb;
        $models = $wpdb->get_results(
            "SELECT * FROM json_models_path"
        );

        echo "<h2> List of models </h2>";
        echo "<div>";
        if(empty($models)){
            echo "There is no models yet";
        }
        foreach($models as $model){
            echo "<p>$model->models_name</p><p>$model->path_file</p>";
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
       
        $this->test_handle_post();

       ?>
       <div>
        <h2>
            New Model
        </h2>
        <form method='post' action='' name='myform' enctype='multipart/form-data'>
        <label for="model_name">Model's name </label>
        <input type="text" id="model_name" name="model_name">
        <input type="file" id='upload_json' name='upload_json' accept=".json">
        <? submit_button('Upload') ?>
        </form>
       </div>
       <?
    }


    private function test_handle_post(){

        if(isset($_FILES['upload_json'])){
            $json = $_FILES['upload_json'];
            $overrides = array( 'test_form' => false );

            $uploaded=wp_handle_upload($json,$overrides);

            $path_file = $uploaded['url'];

            $models_name = $_POST['model_name'];

            // Save url image in database
            $this->upload_data_to_db($models_name,$path_file);

            
            // Error checking using WP functions
            if(is_wp_error($uploaded)){

                echo "Error uploading file: " . $uploaded->get_error_message();
            }else{
                
                echo "<bold> File upload successful! </bold>";
            }
        }
    }

    private function upload_data_to_db($models_name,$path_file){
        global $wpdb;
        try{
         $wpdb->query(
             "INSERT INTO json_models_path VALUES ( '$models_name', '$path_file' );"
         );
        }catch (Exception $e){
            throw new Exception("Models name taken");
        }
    }

    private function delete_data_from_db($models_name){
        global $wpdb;
        
        $table = "json_models_path";
        $wpdb->delete($table, array( 'models_name' => $models_name ));
    }



}

