// Afriserva Real Estate: Interactive Map Module (Leaflet / OpenStreetMap)

export function initPropertyMap(containerId = 'map-container', apiUrl = '/api/properties/coordinates') {
    const mapElement = document.getElementById(containerId);
    if (!mapElement) return;

    // Default coordinates: Victoria Island / Lagos (6.4549, 3.4246)
    const map = L.map(containerId).setView([6.4549, 3.4246], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors | Afriserva Real Estate (afriserva.com)',
        maxZoom: 19
    }).addTo(map);

    const markersGroup = L.featureGroup().addTo(map);

    function loadProperties(queryParams = '') {
        const fetchUrl = apiUrl + (queryParams ? '?' + queryParams : '');

        fetch(fetchUrl)
            .then(res => res.json())
            .then(data => {
                markersGroup.clearLayers();
                if (!data.data || !data.data.length) return;

                const bounds = [];

                data.data.forEach(prop => {
                    if (!prop.latitude || !prop.longitude) return;

                    const lat = parseFloat(prop.latitude);
                    const lng = parseFloat(prop.longitude);
                    bounds.push([lat, lng]);

                    const popupContent = `
                        <div style="width: 220px; font-family: sans-serif;">
                            <img src="${prop.cover_image || 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=400&q=80'}" 
                                 style="width: 100%; height: 110px; object-fit: cover; border-radius: 6px; margin-bottom: 8px;">
                            <div style="font-size: 11px; text-transform: uppercase; font-weight: bold; color: #D4AF37;">${prop.transaction_type}</div>
                            <h4 style="font-size: 13px; margin: 4px 0; line-height: 1.3; font-weight: 600;">${prop.title}</h4>
                            <div style="font-size: 14px; font-weight: bold; color: #0B192C; margin-bottom: 8px;">₦${Number(prop.price).toLocaleString()}</div>
                            <a href="/properties/${prop.slug}" style="display: block; background: #0B192C; color: #fff; text-align: center; padding: 6px; border-radius: 4px; font-size: 12px; font-weight: 600; text-decoration: none;">View Property</a>
                        </div>
                    `;

                    const marker = L.marker([lat, lng]).bindPopup(popupContent);
                    markersGroup.addLayer(marker);
                });

                if (bounds.length > 0) {
                    map.fitBounds(bounds, { padding: [40, 40] });
                }
            })
            .catch(err => console.error("Map data fetch error:", err));
    }

    loadProperties();

    return {
        map,
        reload: loadProperties
    };
}
