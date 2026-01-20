/**
 * Best-effort "disable inspect" deterrent.
 * Note: This cannot fully prevent DevTools; it only blocks common shortcuts.
 */
(function () {
    'use strict';

    // Prevent double-init if included twice
    if (window.__BLOG_DISABLE_INSPECT_INITIALIZED__) return;
    window.__BLOG_DISABLE_INSPECT_INITIALIZED__ = true;

    // ---- DevTools detection (best-effort) ----
    const ENABLE_POPUP_ALERT = true; // set false if you only want console warning
    const DEVTOOLS_THRESHOLD_PX = 160; // typical docked DevTools size
    const DEVTOOLS_ALERT_KEY = '__BLOG_DEVTOOLS_ALERTED__';
    let lastDevtoolsOpen = false;

    function isDevtoolsOpenBySize() {
        // Works best when DevTools is docked
        const widthDiff = Math.abs((window.outerWidth || 0) - (window.innerWidth || 0));
        const heightDiff = Math.abs((window.outerHeight || 0) - (window.innerHeight || 0));
        return widthDiff > DEVTOOLS_THRESHOLD_PX || heightDiff > DEVTOOLS_THRESHOLD_PX;
    }

    function isDevtoolsOpenByConsole() {
        // Works when DevTools is open (often even if not docked).
        // It logs a harmless object once; when DevTools is open, console rendering may access properties.
        let detected = false;
        try {
            const probe = new Image();
            Object.defineProperty(probe, 'id', {
                get: function () {
                    detected = true;
                    return '';
                }
            });
            // Using debug to reduce noise. This will still print one line.
            console.debug(probe);
        } catch (e) {
            // ignore
        }
        return detected;
    }

    function warnDevtoolsOpenOnce() {
        try {
            if (sessionStorage.getItem(DEVTOOLS_ALERT_KEY) === '1') return;
            sessionStorage.setItem(DEVTOOLS_ALERT_KEY, '1');
        } catch (e) {
            // ignore storage errors
        }

        // Console warning (what you asked for)
        console.warn('[ALERT] DevTools / Inspect terdeteksi aktif.');
        console.warn('[ALERT] Harap jangan melakukan inspect / view source pada website ini.');

        // Optional popup alert once per tab/session
        if (ENABLE_POPUP_ALERT) {
            try {
                alert('DevTools / Inspect terdeteksi aktif. Harap jangan melakukan inspect pada website ini.');
            } catch (e) {
                // ignore if blocked
            }
        }
    }

    function checkDevtools() {
        // Combine multiple heuristics for better detection
        const open = isDevtoolsOpenBySize() || isDevtoolsOpenByConsole();
        if (open && !lastDevtoolsOpen) {
            warnDevtoolsOpenOnce();
        }
        lastDevtoolsOpen = open;
    }

    // Initial + periodic checks (covers "Disable JS" -> enable again -> DevTools already open)
    setTimeout(checkDevtools, 800);
    window.addEventListener('resize', checkDevtools, { passive: true });
    setInterval(checkDevtools, 900);

    function isBlockedDevtoolsShortcut(e) {
        const key = String(e.key || '').toLowerCase();
        const code = String(e.code || '').toLowerCase();
        const keyCode = Number(e.keyCode || 0);

        const ctrlOrMeta = Boolean(e.ctrlKey || e.metaKey);
        const shift = Boolean(e.shiftKey);

        // F12
        if (key === 'f12' || code === 'f12' || keyCode === 123) return true;

        // Ctrl+Shift+I / J / C (Chrome/Edge)
        if (ctrlOrMeta && shift) {
            if (key === 'i' || keyCode === 73) return true;
            if (key === 'j' || keyCode === 74) return true;
            if (key === 'c' || keyCode === 67) return true;
            // Ctrl+Shift+K (Firefox devtools)
            if (key === 'k' || keyCode === 75) return true;
        }

        // Ctrl+U (view source)
        if (ctrlOrMeta && !shift && (key === 'u' || keyCode === 85)) return true;

        return false;
    }

    // Disable right click context menu
    document.addEventListener(
        'contextmenu',
        function (e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        },
        true
    );

    // Block common devtools shortcuts
    document.addEventListener(
        'keydown',
        function (e) {
            if (!e) return;
            if (!isBlockedDevtoolsShortcut(e)) return;

            e.preventDefault();
            e.stopPropagation();
            return false;
        },
        true
    );
})();

