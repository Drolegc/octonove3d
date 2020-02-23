Como usar este plugin

Para subir modelos:

Ejemplo: split Low_Poly1.json abdcradkeoa4569 -b 512k -d

Con el modelo exportado en formato .json, aplicar el siguiente commando

( Este es un comando linux, si se tiene instalado git bash en windows se puede usar el equivalente )
split name_file.json nombre_random_alfanumerico -b 512k -d

Este comando generara los siguientes archivos ( com ejemplo )
nombre_random_alfanumerico00
nombre_random_alfanumerico01
nombre_random_alfanumerico02
nombre_random_alfanumerico03
nombre_random_alfanumerico04
nombre_random_alfanumerico05
...
nombre_random_alfanumerico22

seleccionar todos los archivos y llevar a la carpeta gqx5gSX5Td ( cambiar a galeria proximamente )

Para usar:

Aplicar el shortcode

Deben estar todos los parametros, de lo contrario, se mostrara "Error"

[threejs_octonove name=(nombre del modelo ej=modelo_auto) id=(identificador unico) dist=(distancia del zoom, ej = 5) namefile=(nombre del archivo el cual esta ditribuido en varios archivos, ej = nombre_random_alfanumerico) count=(cantidad de archivos donde el modelo esta distribuido, ej=22)]

