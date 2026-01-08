// ==================================================
// SVG
// ==================================================
function getSVG() {
    return document.querySelector('#hotel svg');
}

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

// ----------------------
// Hide all rooms
// ----------------------
function hideRoomSVG() {
    const svg = getSVG();
    if (!svg) return;
    Object.values(ROOM_GROUPS).forEach(id => {
        const g = svg.getElementById(id);
        if (g) g.style.display = 'none';
    });
}

// ----------------------
// Hide all extras
// ----------------------
function hideExtraSVG() {
    const svg = getSVG();
    if (!svg) return;
    Object.values(EXTRA_GROUPS).forEach(prefix => {
        svg.querySelectorAll(`[id^="${prefix}"]`).forEach(g => g.style.display = 'none');
    });
}

// ----------------------
// Show selected room
// ----------------------
function showRoomSVG(tier) {
    hideRoomSVG();
    const svg = getSVG();
    if (!svg) return;
    const id = ROOM_GROUPS[tier];
    if (id) {
        const g = svg.getElementById(id);
        if (g) g.style.display = 'block';
    }
}

// ----------------------
// Show selected extras
// ----------------------
function showExtraSVG(selectedExtras) {
    hideExtraSVG();
    const svg = getSVG();
    if (!svg) return;

    selectedExtras.forEach(name => {
        const prefix = EXTRA_GROUPS[name];
        if (!prefix) return;
        svg.querySelectorAll(`[id^="${prefix}"]`).forEach(g => g.style.display = 'block');
    });
}

// ==================================================
// LOAD SVG
// ==================================================
fetch('/frontend/graphics/FourWalls.svg')
    .then(r => r.text())
    .then(svg => {
        document.getElementById('hotel').innerHTML = svg;
        hideRoomSVG();
        hideExtraSVG();
        restoreState(); // ✅ IMPORTANT
    });

// ==================================================
// PRICE
// ==================================================
let roomPrice = 0;
let returning = false;

function nights() {
    const a = document.querySelector('[name="arrival"]').value;
    const d = document.querySelector('[name="departure"]').value;
    if (!a || !d) return 1;
    return Math.max(1, (new Date(d) - new Date(a)) / 86400000);
}

function updatePrice() {
    let total = roomPrice * nights();

    document.querySelectorAll('[name="features[]"]:checked')
        .forEach(f => total += parseFloat(f.dataset.price));

    const discount = returning ? total * 0.1 : 0;
    total -= discount;

    document.getElementById('livePrice').textContent =
        `$${total.toFixed(2)}` + (returning ? ' (10% returning guest)' : '');
}

// ==================================================
// ROOM SELECTION
// ==================================================
document.querySelectorAll('[name="room"]').forEach(r => {
    r.addEventListener('change', () => {
        roomPrice = parseFloat(r.dataset.roomPrice);
        showRoomSVG(r.dataset.roomTier);

        const selectedExtras = Array.from(
            document.querySelectorAll('[name="features[]"]:checked')
        ).map(f => f.dataset.featureName);

        showExtraSVG(selectedExtras);

        document.getElementById('roomDescription').textContent =
            ROOM_DESCRIPTIONS[r.dataset.roomTier] ?? '';

        updateCalendar(r.value);
        updatePrice();
    });
});

// ==================================================
// FEATURES
// ==================================================
document.querySelectorAll('[name="features[]"]').forEach(f => {
    f.addEventListener('change', () => {
        const selectedExtras = Array.from(
            document.querySelectorAll('[name="features[]"]:checked')
        ).map(f => f.dataset.featureName);

        showExtraSVG(selectedExtras);
        updatePrice();
    });
});

// ==================================================
// AUTO DEPARTURE (Set to 1 Night by default)
// ==================================================
document.querySelector('[name="arrival"]').addEventListener('change', e => {
    const d = new Date(e.target.value);
    d.setDate(d.getDate() + 1);
    document.querySelector('[name="departure"]').value = d.toISOString().slice(0, 10);
    updatePrice();
});

document.querySelector('[name="departure"]').addEventListener('change', updatePrice);

// ==================================================
// CALENDAR
// ==================================================
const calendarGrid = document.getElementById('calendarGrid');
const YEAR = 2026;
const MONTH = 0;

function renderCalendar(blockedDates = []) {
    calendarGrid.innerHTML = '';

    const firstDay = new Date(YEAR, MONTH, 1);
    const lastDay = new Date(YEAR, MONTH + 1, 0);
    const offset = (firstDay.getDay() + 6) % 7;

    for (let i = 0; i < offset; i++) {
        const empty = document.createElement('div');
        empty.className = 'calendar-day empty';
        calendarGrid.appendChild(empty);
    }

    for (let day = 1; day <= lastDay.getDate(); day++) {
        const dateStr = `${YEAR}-01-${String(day).padStart(2, '0')}`;
        const el = document.createElement('div');
        el.className = 'calendar-day';
        el.textContent = day;

        if (blockedDates.includes(dateStr)) el.classList.add('unavailable');
        calendarGrid.appendChild(el);
    }
}

async function updateCalendar(roomId) {
    try {
        const r = await fetch(`/backend/availability.php?room_id=${roomId}`);
        renderCalendar(await r.json());
    } catch {
        renderCalendar([]);
    }
}

renderCalendar([]);

// ==================================================
// RESTORE SESSION STATE 
// ==================================================
function restoreState() {
    const room = document.querySelector('[name="room"]:checked');
    if (room) {
        roomPrice = parseFloat(room.dataset.roomPrice);
        showRoomSVG(room.dataset.roomTier);
        updateCalendar(room.value);

        document.getElementById('roomDescription').textContent =
            ROOM_DESCRIPTIONS[room.dataset.roomTier] ?? '';
    }

    const selectedExtras = Array.from(
        document.querySelectorAll('[name="features[]"]:checked')
    ).map(f => f.dataset.featureName);

    showExtraSVG(selectedExtras);
    updatePrice();
}
