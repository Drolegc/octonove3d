export default class {

    constructor(path, nameId, cant) {
        this.path = path
        this.nameId = nameId
        this.cant = cant

        this.getModel();
    }

    init(gltfData) {
        var canvas = document.getElementById(this.nameId + '-canvas');

        var self = this
        var engine = null;
        var scene = null;
        var sceneToRender = null;
        var createDefaultEngine = function() { return new BABYLON.Engine(canvas, true, { preserveDrawingBuffer: true, stencil: true }); };
        var createScene = function() {
            var scene = new BABYLON.Scene(engine)
            BABYLON.SceneLoader.ShowLoadingScreen = false
            document.getElementById(self.nameId + '-loading').style.display = 'none'
            BABYLON.SceneLoader.Append("", "data:" + gltfData, scene, function() {
                    scene.createDefaultCamera(true, true, true)
                    scene.activeCamera.alpha += Math.PI / 2
                },
                function() {
                    console.log("### Progress ...")
                },
                function() {
                    console.error("Error cargando el modelo")
                }
            );

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

    Decrypt(passphrase, encrypted_json_string) {

        var obj_json = encrypted_json_string

        var encrypted = obj_json.m;
        var salt = CryptoJS.enc.Hex.parse(obj_json.salt);
        var iv = CryptoJS.enc.Hex.parse(obj_json.iv);

        var key = CryptoJS.PBKDF2(passphrase, salt, { hasher: CryptoJS.algo.SHA512, keySize: 64 / 8, iterations: 999 });


        var decrypted = CryptoJS.AES.decrypt(encrypted, key, { iv: iv });

        return decrypted.toString(CryptoJS.enc.Utf8);
    }

    async getModel() {

        var model = ""
        var parts = []
        var sumaTotal = 0
        var sumaAuxiliar = 0
        for (let n = 0; n < this.cant; n++) {
            sumaTotal += n
        }

        var self = this
        let hosts = []

        for (let i = 0; i < this.cant; i++) {
            var url = window.location
            var host = url.protocol + "//" + url.host
            host = 'http://localhost/wordpress/wordpress-5.3.2-es_UY/wordpress'
            const request = axios.get(
                host + '/wp-json/octonove3d/v1/model?m=' + this.path + '&p=' + i, {
                    headers: {
                        'Content-Encoding': 'gzip'
                    }
                }
            )

            hosts.push(request)
                // axios.get(
                //         host + '/wp-json/octonove3d/v1/model?m=' + this.path + '&p=' + i, {
                //             headers: {
                //                 'Content-Encoding': 'gzip'
                //             }
                //         }
                //     )
                //     .then((response) => {

            //         parts[i] = atob(self.Decrypt('condiment coach hypnoses doornail', response.data))
            //         sumaAuxiliar += i
            //         if (sumaAuxiliar == sumaTotal) {
            //             self.init(parts.join(''))
            //         }
            //     })
            //     .catch((error) => console.error(error))

        }

        axios.all(hosts)
            .then(axios.spread((...responses) => {
                console.log("Empezando")
                for (let i = 0; i < responses.length; i++) {
                    parts[i] = atob(self.Decrypt('condiment coach hypnoses doornail', responses[i].data))
                }
                self.init(parts.join(''))
            }))
            .catch((error) => console.error(error))

    }



}