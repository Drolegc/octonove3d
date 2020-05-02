
export default class{

    constructor(path,nameId){
        this.path = path
        this.nameId = nameId

        axios.get(path).then((data) => this.init(data.data));
    }

    init(gltfData) {
        var canvas = document.getElementById(this.nameId);

        var engine = null;
        var scene = null;
        var sceneToRender = null;
        var createDefaultEngine = function () { return new BABYLON.Engine(canvas, true, { preserveDrawingBuffer: true, stencil: true }); };
        var createScene = function () {
            var scene = new BABYLON.Scene(engine);
            scene.createDefaultCameraOrLight();

            var gltf = JSON.stringify(gltfData);

            BABYLON.SceneLoader.Append("", "data:" + gltf, scene, function () {
                scene.createDefaultCamera(true, true, true);
            });

            return scene;
        };

        engine = createDefaultEngine();
        if (!engine) throw 'engine should not be null.';
        scene = createScene();
        sceneToRender = scene

        engine.runRenderLoop(function () {
            if (sceneToRender) {
                sceneToRender.render();
            }
        });

        // Resize
        window.addEventListener("resize", function () {
            engine.resize();
        });
    }

}