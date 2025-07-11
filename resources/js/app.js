import './bootstrap';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import iziToast from 'izitoast';
import 'izitoast/dist/css/iziToast.min.css';

window.Pusher = Pusher;
const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
const isSecure = window.location.protocol === 'https:';

if (window.Laravel?.user) {
    const echoOptions = {
        broadcaster: 'pusher',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
        wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
        wssPort: import.meta.env.VITE_REVERB_PORT || 8080,
        forceTLS: isSecure,
        encrypted: isSecure,
        enabledTransports: isSecure ? ['wss'] : ['ws'],
        cluster: 'mt1',
        disableStats: true,
    };
    window.Echo = new Echo(echoOptions);

    const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    // 📦 Ažuriran kupac
    window.Echo.channel('customer-updates')
        .listen('.customer.updated', (e) => {
            if (window.Laravel?.user?.name === e.user) return;

            iziToast.show({
                title: '👤 Kupac ažuriran',
                message: `<b>${e.user}</b> je izmenio podatke o kupcu <b>${e.customer}</b>.`,
                position: 'topRight',
                timeout: 7000,
                icon: 'fa fa-user-edit',
                layout: 2,
                progressBarColor: isDark ? '#ffc107' : '#f2711c',
                backgroundColor: isDark ? '#2c2f36' : '#fff4e5',
                titleColor: isDark ? '#ffc107' : '#d17c00',
                messageColor: isDark ? '#f8f9fa' : '#333',
                transitionIn: 'fadeInDown',
                transitionOut: 'fadeOutUp',
                balloon: true,
                class: 'rounded shadow',
            });

            window.Livewire?.dispatch('refreshCustomerTable');
        })

        // 🆕 Kreiran kupac
        .listen('.customer.created', (e) => {
            if (window.Laravel?.user?.name === e.user) return;

            iziToast.success({
                title: '🆕 Novi kupac',
                message: `<b>${e.user}</b> je dodao kupca <b>${e.customer}</b>.`,
                position: 'topRight',
                timeout: 6000,
                icon: 'fa fa-user-plus',
                layout: 2,
                progressBarColor: isDark ? '#00e676' : '#28a745',
                backgroundColor: isDark ? '#2c2f36' : '#e8f5e9',
                titleColor: isDark ? '#00e676' : '#2e7d32',
                messageColor: isDark ? '#f8f9fa' : '#333',
                transitionIn: 'fadeInDown',
                transitionOut: 'fadeOutUp',
                balloon: true,
                class: 'rounded shadow',
            });

            window.Livewire?.dispatch('refreshCustomerTable');
        });

    window.Echo.private(`App.Models.User.${window.Laravel.user.id}`)
        .notification
