import axios from 'axios';
window.axios = axios;


window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
// Keep an unavailable API from holding a page-level action open indefinitely.
// Individual requests may still provide their own AbortController; this wrapper
// combines that signal with a bounded timeout and always cleans up its timer.
window.axios.defaults.timeout = 10000;

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
  
  // Intercept fetch globally and give every request a bounded lifetime. This is
  // intentionally a request-level safeguard, not a global loading indicator.
  const originalFetch = window.fetch.bind(window);
  const defaultTimeoutFor = (input, init = {}) => {
    const method = String(init.method || input?.method || 'GET').toUpperCase();
    const hasUploadBody = typeof FormData !== 'undefined' && init.body instanceof FormData;

    if (hasUploadBody) return 30000;
    return ['GET', 'HEAD', 'OPTIONS'].includes(method) ? 10000 : 20000;
  };

  const fetchWithTimeout = (input, init = {}, timeoutOverride = null) => {
    const externalSignal = init.signal;
    const controller = new AbortController();
    const timeoutMs = Number(timeoutOverride ?? init.timeoutMs);
    const timeout = Number.isFinite(timeoutMs) && timeoutMs > 0
      ? timeoutMs
      : defaultTimeoutFor(input, init);
    let timeoutId;
    let abortListener;

    if (externalSignal) {
      if (externalSignal.aborted) {
        controller.abort(externalSignal.reason);
      } else {
        abortListener = () => controller.abort(externalSignal.reason);
        externalSignal.addEventListener('abort', abortListener, { once: true });
      }
    }

    const requestInit = { ...init, signal: controller.signal };
    delete requestInit.timeoutMs;

    timeoutId = window.setTimeout(() => {
      try {
        controller.abort(new DOMException('Request timed out', 'TimeoutError'));
      } catch {
        controller.abort();
      }
    }, timeout);

    return originalFetch(input, requestInit).finally(() => {
      window.clearTimeout(timeoutId);
      if (externalSignal && abortListener) {
        externalSignal.removeEventListener('abort', abortListener);
      }
    });
  };

  window.fetchWithTimeout = fetchWithTimeout;
  window.fetch = function(input, init = {}) {
    const url = (typeof input === 'string') ? input : (input?.url || '');
    if (url.includes('_boost') || url.includes('browser-logs') || url.includes('pulse')) {
      return Promise.resolve(new Response('blocked', { status: 200 }));
    }
    return fetchWithTimeout(input, init);
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

const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;
const pusherCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER;
const host = window.location.hostname;
const isLocalhost = ['localhost', '127.0.0.1', '::1'].includes(host);
const allowLocalEcho = String(import.meta.env.VITE_ENABLE_ECHO_LOCAL || 'false').toLowerCase() === 'true';
const shouldInitEcho = !!pusherKey && !!pusherCluster && (!isLocalhost || allowLocalEcho);

if (shouldInitEcho) {
    Promise.all([
        import('laravel-echo'),
        import('pusher-js'),
    ]).then(([{ default: Echo }, { default: Pusher }]) => {
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: pusherKey,
            cluster: pusherCluster,
            forceTLS: true,
            enabledTransports: ['ws', 'wss'],
        });
    }).catch((error) => {
        console.warn('Echo initialization skipped:', error);
        window.Echo = null;
    });
} else {
    window.Echo = null;
}
