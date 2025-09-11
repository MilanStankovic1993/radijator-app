import './bootstrap';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import iziToast from 'izitoast';
import 'izitoast/dist/css/iziToast.min.css';

console.log('✅ JS učitan');

const echoEnabled = import.meta.env.VITE_ECHO_ENABLED === 'true';

if (echoEnabled) {
  window.Pusher = Pusher;

  const isSecure = window.location.protocol === 'https:';
  const isLocalhost = ['localhost', '127.0.0.1'].includes(window.location.hostname);

  const echoOptions = {
    broadcaster: 'pusher',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: isLocalhost ? 8080 : 443,
    wssPort: 443,
    forceTLS: !isLocalhost,
    encrypted: !isLocalhost,
    enabledTransports: isSecure ? ['wss'] : ['ws'],
    cluster: 'mt1',
    disableStats: true,
  };

  window.Echo = new Echo(echoOptions);

  window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('📶 Echo konekcija USPOSTAVLJENA!');
  });
  window.Echo.connector.pusher.connection.bind('error', (err) => {
    console.error('❌ Echo konekcija greška:', err);
  });
  window.Echo.connector.pusher.connection.bind('state_change', (states) => {
    console.log('🔄 Echo konekcija stanje:', states);
  });

  console.log('📡 Echo konekcija:', echoOptions);
  console.log('👤 window.Laravel.user:', window.Laravel?.user);

  if (window.Laravel?.user) {
    console.log('📡 Pokušavam povezivanje na kanal...');
    const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    window.Echo.channel('customer-updates')
      .listen('.customer.updated', (e) => {
        if (window.Laravel?.user?.name === e.user) return;
        iziToast.show({
          title: '👤 Kupac ažuriran',
          message: `<b>${e.user}</b> je izmenio podatke o kupcu <b>${e.customer}</b>.`,
          position: 'topRight',
          timeout: 7000,
          layout: 2,
        });
        window.Livewire?.dispatch('refreshCustomerTable');
      })
      .listen('.customer.created', (e) => {
        if (window.Laravel?.user?.name === e.user) return;
        iziToast.success({
          title: '🆕 Novi kupac',
          message: `<b>${e.user}</b> je dodao kupca <b>${e.customer}</b>.`,
          position: 'topRight',
          timeout: 6000,
          layout: 2,
        });
        window.Livewire?.dispatch('refreshCustomerTable');
      });

    window.Echo.private(`App.Models.User.${window.Laravel.user.id}`)
      .notification((notification) => {
        iziToast.info({
          title: notification.title ?? 'Obaveštenje',
          message: notification.message ?? '',
          position: 'topRight',
          timeout: 5000,
        });
      });
  }
} else {
  // “No-op” Echo: sprečava WS konekcije, ali ostavlja API-je koje Livewire/axios očekuju
  console.log('🔕 Echo je isključen (VITE_ECHO_ENABLED=false).');
  window.Echo = {
    socketId: () => null, // ⬅⬅⬅ bitno!
    channel: () => ({ listen: () => ({ listen: () => {} }) }),
    private: () => ({ notification: () => {} }),
    connector: { pusher: { connection: { bind: () => {} } } },
  };
}
