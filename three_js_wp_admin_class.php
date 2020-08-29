<?php

include 'functions.php';

class AdminClass {

    const NOMBRE_BD = "octonove3d_safe";

    public function __construct(){
        // Shortcode example    
        add_shortcode( 'octonove3d', array($this,'shortcode') );
        add_shortcode( 'set_octonove3d', array($this,'set_configurations_shortcode'));
        add_shortcode( 'octonove3d_new_model', array($this, 'new_model'));
        add_shortcode( 'octonove3d_preview_card',array($this, 'preview_card'));

        // Add CSS
        add_action( 'wp_enqueue_scripts',array($this,'register_css'));
        add_action( 'wp_enqueue_scripts',array($this, 'load_css'));
        add_action('admin_enqueue_scripts', array($this,'add_css_to_new_model_on_dashboard'));

        // Menus
        add_action( 'admin_menu', array($this,'menu_page') );

        // Endpoint
        add_action( 'rest_api_init' ,function(){
            register_rest_route('octonove3d/v1', '/model', array(
                'methods' => 'GET',
                'callback' => 'getModelData'
            ));
        });
        
    }

    public function register_css(){
        wp_register_style( 'modelscss', plugin_dir_url( __FILE__ ).'includes/css/style.css' );
    }

    public function load_css(){
        wp_enqueue_style( 'modelscss' );
    }

    public function menu_page(){
        add_menu_page( 'Octonove3D', 'Octonove3D', 'manage_options', 'octonove3d-admin-menu',array($this,'help'), '', 200 );
        add_submenu_page( 'octonove3d-admin-menu', 'list-models', 'Models list', 'manage_options', 'list_models_slug', array($this,'list_models'), null);
        add_submenu_page( 'octonove3d-admin-menu', 'new-model', 'New model', 'manage_options', 'new_model_slug', array($this,'new_model'), null);

    }

    public function set_configurations_shortcode(){
        return "
        <script src='".plugins_url( 'includes/build/axios.min.js',__FILE__ )."'></script>
        <script src='".plugins_url( 'includes/build/babylon.js',__FILE__ )."'></script>
        <script src='".plugins_url( 'includes/build/crypto.js',__FILE__ )."'></script>
        ";
    }

    public function shortcode($atts){

        if (isset($atts['name'])) {
            
            $model_name = $atts['name'];

            $model = $this->getModel($model_name);
            $file_name = end(explode('uploads',$model->path_file));
            $file_name = end(explode('/',$file_name));

            
            return
            "
            <div id='". $model_name."' class='model-card'>
                <div class='model-section'>
                    <div class='loading' id='".$model_name."-loading'>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <canvas id='". $model_name."-canvas'></canvas>
                </div>
                <div class='details'>
                <span class='preview-card-models-name'>".$model->models_name."</span><span class='preview-card-models-by'>By ".$model->user."</span>
                </div>
                <script type='module' defer>
                import init from '".plugins_url( 'includes/js/main.js',__FILE__ )."';
                
                new init('".$file_name."','". $model_name."','".$model->cant."');
                </script>
            </div>
            
            ";
        }

        if (isset($atts['from-url'])) {
            
            if(!isset($_GET['model'])) return;
            if($_GET['model'] == '') return;

            $model_name = $_GET['model'];

            $model = $this->getModel($model_name);

            if($model == NULL) return;

            $file_name = end(explode('uploads',$model->path_file));
            $file_name = end(explode('/',$file_name));

            
            return
            "
            <div id='". $model_name."' class='model-card'>
                <div class='model-section'>
                    <div class='loading' id='".$model_name."-loading'>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <canvas id='". $model_name."-canvas'></canvas>
                </div>
                <div class='details'>
                <span class='preview-card-models-name'>".$model->models_name."</span><span class='preview-card-models-by'>By ".$model->user."</span>
                </div>
                <script type='module' defer>
                import init from '".plugins_url( 'includes/js/main.js',__FILE__ )."';
                
                new init('".$file_name."','". $model_name."','".$model->cant."');
                </script>
            </div>
            
            ";
        }
        
        return "Shortcode Error";
    }

