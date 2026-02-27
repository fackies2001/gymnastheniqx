import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// ✅ Wrap sa try-catch para hindi mag-block ng ibang JS
try {
    const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;
    
    if (!pusherKey) {
        console.warn('⚠️ Pusher key not set — Echo disabled.');
    } else {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: pusherKey,
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
            forceTLS: true
        });
        console.log('✅ Echo initialized');
    }
} catch(e) {
    console.warn('⚠️ Echo init failed:', e.message);
}