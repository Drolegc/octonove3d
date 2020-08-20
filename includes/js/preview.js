const fileSelector = document.getElementById('upload_json')

var data_model = ""

fileSelector.addEventListener('change', (event) => {
    document.getElementById('upload_btn').disabled = true
    document.getElementById('upload_btn').value = "Cargando"
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
        //camera = new BABYLON.ArcRotateCamera("Camera", Math.PI / 2, 0, 0, BABYLON.Vector3.Zero(), scene);
        //camera.setPosition(new BABYLON.Vector3(0, 0.5, -4));

        //camera.attachControl(canvas, false);

        BABYLON.SceneLoader.ShowLoadingScreen = false
        BABYLON.SceneLoader.Append("", "data:" + gltfData, scene,
            function() {
                scene.createDefaultCamera(true, true, false);
            },
            function() {
                console.log("### Loading 3d models")
            },
            function(error, message, exception) {
                console.error(message)
                console.log(exception)
            })



        scene.executeWhenReady(function() {
            setTimeout(function() {
                BABYLON.Tools.CreateScreenshotUsingRenderTarget(engine, scene.activeCamera, { width: 500, height: 300 }, function(img) {
                    document.getElementById('dir_img').value = img
                    document.getElementById('upload_btn').value = "Cargando."
                })
            }, 2 * 1000)
            setTimeout(function() {
                BABYLON.Tools.CreateScreenshotUsingRenderTarget(engine, scene.activeCamera, { width: 500, height: 300 }, function(img) {
                    document.getElementById('cntr_img').value = img
                    document.getElementById('upload_btn').value = "Cargando.."
                })
            }, 7 * 1000)
            setTimeout(function() {
                BABYLON.Tools.CreateScreenshotUsingRenderTarget(engine, scene.activeCamera, { width: 500, height: 300 }, function(img) {
                    document.getElementById('izq_img').value = img
                    document.getElementById('upload_btn').value = "Upload"
                    document.getElementById('upload_btn').disabled = false

                })
            }, 12 * 1000)
        })

        scene.registerBeforeRender(function() {

            scene.activeCamera.alpha += 0.005
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

function dataURLtoFile(dataurl, filename) {

    var arr = dataurl.split(','),
        mime = arr[0].match(/:(.*?);/)[1],
        bstr = atob(arr[1]),
        n = bstr.length,
        u8arr = new Uint8Array(n);

    while (n--) {
        u8arr[n] = bstr.charCodeAt(n);
    }

    return new File([u8arr], filename, { type: mime });
}