    public function preview_card($atts){

        $this->handle_delete_model_user($atts);

        if(isset($atts['user'])){
            
            $models = $this->getModelsUser($atts['user']);

            if($models == null) {
                return 
                "
                <div class='preview-card'>
                <div class='preview-card-details'><p style='font-weight: bold; color:red;'>Error - el usuario no tiene modelos</p></div>
                </div>
                ";
            }

            $response = "<div class='".$this->getClassCSSPreview($atts)."'>";
            foreach ($models as $model) {

                $file_name = end(explode('uploads',$model->path_file));
                $file_name = end(explode('/',$file_name));
                $time_id = microtime();
                $delete_btn = '';
                if($model->user == wp_get_current_user()->user_login){
                    $delete_btn = "<span class='delete_btn' onclick='checkBeforeDelete()'>X</span>";
                }
                $response = $response."
                <div class='preview-card'>
                    <canvas class='preview-card-canvas' id='".$model->models_name.$time_id."-preview-card'></canvas>
                    <div class='preview-card-details'>
                        <div><span class='preview-card-models-name'>".$model->models_name."</span><span class='preview-card-models-by'>By ".$model->user."</span></div>
                        <form method='POST' action='' name='delete-model' id='delete-model' enctype='multipart/form-data'>
                            <input type='hidden' name='model_name' id='model_name' value='".$model->models_name."'>
                            ".$delete_btn."
                        </form>
                    </div>
                    <script type='module'>
                    import initPreview from '".plugins_url( 'includes/js/preview-card.js',__FILE__ )."';    
                    
                    initPreview('".$model->models_name.$time_id."-preview-card','".$model->izq_img."','".$model->cntr_img."','".$model->dir_img."','".$model->models_name."');
                    </script>
                </div>
                ";
            }
            $response = $response."</div>";
            return $response;
        }

        if(isset($atts['current_user'])){

            if(!$atts['current_user']) return;
            
            $current_user = wp_get_current_user()->user_login;

            $models = $this->getModelsUser($current_user);

            if($models == null) {
                return 
                "
                <div class='preview-card'>
                <div class='preview-card-details'><p style='font-weight: bold; color:red;'>Error - el usuario no tiene modelos</p></div>
                </div>
                ";
            }

            $response = "<div class='".$this->getClassCSSPreview($atts)."'>";
            foreach ($models as $model) {

                $file_name = end(explode('uploads',$model->path_file));
                $file_name = end(explode('/',$file_name));
                $time_id = microtime();
                $delete_btn = '';
                if($model->user == wp_get_current_user()->user_login){
                    $delete_btn = "<span class='delete_btn' onclick='checkBeforeDelete(this)'>X</span>";
                }
                $response = $response."
                <div class='preview-card'>
                    <canvas class='preview-card-canvas' id='".$model->models_name.$time_id."-preview-card' ></canvas>
                    <div class='preview-card-details'>
                        <div><span class='preview-card-models-name'>".$model->models_name."</span><span class='preview-card-models-by'>By ".$model->user."</span></div>
                        <form method='POST' action='' name='delete-model' enctype='multipart/form-data'>
                            <input type='hidden' name='model_name' id='model_name' value='".$model->models_name."'>
                            ".$delete_btn."
                        </form>
                    </div>
                    <script type='module'>
                    import initPreview from '".plugins_url( 'includes/js/preview-card.js',__FILE__ )."';    
                    
                    initPreview('".$model->models_name.$time_id."-preview-card','".$model->izq_img."','".$model->cntr_img."','".$model->dir_img."','".$model->models_name."');
                    </script>
                </div>
                ";
            }
            $response = $response."</div>";
            $response = $response."<script src='".plugins_url( 'includes/js/functions.js',__FILE__ )."'></script>";
            return $response;
        }

        if (isset($atts['name'])) {

            $model = $this->getModel($atts['name']);

            if($model == null) {
                return 
                "
                <div class='preview-card'>
                <div class='preview-card-details'><p style='font-weight: bold;'>Error - no existe modelo con el nombre dado</p></div>
                </div>
                ";
            }
            
            return
            "
            <div class='preview-card'>
                <canvas class='preview-card-canvas' id='".$model->models_name.$time_id."-preview-card'></canvas>
                <div class='preview-card-details'>
                    <span class='preview-card-models-name'>".$model->models_name."</span><span class='preview-card-models-by'>By ".$model->user."</span>
                </div>
                <script type='module'>
                import initPreview from '".plugins_url( 'includes/js/preview-card.js',__FILE__ )."';
                
                initPreview('".$model->models_name.$time_id."-preview-card','".$model->izq_img."','".$model->cntr_img."','".$model->dir_img."','".$model->models_name."');
                </script>
                </div>
            ";
        }
    }

