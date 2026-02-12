// ============================================
// ✅ STEP 1: Use vendor jQuery (already loaded by AdminLTE)
//    DO NOT import jQuery from npm — it creates a new instance
//    that REPLACES the vendor jQuery which Bootstrap is attached to!
// ============================================
// Vendor scripts (jquery.min.js + bootstrap.bundle.min.js) load BEFORE
// this Vite script. So window.jQuery and window.$ already exist with
// Bootstrap, AdminLTE, and all plugins attached.
// We just ensure $ is available globally.
window.$ = window.$ || window.jQuery;
window.jQuery = window.jQuery || window.$;

// ============================================
// ✅ STEP 2: Import Axios & Laravel Echo
// ============================================
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import './echo';

// ============================================
// ✅ STEP 3: Verify everything is loaded
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    console.log('✅ jQuery loaded:', typeof $ !== 'undefined');
    console.log('✅ jQuery version:', $.fn.jquery);
    console.log('✅ Bootstrap available:', typeof $.fn.modal !== 'undefined');
    console.log('✅ DataTables available:', typeof $.fn.DataTable !== 'undefined');
});

// ============================================
// ✅ STEP 4: Notification Bell Update Logic
// ============================================
(function() {
    'use strict';

    const userId = window.Laravel?.userId;

    async function updateNotificationBell() {
        try {
            const response = await fetch('/notifications/get');

            const contentType = response.headers.get('content-type');
            if (!response.ok || !contentType?.includes('application/json')) {
                return;
            }

            const data = await response.json();

            const badge = document.querySelector('#my-notification .navbar-badge');
            if (badge && data.label !== undefined) {
                badge.innerText = data.label;
            }

            const dropdown = document.querySelector('#my-notification .adminlte-dropdown-content');
            if (dropdown && data.dropdown !== undefined) {
                dropdown.innerHTML = data.dropdown;
            }
        } catch (err) {
            console.warn('🔕 Notification fetch skipped:', err.message);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const notificationWrapper = document.querySelector('#my-notification');

        if (notificationWrapper) {
            updateNotificationBell();

            if (typeof window.Echo !== 'undefined' && userId) {
                window.Echo.private(`App.Models.User.${userId}`)
                    .notification((notification) => {
                        console.log('🔔 Notification received.');
                        updateNotificationBell();
                    });
            }
        }
    });
})();