const fileSelector = document.getElementById('upload_json')

var data_model = ""

fileSelector.addEventListener('change', (event) => {
    leerElArchivoSeleccionado(event)
})

function leerElArchivoSeleccionado(event) {
    const reader = new FileReader()
    reader.onload = (event) => {
        data_model = event.target.result
        init(data_model)
    }
    reader.readAsText(event.target.files[0])
}

function init(gltfData) {
    var canvas = document.getElementById('preview');

    var engine = null
    var scene = null
    var camera = null

    var sceneToRender = null
    var createDefaultEngine = function() { return new BABYLON.Engine(canvas, true, { preserveDrawingBuffer: true, stencil: true }); };

    var createScene = function() {
        var scene = new BABYLON.Scene(engine);
        camera = new BABYLON.ArcRotateCamera("Camera", Math.PI / 2, 0, 0, BABYLON.Vector3.Zero(), scene);
        camera.setPosition(new BABYLON.Vector3(0, 0.5, -4));

        camera.attachControl(canvas, false);

        BABYLON.SceneLoader.ShowLoadingScreen = false
        BABYLON.SceneLoader.Append("", "data:" + gltfData, scene,
            function() {

                scene.clearColor = new BABYLON.Color4

            },
            function() {
                console.log("### Loading 3d models")
            },
            function(error, message, exception) {
                console.error(message)
                console.log(exception)
            })



        scene.executeWhenReady(function() {
            console.log("Scene ready");
            BABYLON.Tools.CreateScreenshotUsingRenderTarget(engine, scene.activeCamera, { width: 500, height: 300 }, function(img) {
                console.log("New img")
                document.getElementById('cntr_img').value = img
                document.getElementById('izq_img').value = img
                document.getElementById('dir_img').value = img
            })
        })

        scene.registerBeforeRender(function() {
            let mesh = scene.getMeshByName('__root__')
            if (mesh) {
                mesh.rotationQuaternion = null
                mesh.rotation.y += 0.005
            }
        })

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