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
        
        if (isset($atts['name']) && isset($atts['id']) && isset($atts['namefile']) && isset($atts['count']) && isset($atts['dist'])) {//Chequeo si existen los parametros y no son nulos
            
            return
            "
            <div id='". $atts['name']. '-'. $atts['id']. "' class='model-canvas'></div>
            <script type='module'>
            import init from '".plugins_url( 'includes/js/main.js',__FILE__ )."';
            
            new init('". $atts['name']. '-'. $atts['id']. "','".$atts['dist']."','".plugin_dir_url(__FILE__ )."','". $atts['namefile']."','". $atts['count']."');
            </script>
            ";
        }
        else
            echo 'Error';
    }
}

