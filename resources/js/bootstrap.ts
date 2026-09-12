// resources/js/echo.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const pusherHost = import.meta.env.VITE_PUSHER_HOST || undefined;
const pusherPort = Number(import.meta.env.VITE_PUSHER_PORT || 443);

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
  forceTLS: import.meta.env.VITE_PUSHER_SCHEME !== 'http',
  ...(pusherHost ? {
    wsHost: pusherHost,
    wsPort: pusherPort,
    wssPort: pusherPort,
    enabledTransports: ['ws', 'wss'],
  } : {}),
  authEndpoint: '/broadcasting/auth',
  auth: {
    headers: {
      ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
      'X-Requested-With': 'XMLHttpRequest',
    },
  },
});

// Keep real-time diagnostics available when testing a production build locally.
// Vite removes `import.meta.env.DEV` branches from built assets, which otherwise
// hides the information needed to diagnose private-channel authentication.
const isLocalLiveDebug = ['localhost', '127.0.0.1'].includes(window.location.hostname);

if (isLocalLiveDebug) {
  const connection = (window.Echo as any).connector?.pusher?.connection;
  console.info('[Live Echo] initialized', {
    broadcaster: 'pusher',
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    customHost: Boolean(pusherHost),
  });
  connection?.bind('connected', () => console.info('[Live Echo] WebSocket connected'));
  connection?.bind('error', (error: unknown) => console.error('[Live Echo] WebSocket error', error));
  connection?.bind('disconnected', () => console.warn('[Live Echo] WebSocket disconnected'));
}
