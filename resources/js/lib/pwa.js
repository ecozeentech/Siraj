/* Service worker registration, update flow and install prompt handling. */
const DISMISS_KEY = 'siraj.install.dismissed';
const DISMISS_DAYS = 30;

export const registerPwa = () => {
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', async () => {
            try {
                const reg = await navigator.serviceWorker.register('/sw.js', { scope: '/' });
                window.__sirajSW = reg;
                const announce = () => {
                    if (reg.waiting && navigator.serviceWorker.controller) window.dispatchEvent(new CustomEvent('pwa:update-ready'));
                };
                announce();
                reg.addEventListener('updatefound', () => {
                    const w = reg.installing;
                    w?.addEventListener('statechange', () => w.state === 'installed' && announce());
                });
                setInterval(() => reg.update().catch(() => {}), 60 * 60 * 1000);
            } catch (e) {
                console.warn('Service worker registration failed', e);
            }
        });
        let reloading = false;
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            if (reloading) return;
            reloading = true;
            window.location.reload();
        });
    }

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        window.__installPrompt = e;
        window.dispatchEvent(new CustomEvent('pwa:can-install'));
    });
    window.addEventListener('appinstalled', () => {
        window.__installPrompt = null;
        localStorage.removeItem(DISMISS_KEY);
        window.dispatchEvent(new CustomEvent('pwa:installed'));
        window.toast?.('SIRAJ installed. Find it on your home screen.');
    });
};

export const pwaStatus = () => ({
    online: navigator.onLine,
    queued: 0,
    justSynced: false,
    updateReady: false,
    canInstall: false,
    showIosHint: false,

    init() {
        window.addEventListener('online', () => (this.online = true));
        window.addEventListener('offline', () => (this.online = false));
        window.addEventListener('sync:changed', (e) => (this.queued = e.detail.count));
        window.addEventListener('sync:done', () => {
            this.justSynced = true;
            setTimeout(() => (this.justSynced = false), 2500);
        });
        window.addEventListener('pwa:update-ready', () => (this.updateReady = true));
        window.addEventListener('pwa:can-install', () => this.evaluateInstall());
        window.addEventListener('pwa:installed', () => {
            this.canInstall = false;
            this.showIosHint = false;
        });
        this.evaluateInstall();
        window.sirajSync?.count().then((c) => (this.queued = c));
        if (window.__sirajSW?.waiting) this.updateReady = true;
    },

    dismissedRecently() {
        const t = Number(localStorage.getItem(DISMISS_KEY) || 0);
        return t && Date.now() - t < DISMISS_DAYS * 86400000;
    },

    isStandalone() {
        return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    },

    evaluateInstall() {
        if (this.isStandalone() || this.dismissedRecently()) return;
        const ua = navigator.userAgent;
        const ios = /iphone|ipad|ipod/i.test(ua) && !window.MSStream;
        const safari = /safari/i.test(ua) && !/crios|fxios|edgios/i.test(ua);
        if (window.__installPrompt) this.canInstall = true;
        else if (ios && safari && document.body.dataset.surface !== 'admin') this.showIosHint = true;
    },

    async install() {
        const p = window.__installPrompt;
        if (!p) return;
        p.prompt();
        const { outcome } = await p.userChoice;
        window.__installPrompt = null;
        this.canInstall = false;
        if (outcome === 'dismissed') localStorage.setItem(DISMISS_KEY, String(Date.now()));
    },

    dismissInstall() {
        localStorage.setItem(DISMISS_KEY, String(Date.now()));
        this.canInstall = false;
        this.showIosHint = false;
    },

    applyUpdate() {
        window.__sirajSW?.waiting?.postMessage({ type: 'SKIP_WAITING' });
    },
});

const b64ToUint8 = (b64) => {
    const pad = '='.repeat((4 - (b64.length % 4)) % 4);
    const raw = atob((b64 + pad).replace(/-/g, '+').replace(/_/g, '/'));
    return Uint8Array.from([...raw].map((c) => c.charCodeAt(0)));
};

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content;

/** Web push opt-in for this device. Requires a VAPID key configured by the admin. */
export const pushSetup = () => ({
    supported: 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window,
    enabled: false,
    message: 'Checking…',
    async init() {
        if (!this.supported) {
            this.message = 'Push notifications are not supported in this browser. On iPhone, add SIRAJ to your home screen first.';
            return;
        }
        const reg = await navigator.serviceWorker.ready;
        this.enabled = !!(await reg.pushManager.getSubscription());
        this.message = this.enabled ? 'Push is enabled on this device.' : Notification.permission === 'denied' ? 'Notifications are blocked in your browser settings.' : 'Push is off on this device.';
    },
    async toggle() {
        const reg = await navigator.serviceWorker.ready;
        const existing = await reg.pushManager.getSubscription();
        const headers = { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() };
        if (existing) {
            await fetch('/app/push/unsubscribe', { method: 'POST', headers, body: JSON.stringify({ endpoint: existing.endpoint }) });
            await existing.unsubscribe();
            this.enabled = false;
            this.message = 'Push is off on this device.';
            return;
        }
        const perm = await Notification.requestPermission();
        if (perm !== 'granted') {
            this.message = 'Permission was not granted.';
            return;
        }
        try {
            const sub = await reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: b64ToUint8(document.body.dataset.vapid) });
            const json = sub.toJSON();
            const res = await fetch('/app/push/subscribe', { method: 'POST', headers, body: JSON.stringify({ endpoint: json.endpoint, keys: json.keys, contentEncoding: (PushManager.supportedContentEncodings || ['aesgcm'])[0] }) });
            if (!res.ok) throw new Error('save failed');
            this.enabled = true;
            this.message = 'Push is enabled on this device.';
        } catch (e) {
            this.message = 'Could not enable push on this device.';
        }
    },
});
