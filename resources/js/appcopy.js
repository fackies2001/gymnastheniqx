// ============================================
// ✅ STEP 1: Import jQuery FIRST (BEFORE EVERYTHING!)
// ============================================
/*
import jQuery from 'jquery';

// ============================================
// ✅ STEP 2: Make jQuery available globally
// ============================================
window.jQuery = window.$ = jQuery;

// ============================================
// ✅ STEP 3: Import Bootstrap 4 (needs jQuery)
// ============================================
  import 'bootstrap';

// ============================================
// ✅ STEP 4: Import DataTables (needs jQuery & Bootstrap)
// ============================================
 import 'datatables.net-bs4';
 import 'datatables.net-bs4/css/dataTables.bootstrap4.css';
 import 'datatables.net-responsive-bs4';
 import 'datatables.net-responsive-bs4/css/responsive.bootstrap4.css';

// ============================================
// ✅ STEP 5: Import Axios & Laravel Echo
// ============================================
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import './echo';

// ============================================
// ✅ STEP 6: Verify everything is loaded
// ============================================
console.log('✅ jQuery loaded:', typeof $ !== 'undefined');
console.log('✅ jQuery version:', $.fn.jquery);
console.log('✅ Bootstrap available:', typeof $.fn.modal !== 'undefined');
console.log('✅ DataTables available:', typeof $.fn.DataTable !== 'undefined');

/**
 * Notification Bell Update Logic
 */
/*
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

feb 11