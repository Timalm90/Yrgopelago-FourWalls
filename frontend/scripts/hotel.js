function loadSVG() {
    fetch("/frontend/graphics/FourWalls.svg")
        .then(res => res.text())
        .then(svg => {
            document.getElementById('hotel').innerHTML = svg;
        });
}

loadSVG();