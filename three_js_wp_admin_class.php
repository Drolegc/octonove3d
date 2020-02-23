<?

class ThreeJSWPAdminClass {

    public function __construct(){
        // Shortcode example    
        add_shortcode( 'threejs_octonove', array($this,'threejswp_shortcode') );

        // Add CSS
        add_action( 'wp_enqueue_scripts',array($this,'register_css'));
        add_action( 'wp_enqueue_scripts',array($this, 'load_css'));
        
    }
    

    public function register_css(){
        wp_register_style( 'modelscss', plugin_dir_url( __FILE__ ).'includes/css/style.css' );
    }

    public function load_css(){
        wp_enqueue_style( 'modelscss' );
    }

    public function threejswp_shortcode($atts){
        
        if (isset($atts['name']) && isset($atts['id']) && isset($atts['namefile']) && isset($atts['count']) && isset($atts['count'])) {//Chequeo si existen los parametros y no son nulos
            echo '<div id="'. $atts['name']. '-'. $atts['id']. '" class="model-canvas"></div>';
            echo '<script type="module">';
            echo 'import init from "'.plugins_url( 'includes/js/main.js',__FILE__ ). '";';
        
            echo 'new init("'. $atts['name']. '-'. $atts['id']. '","'.$atts['dist'].'" ,"' .plugin_dir_url( __FILE__ ). '", "' . $atts['namefile']. '", '. $atts['count']. ')';
            echo '</script>';
        }
        else
            echo 'Error';

        
            /*
        $atts = shortcode_atts(
            array(
                'name' => 'example',
                'dist' => 5,
                'id' => 1,
                'namefile' => '',
                'count' => 23
            ),$atts
        );

        // Id of the div

        $name = $atts['name'];
        $id = $atts['id'];
        $div_id = "{$name}{$id}";
        $dist = $atts['dist'];
        $namefile = $atts['namefile'];
        $count = $atts['count'];

        
        return
         "
        <div id='".$div_id."' class='model-canvas'></div>
        <script type='module'>
        import init from '".plugins_url( 'includes/js/main.js',__FILE__ )."';
        
        new init('".$div_id."','".$dist."','".plugin_dir_url(__FILE__ )."','".$namefile."','".$count."');
        </script>
        ";
        */
        
    }


}
/*
<script src='".plugins_url( 'build/three.min.js',__FILE__ )."'></script>
        <script src='".plugins_url( 'build/OrbitControls.js',__FILE__ )."'></script>
        <script src='".plugins_url( 'test.js',__FILE__ )."'></script>

        <script type='module'>
        
        init('".$div_id."','".$namefile."','".$dist."');
        
        </script>
*/
