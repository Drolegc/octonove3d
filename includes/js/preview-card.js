export default function(divId, izq_img, cntr_img, dir_img) {

    let canvas = document.getElementById(divId)

    canvas.width = canvas.offsetWidth
    canvas.height = canvas.offsetHeight

    let context = canvas.getContext("2d")
    let width = canvas.width
    let height = canvas.height

    let izq_img_canvas = new Image()
    izq_img_canvas.onload = function() {
        context.drawImage(izq_img_canvas, 0, 0, width, height)
    }
    izq_img_canvas.src = izq_img

    let cntr_img_canvas = new Image()
    cntr_img_canvas.src = cntr_img

    let dir_img_canvas = new Image()
    dir_img_canvas.src = dir_img

    canvas.addEventListener('mousemove', function(e) {
        var x = e.offsetX
        var y = e.offsetY
        var sections_distance = canvas.width / 3
        if (x < sections_distance) {
            context.drawImage(izq_img_canvas, 0, 0, width, height)
        } else if (x > sections_distance && x < sections_distance * 2) {
            context.drawImage(cntr_img_canvas, 0, 0, width, height)
        } else {
            context.drawImage(dir_img_canvas, 0, 0, width, height)

        }
    })
}