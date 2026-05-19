(function () {
    'use strict';

    var script = document.currentScript;
    var apiBase = (script && script.getAttribute('data-api')) || '';

    if (!apiBase) {
        var origin = script && script.src ? new URL(script.src).origin : window.location.origin;
        apiBase = origin;
    }

    apiBase = apiBase.replace(/\/$/, '');

    var payload = {
        page_url: window.location.href,
        referrer: document.referrer || null,
        screen_width: window.screen && window.screen.width,
        screen_height: window.screen && window.screen.height,
        language: navigator.language || null,
        timezone: (function () {
            try {
                return Intl.DateTimeFormat().resolvedOptions().timeZone;
            } catch (e) {
                return null;
            }
        })(),
        user_agent: navigator.userAgent,
    };

    fetch(apiBase + '/api/track', {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
        },
        body: JSON.stringify(payload),
        keepalive: true,
    }).catch(function () {
    });
})();
