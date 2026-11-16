<?php
?>
<link rel="stylesheet" href="../assets/css/analytics-consent.css?v=1">
<script>
(function () {
    'use strict';
    var containerId = 'GTM-WLC45QH4';
    var storageKey = 'catfeteria_consentimiento_analytics_v1';
    var cargado = false;

    window.dataLayer = window.dataLayer || [];
    window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };

    window.gtag('consent', 'default', {
        analytics_storage: 'denied',
        ad_storage: 'denied',
        ad_user_data: 'denied',
        ad_personalization: 'denied'
    });

    function leerPreferencia() {
        try { return localStorage.getItem(storageKey); }
        catch (e) { return null; }
    }

    function guardarPreferencia(valor) {
        try { localStorage.setItem(storageKey, valor); }
        catch (e) { }
    }

    function cargarTagManager() {
        if (cargado) return;
        cargado = true;

        window.gtag('consent', 'update', { analytics_storage: 'granted' });
        window.gtag('js', new Date());

        var script = document.createElement('script');
        script.async = true;
        script.src = 'https://www.googletagmanager.com/gtm.js?id=' + containerId;
        document.head.appendChild(script);
    }

    window.CatfeteriaAnalytics = {
        preferencia: leerPreferencia,
        aceptar: function () {
            guardarPreferencia('aceptado');
            cargarTagManager();
        },
        rechazar: function () {
            guardarPreferencia('rechazado');
            window.gtag('consent', 'update', { analytics_storage: 'denied' });
            
            if (cargado) window.location.reload();
        }
    };

    if (leerPreferencia() === 'aceptado') cargarTagManager();
}());
</script>
