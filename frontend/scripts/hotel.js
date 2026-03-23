//HellO!!
// ==================================================
// SVG 
// ==================================================
function getSVG() {
    return document.querySelector('#hotel svg');
}

// SVG GROUP MAPPINGS
const ROOM_GROUPS = {
    economy: 'Budget',
    standard: 'Standard',
    luxury: 'Luxury'
};

const EXTRA_GROUPS = {
    carpet: 'Carpet',
    'good dog': 'Dog',
    fireplace: 'Fireplace',
    butler: 'Butler'
};

// ==================================================
// Hide all rooms
// ==================================================
function hideRoomSVG() {
    const svg = getSVG();
    if (!svg) return;

    Object.values(ROOM_GROUPS).forEach(id => {
        const g = svg.getElementById(id);
        if (g) g.style.display = 'none';
    });
}

function hideExtraSVG() {
    const svg = getSVG();
    if (!svg) return;

    Object.values(EXTRA_GROUPS).forEach(prefix => {
        svg.querySelectorAll(`[id^="${prefix}"]`)
            .forEach(g => g.style.display = 'none');
    });
}

function showRoomSVG(tier) {
    hideRoomSVG();
    const svg = getSVG();
    if (!svg) return;

    const id = ROOM_GROUPS[tier];
    if (!id) return;

    const g = svg.getElementById(id);
    if (g) g.style.display = 'block';
}

function showExtraSVG(selectedExtras) {
    hideExtraSVG();
    const svg = getSVG();
    if (!svg) return;

    selectedExtras.forEach(name => {
        const prefix = EXTRA_GROUPS[name];
        if (!prefix) return;

        svg.querySelectorAll(`[id^="${prefix}"]`)
            .forEach(g => g.style.display = 'block');
    });
}

// ==================================================
// LOAD SVG
// ==================================================
fetch('frontend/graphics/FourWalls.svg')
    .then(r => r.text())
    .then(svg => {
        document.getElementById('hotel').innerHTML = svg;
        hideRoomSVG();
        hideExtraSVG();
        restoreState();
    });

// ==================================================
// ROOM SELECTION
// ==================================================
document.querySelectorAll('[name="room"]').forEach(radio => {
    radio.addEventListener('change', () => {
        roomPrice = parseFloat(radio.dataset.roomPrice) || 0;

        showRoomSVG(radio.dataset.roomTier);

        const selectedExtras = Array.from(
            document.querySelectorAll('[name="features[]"]:checked')
        ).map(f => f.dataset.featureName);

        showExtraSVG(selectedExtras);

        document.getElementById('roomDescription').textContent =
            ROOM_DESCRIPTIONS[radio.dataset.roomTier] ?? '';

        updateCalendar(radio.value);
        updatePrice();
    });
});

// ==================================================
// FEATURES
// ==================================================
document.querySelectorAll('[name="features[]"]').forEach(box => {
    box.addEventListener('change', () => {
        const selectedExtras = Array.from(
            document.querySelectorAll('[name="features[]"]:checked')
        ).map(f => f.dataset.featureName);

        showExtraSVG(selectedExtras);
        updatePrice();
    });
});


//Please be nice to me!