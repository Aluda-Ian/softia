/*
 * Softia web app: service worker, "install app" prompt and notification opt-in.
 * Plain JavaScript, no build step.
 */
(() => {
    const config = window.SoftiaApp || {};
    const storage = {
        get: (key) => { try { return localStorage.getItem(key); } catch { return null; } },
        set: (key, value) => { try { localStorage.setItem(key, value); } catch { /* private mode */ } },
    };

    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
    }

    /* ---------- Swipe carousels (manual only, phones) ---------- */
    document.querySelectorAll('[data-carousel]').forEach((track) => {
        const items = [...track.querySelectorAll('[data-carousel-item]')];
        const dots = [...(track.parentElement.querySelectorAll('[data-carousel-dot]'))];

        if (items.length === 0 || dots.length === 0) {
            return;
        }

        const setActive = (index) => dots.forEach((dot, i) => {
            const active = i === index;
            dot.classList.toggle('w-6', active);
            dot.classList.toggle('bg-navy', active);
            dot.classList.toggle('w-2', !active);
            dot.classList.toggle('bg-navy/20', !active);
            dot.setAttribute('aria-current', active ? 'true' : 'false');
        });

        // Cheap enough (a handful of cards) to run on every scroll event.
        const sync = () => {
            const left = track.scrollLeft + parseFloat(getComputedStyle(track).scrollPaddingLeft || 0);
            const index = items.reduce((best, item, i) =>
                Math.abs(item.offsetLeft - track.offsetLeft - left) < Math.abs(items[best].offsetLeft - track.offsetLeft - left) ? i : best, 0);
            setActive(index);
        };
        track.addEventListener('scroll', sync, { passive: true });

        dots.forEach((dot, i) => dot.addEventListener('click', () => {
            track.scrollTo({ left: items[i].offsetLeft - track.offsetLeft - parseFloat(getComputedStyle(track).scrollPaddingLeft || 0), behavior: 'smooth' });
        }));
    });

    /* ---------- Install prompt ---------- */
    const banner = document.querySelector('[data-install-banner]');
    let deferredPrompt = null;

    const showBanner = (mode) => {
        if (!banner || isStandalone || storage.get('softia-install-dismissed')) {
            return;
        }
        banner.dataset.mode = mode;
        banner.querySelectorAll('[data-install-mode]').forEach((el) => { el.hidden = el.dataset.installMode !== mode; });
        banner.hidden = false;
    };

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;
        setTimeout(() => showBanner('prompt'), 4000);
    });

    if (isIos && !isStandalone) {
        setTimeout(() => showBanner('ios'), 6000);
    }

    banner?.querySelector('[data-install-accept]')?.addEventListener('click', async () => {
        if (!deferredPrompt) {
            return;
        }
        deferredPrompt.prompt();
        await deferredPrompt.userChoice;
        deferredPrompt = null;
        banner.hidden = true;
    });

    banner?.querySelectorAll('[data-install-dismiss]').forEach((button) => button.addEventListener('click', () => {
        banner.hidden = true;
        storage.set('softia-install-dismissed', '1');
    }));

    window.addEventListener('appinstalled', () => { if (banner) banner.hidden = true; });

    /* ---------- Notifications ---------- */
    const bells = document.querySelectorAll('[data-notify-toggle]');
    const pushSupported = 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window && config.vapidPublicKey;

    const base64ToBytes = (base64) => {
        const padded = (base64 + '='.repeat((4 - (base64.length % 4)) % 4)).replace(/-/g, '+').replace(/_/g, '/');
        return Uint8Array.from(atob(padded), (char) => char.charCodeAt(0));
    };

    const send = (url, body) => fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrf, Accept: 'application/json' },
        body: JSON.stringify(body),
    });

    const setState = (state) => bells.forEach((bell) => {
        bell.dataset.state = state;
        bell.setAttribute('aria-pressed', state === 'on' ? 'true' : 'false');
        bell.title = { on: 'Notifications on (tap to turn off)', off: 'Get offers & order updates', blocked: 'Notifications are blocked in your browser settings' }[state];
    });

    const currentSubscription = async () => (await navigator.serviceWorker.ready).pushManager.getSubscription();

    const init = async () => {
        if (!pushSupported) {
            bells.forEach((bell) => { bell.hidden = true; });
            return;
        }
        if (Notification.permission === 'denied') {
            setState('blocked');
            return;
        }
        setState((await currentSubscription()) ? 'on' : 'off');
    };

    bells.forEach((bell) => bell.addEventListener('click', async () => {
        if (!pushSupported || bell.dataset.state === 'blocked') {
            return;
        }

        const existing = await currentSubscription();

        if (existing) {
            await send(config.unsubscribeUrl, { endpoint: existing.endpoint });
            await existing.unsubscribe();
            setState('off');
            return;
        }

        const permission = await Notification.requestPermission();

        if (permission !== 'granted') {
            setState(permission === 'denied' ? 'blocked' : 'off');
            return;
        }

        const subscription = await (await navigator.serviceWorker.ready).pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: base64ToBytes(config.vapidPublicKey),
        });

        await send(config.subscribeUrl, subscription.toJSON());
        setState('on');
    }));

    init();
})();