    private function handle_delete_model_user($atts){

        if(isset($_POST['model_name'])){
            $current_user = wp_get_current_user()->user_login;
            global $wpdb;
            try{

                if(!$this->check_model_exists($_POST['model_name'])){
                    echo "<b style='color:red;'>Este modelo no existe</b>";
                    return;
                }

                $models_name = $_POST['model_name'];
                $model =  $wpdb->get_row(
                    "SELECT * FROM octonove3d_safe WHERE models_name = '$models_name' AND user = '$current_user' ;"
                );

                if($model == null){
                    echo "<b style='color:red;'>El usuario no tiene un modelo con el nombre dado</b>";
                    return;
                }

                $upload_info = wp_get_upload_dir();
                $model_file = explode('uploads/',$model->path_file);
                $file = $upload_info['basedir'] .'/'. $model_file[1];
                deleteDirectory($file);

                $path_izq_img = explode('uploads', $model->izq_img);
                $path_izq_img = wp_upload_dir()['basedir'].'/'.end($path_izq_img);
                wp_delete_file($path_izq_img);

                $path_cntr_img = explode('uploads', $model->cntr_img);
                $path_cntr_img = wp_upload_dir()['basedir'].'/'.end($path_cntr_img);
                wp_delete_file($path_cntr_img);

                $path_dir_img = explode('uploads', $model->dir_img);
                $path_dir_img = wp_upload_dir()['basedir'].'/'.end($path_dir_img);
                wp_delete_file($path_dir_img);

                $wpdb->query(
                    "DELETE FROM octonove3d_safe WHERE models_name = '".$_POST['model_name']."';"
                );

                echo "Models deleted";
            }catch (Exception $e){
                throw new Exception("Model ".$_POST['model_name']."does not exist.");
            }
        }
    }

    private function getModel($models_name){
        global $wpdb;

        $nombre = self::NOMBRE_BD;
        $model = $wpdb->get_row(
            "SELECT * FROM $nombre WHERE models_name = '$models_name';"
        );

        return $model;
    }

    private function getModelsUser($username){
        global $wpdb;

        $models = $wpdb->get_results(
            "SELECT * FROM octonove3d_safe WHERE user = '$username';"
        );

        return $models;
    }

    private function getClassCSSModel($atts){
        if(!isset($atts['style'])) return 'model-card';

        switch($atts['style']){
            case 'all-screen':
                return 'model-all-screen';
            case 'card':
            default:
                return 'model-card';
        }
        
    }

    private function getClassCSSPreview($atts){

        if(!isset($atts['list'])) return '';

        switch($atts['list']){
            case 'horizontal':
                return 'preview-list preview-list-horizontal';
            case 'vertical':
                return 'preview-list preview-list-vertical';
            default:
            return '';
        }
    }

    public function list_models(){

        $this->delete_handle_post();

        global $wpdb;
        $models = $wpdb->get_results(
            "SELECT * FROM octonove3d_safe"
        );
        
        echo "<h2> List of models </h2>";
        echo "<div>";
        if(empty($models)){
            echo "There is no models yet";
        }
        foreach($models as $model){
            $split = explode('uploads',$model->path_file);
            ?>
            <form action="" method='post' name='myform' id="myform" enctype='multipart/form-data'>
                <p for="user">User: <b><?php echo $model->user ?></b> |</p>
                <label for='model_name'>Name </label>
                <input type='text' id='model_name' name='model_name' value="<?php echo $model->models_name?>" />
                <label for='path'>Path </label>
                <input type='text' id='path' name='path' value="<?php echo "uploads".end($split);?>" />
                <button>Delete</button>
            </form>
            <hr>
            <?php
        }
        echo "</div>";
    } 

