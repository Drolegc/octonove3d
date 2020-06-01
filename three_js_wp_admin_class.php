<?php
class AdminClass {

    const NOMBRE_BD = "babylon_models_paid";

    public function __construct(){
        // Shortcode example    
        add_shortcode( 'octonove3d', array($this,'shortcode') );
        add_shortcode( 'set_octonove3d', array($this,'set_configurations_shortcode'));
        add_shortcode( 'octonove3d_new_model', array($this, 'new_model'));

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
        add_menu_page( 'Octonove3D', 'Octonove3D', 'manage_options', 'octonove3d-admin-menu',array($this,'help'), '', 200 );
        add_submenu_page( 'octonove3d-admin-menu', 'list-models', 'Models list', 'manage_options', 'list_models_slug', array($this,'list_models'), null);
        add_submenu_page( 'octonove3d-admin-menu', 'new-model', 'New model', 'manage_options', 'new_model_slug', array($this,'new_model'), null);

    }

    public function set_configurations_shortcode(){
        return "
        <script src='".plugins_url( 'includes/build/axios.min.js',__FILE__ )."'></script>
        <script src='".plugins_url( 'includes/build/babylon.js',__FILE__ )."'></script>
        ";
    }

    public function shortcode($atts){
        
        if(isset($atts['user'])){
            
            $models = $this->getModelsUser($atts['user']);
            $response = "";
            foreach ($models as $model) {
                $response = $response."
                <div id='". $model->models_name."' class='".$this->getClassCSS($atts)."'>
                <canvas id='". $model->models_name."-canvas'></canvas>
                <div class='details'>
                    <p>".ucfirst($model->models_name)."</p>
                </div>
                </div>
                <script type='module'>
                import init from '".plugins_url( 'includes/js/main.js',__FILE__ )."';
                
                new init('".$model->path_file."','". $model->models_name."-canvas');
                </script>
                ";
            }
            return $response;
        }

        if (isset($atts['name'])) {

            $model = $this->getModel($atts['name']);
            
            return
            "
            <div id='". $atts['name']."' class='".$this->getClassCSS($atts)."'>
                <canvas id='". $atts['name']."-canvas'></canvas>
                <div class='details'>
                    <p>".ucfirst($atts['name'])."</p>
                    <small>".$model->user."</small>
                </div>
            </div>
            
            <script type='module'>
            import init from '".plugins_url( 'includes/js/main.js',__FILE__ )."';
            
            new init('".$model->path_file."','". $atts['name']."-canvas');
            </script>
            ";
        }
        
        return "Shortcode Error";
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

        $db = self::NOMBRE_BD;
        $models = $wpdb->get_results(
            "SELECT * FROM $db WHERE user = '$username';"
        );
        return $models;
    }

    private function getClassCSS($atts){
        if(!isset($atts['style'])) return 'model-card';

        switch($atts['style']){
            case 'all-screen':
                return 'model-all-screen';
            case 'card':
            default:
                return 'model-card';
        }
        
    }

    public function list_models(){

        $this->delete_handle_post();

        global $wpdb;
        $models = $wpdb->get_results(
            "SELECT * FROM babylon_models_paid"
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
                <input type='text' id='model_name' name='model_name' value="<?php echo $model->models_name?>" />
                <label for='path'>Path </label>
                <input type='text' id='path' name='path' value="<?php echo "uploads".end($split);?>" />
                <input type="submit" value="Delete">
            </form>
            <?php
        }
        echo "</div>";
    } 

    public function help(){
        ?>
        <div>
        <h1>Sobre OCTONOVE3D</h1>
        <p>
        Octonove3D es un plugin que permite mostrar,junto con la libreria BabylonJS, modelos 3D.
        </p>
        <div>
            <h2>Guia de como usar Octonove3D</h2>
            <ul style="padding: 1%;">
                <li>
                    <h2>Como subir un modelo</h2>
                    <ul>
                        <li>
                            Dado que el plugin solo acepta .babylon como extension, es necasario importar el modelo que se tenga al <a href="https://sandbox.babylonjs.com/">sandbox de BabylonJS</a>, y exportarlo como .babylon
                        </li>
                        <li>Una vez que se tenga el modelo .babylon, ir al menu, New Model, llenar el formulario y dar subir</li>
                    </ul>
                </li>
                <li>
                    <h2>Shortcodes</h2>
                    <ul>
                        <li>
                            set_octonove ( este shortcode importa la libraria de babylonjs y axios, es necesario incorporarlo antes de mostrar el o los modelos )
                        </li>
                        <li>
                            octonove_3d ( muestra el modelo que se especifique, de lo contrario mostrara error).
                            </br><b>Parametros obligatorios</b>: name (nombre del modelo que ya se haya guardado en la base de datos)
                            </br><b>Parametros opcionales</b>: style (opciones: card,all-screen) ; user ( nombre de un usuario registrado, si se usa esta opcion no es necesario hacer uso del parametro name)
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
        </div>
        <?php 
    }

    public function new_model(){
       
        if( !is_user_logged_in() ) return;

        $this->new_model_handle_post();

        ?>
        <div id="new_model">
         <h2>
             New Model
         </h2>
         <form method='post' action='' name='myform' enctype='multipart/form-data'>
         <label for="model_name">Model's name </label>
         <input type="text" id="model_name" name="model_name">
         <input type="file" id='upload_json' name='upload_json' accept=".babylon" >
         <input type="submit" value="Upload">
         </form>
        </div>
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
                $model_file = explode('uploads',$_POST['path']);
                $file = $upload_info['basedir'] . end($model_file);

                wp_delete_file( $file );

                $wpdb->query(
                    "DELETE FROM babylon_models_paid WHERE models_name = '".$_POST['model_name']."';"
                );

                echo "Models deleted";
            }catch (Exception $e){
                throw new Exception("Model ".$_POST['model_name']."does not exist.");
            }
        }
    }

    private function new_model_handle_post(){

        if(isset($_FILES['upload_json'])){
            //Chequear primero si el nombre del modelo existe

            if($this->check_model_exists($_POST['model_name'])){
                echo "<b style='color:red'>Error: Modelo con el mismo nombre ya existe</b>";
                return;
            }

            $model_json = $_FILES['upload_json'];
            $overrides = array( 'test_form' => false );
            // Subimos el archivo 
            $file_uploaded = wp_handle_upload($model_json,$overrides);
            if(isset($file_uploaded["error"])){
                echo "<b style='color:red'>Error: ".$file_uploaded["error"]."</b>";
                return;
            }

            // Obtenemos el path
            $path_file = $file_uploaded['file'];
            $path_file = explode('uploads',$path_file);
            $path_file = end($path_file);

            $models_name = $_POST['model_name'];
            // Guardamos nombre del modelo y path
            $this->upload_data_to_db($models_name,wp_upload_dir()['baseurl'].$path_file);
            
            echo "<b style='color:green;'> File upload successful! </b>";
        }
    }

    private function check_model_exists($models_name){
        global $wpdb;
        
        $db = self::NOMBRE_BD;
        return $wpdb->get_var("SELECT COUNT(1) FROM $db WHERE models_name='$models_name'");
    }

    private function upload_data_to_db($models_name,$url_file){
        global $wpdb;
        try{
         $wpdb->query(
             "INSERT INTO babylon_models_paid VALUES ( '$models_name', '$url_file' );"
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

        $table = "babylon_models_paid";
        $wpdb->delete($table, array( 'models_name' => $models_name ));

    }



}


?>