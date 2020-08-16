export default function(divId, izq_img, cntr_img, dir_img) {

    var div = document.getElementById(divId)
    var children = div.childNodes
    div.style.backgroundImage = "url(" + cntr_img + ")"

    div.addEventListener('mouseleave', function(e) {
        div.style.backgroundImage = "url(" + cntr_img + ")"
    })

    for (let i = 0; i < children.length; i++) {
        children[i].addEventListener("mouseover", function(event) {
            console.log(i)
            switch (i) {
                case 1:
                    div.style.backgroundImage = "url(" + izq_img + ")"
                    break;
                case 3:
                    div.style.backgroundImage = "url(" + cntr_img + ")"
                    break;
                case 5:
                    div.style.backgroundImage = "url(" + dir_img + ")"
                    break;
                default:
                    console.error("Error cargando preview_card")
            }
        })
    }
}