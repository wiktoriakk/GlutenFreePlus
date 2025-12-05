// ==================== DISCOVER PAGE ====================

let map;
let markers = [];
let places = [];

document.addEventListener('DOMContentLoaded', function() {
    initMap();
    loadPlaces();
    initFilters();
    initMobileDrawer();
});

// ==================== MAP INITIALIZATION ====================

function initMap() {
    // Center on Milan, Italy
    map = L.map('map').setView([45.4642, 9.1900], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    // Custom marker icon
    const customIcon = L.divIcon({
        className: 'custom-marker',
        html: `<div style="background: #1B5E20; color: white; width: 32px; height: 32px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);">
            <svg style="transform: rotate(45deg); width: 16px; height: 16px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
            </svg>
        </div>`,
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });

    window.customIcon = customIcon;
}

// ==================== LOAD PLACES ====================

async function loadPlaces(type = 'all', certified = false) {
    try {
        let url = '/discover/places?';
        if (type !== 'all') url += `type=${type}&`;
        if (certified) url += `certified=true&`;

        const response = await fetch(url);
        const data = await response.json();

        if (data.success && data.places) {
            places = data.places;
            displayPlaces(places);
            addMarkersToMap(places);
            updatePlacesCount(places.length);
        }
    } catch (error) {
        console.error('Error loading places:', error);
    }
}

// ==================== DISPLAY PLACES ====================

function displayPlaces(places) {
    const desktopList = document.getElementById('places-list');
    const mobileList = document.getElementById('mobile-places-list');

    const html = places.map(place => createPlaceCard(place)).join('');
    
    desktopList.innerHTML = html;
    mobileList.innerHTML = html;
}

function createPlaceCard(place) {
    const stars = '★'.repeat(Math.floor(place.rating)) + '☆'.repeat(5 - Math.floor(place.rating));
    
    return `
        <div class="place-card" data-id="${place.id}" onclick="selectPlace(${place.id})">
            <div class="place-card-header">
                <div class="place-image">
                    ${place.image ? 
                        `<img src="${place.image}" alt="${escapeHtml(place.name)}" onerror="this.parentElement.innerHTML='<span style=\\'font-size:2rem\\'>🍴</span>'">` :
                        '<span style="font-size:2rem">🍴</span>'
                    }
                </div>
                <div class="place-info">
                    <div class="place-name">${escapeHtml(place.name)}</div>
                    <div class="place-cuisine">${escapeHtml(place.cuisine)}</div>
                    <div class="place-rating">
                        <span class="stars">${stars}</span>
                        <span>(${place.reviews} reviews)</span>
                    </div>
                </div>
            </div>
            <div class="place-distance">${place.distance}</div>
        </div>
    `;
}

// ==================== MAP MARKERS ====================

function addMarkersToMap(places) {
    // Clear existing markers
    markers.forEach(marker => map.removeLayer(marker));
    markers = [];

    places.forEach(place => {
        const marker = L.marker([place.lat, place.lng], { 
            icon: window.customIcon 
        }).addTo(map);

        marker.bindPopup(`
            <div style="text-align: center; padding: 0.5rem;">
                <strong style="font-size: 1rem; display: block; margin-bottom: 0.5rem;">${escapeHtml(place.name)}</strong>
                <p style="color: #666; font-size: 0.875rem; margin-bottom: 0.5rem;">${escapeHtml(place.cuisine)}</p>
                <button onclick="selectPlace(${place.id})" style="background: #1B5E20; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;">
                    View Details
                </button>
            </div>
        `);

        marker.on('click', function() {
            selectPlace(place.id);
        });

        markers.push(marker);
    });

    // Fit map to show all markers
    if (markers.length > 0) {
        const group = L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
}

// ==================== FILTERS ====================

function initFilters() {
    const filterBtns = document.querySelectorAll('.map-filters .filter-btn');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filter = this.dataset.filter;
            
            if (filter === 'certified') {
                loadPlaces('all', true);
            } else {
                loadPlaces(filter, false);
            }
        });
    });
}

// ==================== SELECT PLACE ====================

function selectPlace(placeId) {
    // Remove active class from all cards
    document.querySelectorAll('.place-card').forEach(card => {
        card.classList.remove('active');
    });

    // Add active class to selected card
    document.querySelectorAll(`.place-card[data-id="${placeId}"]`).forEach(card => {
        card.classList.add('active');
    });

    // Find place and center map
    const place = places.find(p => p.id === placeId);
    if (place) {
        map.setView([place.lat, place.lng], 15);
        
        // Open corresponding marker popup
        markers.forEach(marker => {
            if (marker.getLatLng().lat === place.lat && marker.getLatLng().lng === place.lng) {
                marker.openPopup();
            }
        });

        // Close mobile drawer
        const drawer = document.getElementById('mobile-drawer');
        drawer.classList.remove('open');
    }
}

// ==================== MOBILE DRAWER ====================

function initMobileDrawer() {
    const drawer = document.getElementById('mobile-drawer');
    const handle = drawer.querySelector('.drawer-handle');

    handle.addEventListener('click', function() {
        drawer.classList.toggle('open');
    });

    // Close drawer when clicking on map
    document.getElementById('map').addEventListener('click', function() {
        drawer.classList.remove('open');
    });
}

// ==================== HELPERS ====================

function updatePlacesCount(count) {
    const countElement = document.getElementById('places-count');
    if (countElement) {
        countElement.textContent = `${count} place${count !== 1 ? 's' : ''} found`;
    }
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}