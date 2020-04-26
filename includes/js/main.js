import * as THREE from '../build/three.module.js';
import { OrbitControls } from '../build/OrbitControls.js';

export default class{

    constructor(name,dist,path,size){

        this.name = name;
        this.dist = dist;
        this.path = path;
        this.size = size;

        this.loadFileFirst(size).then((file)=> this.initCanvas(file));
    }

    initCanvas(jsonfile){

        this.container = document.getElementById(this.name);

        var height = this.container.getBoundingClientRect().height;
        var width = this.container.getBoundingClientRect().width;

        this.camera = new THREE.PerspectiveCamera(45, width / height, 1, 2000);
        this.camera.position.z = this.dist;

        this.scene = new THREE.Scene();

        var ambient = new THREE.AmbientLight(0x444444);
        this.scene.add(ambient);

        var directionalLight = new THREE.DirectionalLight(0xffeedd);
        directionalLight.position.set(0, 0, 1).normalize();
        this.scene.add(directionalLight);

        var objectLoader = new THREE.ObjectLoader();

        this.loader(this.scene,objectLoader,jsonfile);

        var canvas = document.createElement('canvas');
        canvas.width = this.container.offsetWidth;
        canvas.height = this.container.offsetHeight + 50;

        this.renderer = new THREE.WebGLRenderer( { alpha:true,canvas:canvas });
        this.renderer.setSize(canvas.width,canvas.height);
        this.container.appendChild(canvas);

        this.control = new OrbitControls(this.camera, this.renderer.domElement);
        this.control.autoRotate = true;

        var self = this;
        window.addEventListener('resize', () => {
            self.onWindowResize();
        }, false);
        this.onWindowResize();
    
        this.animate();
    }
    
    onWindowResize() {
        this.camera.aspect = this.container.offsetWidth / this.container.offsetHeight;
        this.camera.updateProjectionMatrix();
    
        this.renderer.setSize(this.container.offsetWidth, this.container.offsetHeight);
    }

    animate() {

        var self = this;
        requestAnimationFrame(() => {
            self.animate();
        });
        this.control.update();
        this.render();
    
    }

    render() {

        var scene = this.scene;
        var camera = this.camera;

        this.renderer.render(scene,camera)
    
    }

    loader(scene,objectLoader,str){
        var j = JSON.parse(str);
        var camera = this.camera;
        objectLoader.parse(j, function (obj) {
            camera.lookAt(obj.position)
            scene.add(obj);

        });
    }

    async loadFileFirst() {

        var resp = "";

        for(var i=0;i<this.size;i++){
            var response = await axios.get(this.path + "00" + i);
            resp += response.data;
        }
    
        return resp;
    
    }
}