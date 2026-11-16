'use strict';

(() => {
    const destino = window.UBICACION_CAFETERIA;
    const botonCentrar = document.getElementById('centrarMapa');
    const estado = document.getElementById('estadoMapa');

    if (!destino || !window.L || !document.getElementById('mapaUbicacion')) {
        if (estado) {
            estado.hidden = false;
            estado.textContent = 'No se pudo cargar el mapa. Comprueba tu conexión a Internet.';
        }
        if (botonCentrar) botonCentrar.disabled = true;
        return;
    }

    const puntoIadaIit = L.latLng(destino.lat, destino.lng);
    const zoomInicial = 16;
    const mapa = L.map('mapaUbicacion', { scrollWheelZoom: false })
        .setView(puntoIadaIit, zoomInicial);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a>'
    }).addTo(mapa);

    const marcador = L.circleMarker(puntoIadaIit, {
        radius: 11,
        color: '#426938',
        weight: 3,
        fillColor: '#4C8D60',
        fillOpacity: 1
    }).addTo(mapa).bindPopup('<strong>Catfetería</strong><br>UACJ · IADA/IIT');

    marcador.openPopup();

    botonCentrar?.addEventListener('click', () => {
        mapa.setView(puntoIadaIit, zoomInicial, { animate: true });
        marcador.openPopup();
    });
})();
