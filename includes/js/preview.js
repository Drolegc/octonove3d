const fileSelector = document.getElementById('upload_json')
const div_model = document.getElementById('new_model')

const preview_click = document.createElement('button')
preview_click.id = 'preview_click'
preview_click.innerHTML = 'Ver modelo'

const preview_div = document.createElement('div')
preview_div.appendChild(document.createElement('canvas'))
preview_div.id = 'preview_div'
preview_div.style.position = 'absolute'
preview_div.style.height = '40vh'
preview_div.style.width = '40vw'
preview_div.style.zIndex = '1'

var data_model = ""

preview_click.addEventListener('click', (event) => {
    if (document.getElementById('preview_div') == null) {
        preview_div.style.top = `${event.clientY}px`
        preview_div.style.left = `${event.clientX}px`
        document.body.appendChild(preview_div)
        init(data_model)

        preview_click.innerHTML = 'Cerrar'
    } else {
        document.body.removeChild(preview_div)
        preview_click.innerHTML = 'Ver modelo'

    }
})

fileSelector.addEventListener('change', (event) => {
    if (document.getElementById('preview_click') == null) {
        div_model.appendChild(preview_click)
        leerElArchivoSeleccionado(event)
    }

})

function leerElArchivoSeleccionado(event) {
    const reader = new FileReader()
    reader.onload = (event) => {
        data_model = event.target.result
    }
    reader.readAsText(event.target.files[0])
}

function init(gltfData) {
    var canvas = document.getElementById('preview_div').firstElementChild;

    var engine = null;
    var scene = null;
    var sceneToRender = null;
    var createDefaultEngine = function() { return new BABYLON.Engine(canvas, true, { preserveDrawingBuffer: true, stencil: true }); };
    var createScene = function() {
        var scene = new BABYLON.Scene(engine);

        BABYLON.SceneLoader.Append("", "data:" + gltfData, scene, function() {
            scene.createDefaultCamera(true, true, true);
            scene.activeCamera.alpha += Math.PI / 2;
            SceneLoader.ShowLoadingScreen = false;
            scene.clearColor = new BABYLON.Color4;

        }, function() {
            console.log("### Loading 3d models")
        }, function() {
            console.error("### Error loading model")
        });

        return scene;
    };

    engine = createDefaultEngine();
    if (!engine) throw 'engine should not be null.';
    scene = createScene();
    sceneToRender = scene

    engine.runRenderLoop(function() {
        if (sceneToRender) {
            sceneToRender.render();
        }
    });

    // Resize
    window.addEventListener("resize", function() {
        engine.resize();
    });
}