@once
  @auth
    @if ((int) config('security.session_idle_timeout_minutes', 30) > 0)
      <script>
        (() => {
          const timeoutMs = {{ (int) config('security.session_idle_timeout_minutes', 30) }} * 60 * 1000;
          const heartbeatIntervalMs = Math.min(60000, Math.max(10000, Math.floor(timeoutMs / 2)));
          const logoutUrl = @json(route('logout'));
          const heartbeatUrl = @json(route('session.keep-alive'));
          const loginUrl = @json(auth()->user()->user_type === 'admin'
            ? route('admin.login', ['idle' => 1])
            : route('home', ['idle' => 1]));
          const token = @json(csrf_token());
          const activityStorageKey = 'calaia-session-last-activity';
          let lastActivityAt = Date.now();
          let lastHeartbeatAt = Date.now();
          let lastHandledAt = 0;
          let timer = null;
          let expired = false;

          const storedActivity = Number(window.localStorage.getItem(activityStorageKey));
          if (Number.isFinite(storedActivity) && storedActivity > lastActivityAt - timeoutMs) {
            lastActivityAt = storedActivity;
          }

          const expireSession = () => {
            if (expired) return;
            expired = true;

            fetch(logoutUrl, {
              method: 'POST',
              credentials: 'same-origin',
              headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
              },
            }).finally(() => {
              window.location.replace(loginUrl);
            });
          };

          const scheduleExpiry = () => {
            clearTimeout(timer);
            const remainingMs = timeoutMs - (Date.now() - lastActivityAt);
            timer = setTimeout(expireSession, Math.max(remainingMs, 0));
          };

          const sendHeartbeat = () => {
            const now = Date.now();
            if (expired || now - lastHeartbeatAt < heartbeatIntervalMs) return;
            lastHeartbeatAt = now;

            fetch(heartbeatUrl, {
              method: 'POST',
              credentials: 'same-origin',
              headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
              },
            }).then((response) => {
              if (response.status === 401 || response.status === 419) {
                expireSession();
              }
            }).catch(() => {});
          };

          const markActive = () => {
            const now = Date.now();
            if (expired || now - lastHandledAt < 1000) return;

            lastHandledAt = now;
            lastActivityAt = now;
            window.localStorage.setItem(activityStorageKey, String(now));
            scheduleExpiry();
            sendHeartbeat();
          };

          ['click', 'keydown', 'mousemove', 'scroll', 'touchstart'].forEach((eventName) => {
            document.addEventListener(eventName, markActive, { passive: true });
          });

          window.addEventListener('storage', (event) => {
            if (event.key !== activityStorageKey) return;

            const activityAt = Number(event.newValue);
            if (Number.isFinite(activityAt) && activityAt > lastActivityAt) {
              lastActivityAt = activityAt;
              scheduleExpiry();
            }
          });

          document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
              scheduleExpiry();
              sendHeartbeat();
            }
          });

          window.localStorage.setItem(activityStorageKey, String(lastActivityAt));
          scheduleExpiry();
        })();
      </script>
    @endif
  @endauth
@endonce