    public function help(){
        include('help.php');
    }

    public function new_model(){

        if( !is_user_logged_in() ) return;

        $this->new_model_handle_post();

        $css_admin = '';

        if(is_admin()){
            $css_admin = 'admin-dashboard';
        }

        ?>
        
        <div id="new_model">
         <h2>
             New Model
         </h2>
         <form method='post' action='' id='myform' name='myform' enctype='multipart/form-data'>
            <label for="model_name">Model's name </label>
            <input type="text" id="model_name" name="model_name" required>
            <input type="file" id='upload_json' name='upload_json' accept=".babylon" required>
            <input type="hidden"  id="cntr_img" name="cntr_img"  required>
            <input type="hidden"  id="izq_img" name="izq_img"  required>
            <input type="hidden"  id="dir_img" name="dir_img"  required>
            <input type="submit" value="Upload" id="upload_btn" disabled>
         </form>
         <div class="model-section  <?php echo $css_admin?> new_model">
            <div class='loading opaco-bg' id='preview-loading'>
                <div>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
            <canvas id="preview" width="500px" height="300px"></canvas>
         </div>
        </div>
        <script src='<?php echo plugins_url( 'includes/build/babylon.js',__FILE__ ) ?>'></script>
        <script src='<?php echo plugin_dir_url( __FILE__ ).'includes/js/preview.js' ?>'></script>
        <?php
    }

    private function delete_handle_post(){

        if(isset($_POST['model_name']) && isset($_POST['path'])){
            global $wpdb;
            try{

                if(!$this->check_model_exists($_POST['model_name'])){
                    echo "<b style='color:red;'>Este modelo no existe</b>";
                    return;
                }

                $upload_info = wp_get_upload_dir();
                $model_file = explode('/',$_POST['path']);
                $file = $upload_info['basedir'] .'/'. $model_file[1];
                deleteDirectory($file);
                
                $model = $this->getModel($_POST['model_name']);

                $path_izq_img = explode('uploads', $model->izq_img);
                $path_izq_img = wp_upload_dir()['basedir'].'/'.end($path_izq_img);
                wp_delete_file($path_izq_img);

                $path_cntr_img = explode('uploads', $model->cntr_img);
                $path_cntr_img = wp_upload_dir()['basedir'].'/'.end($path_cntr_img);
                wp_delete_file($path_cntr_img);

                $path_dir_img = explode('uploads', $model->dir_img);
                $path_dir_img = wp_upload_dir()['basedir'].'/'.end($path_dir_img);
                wp_delete_file($path_dir_img);

                $wpdb->query(
                    "DELETE FROM octonove3d_safe WHERE models_name = '".$_POST['model_name']."';"
                );

                echo "Models deleted";
            }catch (Exception $e){
                throw new Exception("Model ".$_POST['model_name']."does not exist.");
            }
        }
    }

