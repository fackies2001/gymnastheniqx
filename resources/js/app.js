window.$ = window.$ || window.jQuery;
window.jQuery = window.jQuery || window.$;

import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import './echo';

document.addEventListener('DOMContentLoaded', function () {
    console.log('✅ jQuery loaded:', typeof $ !== 'undefined');
    console.log('✅ jQuery version:', $.fn.jquery);
    console.log('✅ Bootstrap available:', typeof $.fn.modal !== 'undefined');
    console.log('✅ DataTables available:', typeof $.fn.DataTable !== 'undefined');
});

(function() {
    'use strict';

    // ✅ Read userId from meta tag instead of window.Laravel
    const userIdMeta = document.querySelector('meta[name="user-id"]');
    const userId = userIdMeta ? userIdMeta.content : null;

    function showToast(message, icon = 'fas fa-bell') {
        const existing = document.getElementById('notif-toast-container');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.id = 'notif-toast-container';
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 99999;
            min-width: 300px;
            max-width: 380px;
        `;
        toast.innerHTML = `
            <div style="
                background: #fff;
                border-left: 4px solid #007bff;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                padding: 14px 16px;
                display: flex;
                align-items: flex-start;
                gap: 12px;
                animation: slideInRight 0.3s ease;
            ">
                <div style="margin-top:2px;">
                    <i class="${icon}" style="color:#007bff; font-size:1.2rem;"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-weight:600; font-size:0.85rem; color:#333; margin-bottom:2px;">
                        New Notification
                    </div>
                    <div style="font-size:0.82rem; color:#555; line-height:1.4;">
                        ${message}
                    </div>
                </div>
                <button onclick="this.closest('#notif-toast-container').remove()"
                    style="background:none; border:none; cursor:pointer; color:#aaa; font-size:1rem; padding:0; margin-top:-2px;">
                    &times;
                </button>
            </div>
            <style>
                @keyframes slideInRight {
                    from { transform: translateX(100%); opacity: 0; }
                    to   { transform: translateX(0);    opacity: 1; }
                }
                @keyframes slideOutRight {
                    from { transform: translateX(0);    opacity: 1; }
                    to   { transform: translateX(100%); opacity: 0; }
                }
            </style>
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            const t = document.getElementById('notif-toast-container');
            if (t) {
                t.querySelector('div').style.animation = 'slideOutRight 0.3s ease forwards';
                setTimeout(() => t.remove(), 300);
            }
        }, 5000);
    }

    function animateBell() {
        const bell = document.querySelector('#my-notification .nav-link i.fas.fa-bell, #my-notification .nav-link i');
        if (!bell) return;

        bell.style.cssText = `
            display: inline-block;
            animation: bellBounce 0.6s ease 3;
        `;

        if (!document.getElementById('bell-bounce-style')) {
            const style = document.createElement('style');
            style.id = 'bell-bounce-style';
            style.textContent = `
                @keyframes bellBounce {
                    0%  { transform: rotate(0deg);   }
                    20% { transform: rotate(-20deg); }
                    40% { transform: rotate(20deg);  }
                    60% { transform: rotate(-15deg); }
                    80% { transform: rotate(10deg);  }
                    100%{ transform: rotate(0deg);   }
                }
            `;
            document.head.appendChild(style);
        }

        setTimeout(() => { bell.style.animation = ''; }, 2000);
    }

    function updateBadge(count) {
        const badge = document.querySelector('#my-notification .navbar-badge');
        if (!badge) return;
        badge.innerText = count > 0 ? count : '';
        badge.style.display = count > 0 ? 'inline-block' : 'none';
    }

    async function updateNotificationBell(showToastFlag = false, toastMessage = '', toastIcon = '') {
        try {
            const response = await fetch('/notifications/get');
            const contentType = response.headers.get('content-type');
            if (!response.ok || !contentType?.includes('application/json')) return;

            const data = await response.json();

            if (data.count !== undefined) updateBadge(data.count);

            const dropdown = document.querySelector('#my-notification .adminlte-dropdown-content');
            if (dropdown && data.dropdown !== undefined) {
                dropdown.innerHTML = data.dropdown;
            }

            if (showToastFlag && toastMessage) {
                showToast(toastMessage, toastIcon);
                animateBell();
            }

        } catch (err) {
            console.warn('🔕 Notification fetch skipped:', err.message);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const notificationWrapper = document.querySelector('#my-notification');
        if (!notificationWrapper) return;

        updateNotificationBell();

        if (typeof window.Echo !== 'undefined' && userId) {
            window.Echo.private(`App.Models.User.${userId}`)
                .notification((notification) => {
                    console.log('🔔 Real-time notification received:', notification);
                    const message = notification.message ?? notification.data?.message ?? 'You have a new notification.';
                    const icon    = notification.icon    ?? notification.data?.icon    ?? 'fas fa-bell';
                    updateNotificationBell(true, message, icon);
                });
        }

        setInterval(() => updateNotificationBell(), 30000);
    });
})();