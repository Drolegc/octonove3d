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