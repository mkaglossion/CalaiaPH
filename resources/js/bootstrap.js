import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

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
  XMLHttpRequest.prototype.open = function(method, url, ...args) {
    if (url.includes('_boost') || url.includes('browser-logs') || url.includes('pulse')) {
      this._blocked = true;
      return;
    }
    return xhrOpen.apply(this, [method, url, ...args]);
  };
  
  XMLHttpRequest.prototype.send = function(data) {
    if (this._blocked) return Promise.resolve();
    return XMLHttpRequest.prototype.send.call(this, data);
  };
})();

// Pusher/Laravel Echo removed to prevent unload event permission violations
// To re-enable, add VITE_PUSHER_APP_KEY and VITE_PUSHER_APP_CLUSTER to .env
// and uncomment the following code:

// import Echo from 'laravel-echo';
// import Pusher from 'pusher-js';
// window.Pusher = Pusher;
// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });
