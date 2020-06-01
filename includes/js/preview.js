const fileSelector = document.getElementById('upload_json')
const div_model = document.getElementById('new_model')

const preview_click = document.createElement('button')
preview_click.id = 'preview_click'
preview_click.innerHTML = 'Ver modelo'

const preview_div = document.createElement('div')
preview_div.id = 'preview_div'
preview_div.style.position = 'absolute'
preview_div.style.height = '500px'
preview_div.style.width = '400px'
preview_div.style.backgroundColor = 'green'
preview_div.style.zIndex = '1'
preview_div.innerHTML = "example div"

preview_click.addEventListener('click', (event) => {
    console.log("preview_click")
    if (document.getElementById('preview_div') == null) {
        preview_div.style.top = `${event.clientY}px`
        preview_div.style.left = `${event.clientX}px`
        document.body.appendChild(preview_div)
    } else {
        document.body.removeChild(preview_div)
    }
})

fileSelector.addEventListener('change', (event) => {
    console.log("Se cambio el file")
    if (document.getElementById('preview_click') == null)
        div_model.appendChild(preview_click)
})