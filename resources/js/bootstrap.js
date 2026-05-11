import axios from 'axios';
window.axios = axios;


window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Set CSRF token for all Axios requests
const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Block browser-logs and monitoring requests before they start
(function() {
  // Safely disable flushLogs without causing errors if it's read-only
  try {
    Object.defineProperty(window, 'flushLogs', {
      value: () => Promise.resolve(),
      writable: false,
      configurable: true
    });
  } catch (e) {
    // If flushLogs is read-only, just skip it
  }
  
  try {
    Object.defineProperty(window, 'scheduleFlush', {
      value: () => {},
      writable: false,
      configurable: true
    });
  } catch (e) {
    // If scheduleFlush is read-only, just skip it
  }
  
  // Intercept fetch globally
  const originalFetch = window.fetch;
  window.fetch = function(...args) {
    const url = (typeof args[0] === 'string') ? args[0] : (args[0]?.url || '');
    if (url.includes('_boost') || url.includes('browser-logs') || url.includes('pulse')) {
      return Promise.resolve(new Response('blocked', { status: 200 }));
    }
    return originalFetch.apply(this, args);
  };
  
  // Also intercept XMLHttpRequest
  const xhrOpen = XMLHttpRequest.prototype.open;
  const xhrSend = XMLHttpRequest.prototype.send;
  XMLHttpRequest.prototype.open = function(method, url, ...args) {
    if (url.includes('_boost') || url.includes('browser-logs') || url.includes('pulse')) {
      this._blocked = true;
      return;
    }
    return xhrOpen.apply(this, [method, url, ...args]);
  };
  
  XMLHttpRequest.prototype.send = function(data) {
    if (this._blocked) return;
    return xhrSend.call(this, data);
  };
})();

// ========== PUSHER/LARAVEL ECHO - REAL-TIME BROADCASTING ==========
// Configured for real-time inventory updates and event broadcasting
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;
const pusherCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER;
const host = window.location.hostname;
const isLocalhost = ['localhost', '127.0.0.1', '::1'].includes(host);
const allowLocalEcho = String(import.meta.env.VITE_ENABLE_ECHO_LOCAL || 'false').toLowerCase() === 'true';
const shouldInitEcho = !!pusherKey && !!pusherCluster && (!isLocalhost || allowLocalEcho);

if (shouldInitEcho) {
    try {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: pusherKey,
            cluster: pusherCluster,
            forceTLS: true,
            enabledTransports: ['ws', 'wss'],
        });
    } catch (error) {
        console.warn('Echo initialization skipped:', error);
        window.Echo = null;
    }
} else {
    window.Echo = null;
}