    private function new_model_handle_post(){

        require_once(ABSPATH . 'wp-admin/includes/file.php');

        if(isset($_FILES['upload_json'])){

            if(pathinfo($_FILES['upload_json']['name'],PATHINFO_EXTENSION) != 'babylon'){
                echo "<b style='color:red>Error: Archivo no tiene la extension .babylon</b>";
            }

            if($this->check_model_exists($_POST['model_name'])){
                echo "<b style='color:red'>Error: Modelo con el mismo nombre ya existe</b>";
                return;
            }

            $model_json = $_FILES['upload_json'];
            $overrides = array( 'test_form' => false );
            // Subimos el archivo 
            $file_uploaded = wp_handle_upload($model_json,$overrides);

            if(isset($file_uploaded["error"])){
                echo "<b style='color:red'>Error subiendo modelo: ".$file_uploaded["error"]."</b>";
                return;
            }

            $file_cntr_img = $this->base64_image_to_file($_POST['cntr_img']);
            if(isset($file_cntr_img['error'])){
                echo "<b style='color:red'>Error:  subiendo img cntr ".$file_cntr_img["error"]."</b>";
                return;
            }
            $file_izq_img = $this->base64_image_to_file($_POST['izq_img']);
            if(isset($file_izq_img['error'])){
                echo "<b style='color:red'>Error:  subiendo img izq ".$file_izq_img["error"]."</b>";
                return;
            }
            $file_dir_img = $this->base64_image_to_file($_POST['dir_img']);
            if(isset($file_dir_img['error'])){
                echo "<b style='color:red'>Error: subiendo img dir ".$file_dir_img["error"]."</b>";
                return;
            }


            $path_file = $file_uploaded['file'];
            
            $models_name = $_POST['model_name'];

            $dir_name = md5($models_name);
            wp_mkdir_p( wp_upload_dir()['basedir'].'/'.$dir_name );
            $models_dir = wp_upload_dir()['basedir'].'/'.$dir_name.'/';

            $data_file_count = $this->split_file($file_uploaded['file'],$models_dir);

            $new_path_file_id = $data_file_count["file_name"];
            $dir_file = wp_upload_dir()['baseurl'].'/'.$dir_name.'/'.$new_path_file_id;

            // Guardamos nombre del modelo y path
            $this->upload_data_to_db($models_name,$dir_file,$data_file_count["count"],$file_izq_img['tmp_name'],$file_cntr_img['tmp_name'],$file_dir_img['tmp_name']);
            
            // Delete aux file

            wp_delete_file($file_uploaded['file']);

            echo "<br><b style='color:green;'> File upload successful! </b>";
        }
    }

    private function split_file($path_file,$models_dir){

        $quinientos_kb = 512000;
        $new_file = fsplit($path_file,$quinientos_kb,$models_dir);
        
        return $new_file;
    }

    private function check_model_exists($models_name){
        global $wpdb;
        
        $db = self::NOMBRE_BD;
        return $wpdb->get_var("SELECT COUNT(1) FROM $db WHERE models_name='$models_name'");
    }

    private function upload_data_to_db($models_name,$url_file,$cant,$izq_img,$cntr_img,$dir_img){
        global $wpdb;
        try{
            $db = self::NOMBRE_BD;
            $current_user = wp_get_current_user()->user_login;
            $wpdb->query(
                "INSERT INTO $db VALUES ( '$current_user','$models_name', '$url_file', '$cant', '$izq_img', '$cntr_img', '$dir_img' );"
            );
        }catch (Exception $e){
            throw new Exception("Models name taken");
        }

    }

    private function delete_data_from_db($models_name){
        global $wpdb;
        
        $db = self::NOMBRE_BD;
        $model = $wpdb->get_row(
            "SELECT * FROM $db WHERE models_name = '$models_name';"
        );

        $table = "octonove3d_safe";
        $wpdb->delete($table, array( 'models_name' => $models_name ));

    }

    private function base64_image_to_file($img){

        $upload_dir = wp_upload_dir();

        $upload_path = $upload_dir;

        $decoded =  base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $img));
        $filename = 'my-base64-image.png';

        $hashed_filename = md5( $filename . microtime() ) . '_' . $filename;

        $image_upload = file_put_contents( $upload_path['path'].'/' . $hashed_filename, $decoded );

        if( !function_exists( 'wp_handle_sideload' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        if( !function_exists( 'wp_get_current_user' ) ) {
        require_once( ABSPATH . 'wp-includes/pluggable.php' );
        }

        $file = array();
        //$file['error']    = '';
        $file['tmp_name'] = $upload_path['url'] .'/'. $hashed_filename;
        $file['name']     = $hashed_filename;
        $file['type']     = 'image/png';
        $file['size']     = filesize( $upload_path['path'].'/' . $hashed_filename );
        return $file;
    }

    public function add_css_to_new_model_on_dashboard(){
        wp_enqueue_style('admin-styles', plugin_dir_url( __FILE__ ).'includes/css/style.css');
    }

}


?>