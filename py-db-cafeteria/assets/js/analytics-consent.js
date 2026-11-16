(function () {
    'use strict';

    var panel = document.getElementById('analyticsConsentPanel');
    var preferencias = document.getElementById('analyticsPreferencias');
    var aceptar = document.getElementById('analyticsAceptar');
    var rechazar = document.getElementById('analyticsRechazar');
    var analytics = window.CatfeteriaAnalytics;

    if (!panel || !preferencias || !aceptar || !rechazar || !analytics) return;

    if (analytics.preferencia() === null) panel.hidden = false;

    preferencias.addEventListener('click', function () {
        panel.hidden = false;
        aceptar.focus();
    });

    aceptar.addEventListener('click', function () {
        analytics.aceptar();
        panel.hidden = true;
    });

    rechazar.addEventListener('click', function () {
        analytics.rechazar();
        panel.hidden = true;
    });
}());
