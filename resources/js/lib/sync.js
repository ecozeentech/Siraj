/*
 * Offline action queue.
 * Eligible user actions (bookmarks, reading progress, tasbih/dhikr sessions, prayer logs, adhkar progress) are
 * written to IndexedDB with a client-generated id, then flushed to POST /app/sync when online. The server stores
 * a receipt per (user, client id), so retries after a flaky connection can never create duplicates.
 */
const DB = 'siraj';
const STORE = 'queue';

const open = () =>
    new Promise((resolve, reject) => {
        const req = indexedDB.open(DB, 2);
        req.onupgradeneeded = () => {
            const db = req.result;
            if (!db.objectStoreNames.contains(STORE)) db.createObjectStore(STORE, { keyPath: 'id' });
            if (!db.objectStoreNames.contains('quran')) db.createObjectStore('quran', { keyPath: 'key' });
            if (!db.objectStoreNames.contains('kv')) db.createObjectStore('kv', { keyPath: 'key' });
        };
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });

const tx = async (store, mode, fn) => {
    const db = await open();
    return new Promise((resolve, reject) => {
        const t = db.transaction(store, mode);
        const result = fn(t.objectStore(store));
        t.oncomplete = () => resolve(result?.result ?? result);
        t.onerror = () => reject(t.error);
    });
};

const uuid = () => (crypto.randomUUID ? crypto.randomUUID() : 'id-' + Date.now() + '-' + Math.random().toString(16).slice(2));
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;

let flushing = false;

export const sirajSync = {
    uuid,

    async enqueue(type, payload) {
        const item = { id: uuid(), type, payload, created: Date.now() };
        await tx(STORE, 'readwrite', (s) => s.put(item));
        this.notify();
        if (navigator.onLine) this.flush();
        return item.id;
    },

    async all() {
        return tx(STORE, 'readonly', (s) => s.getAll());
    },

    async count() {
        return tx(STORE, 'readonly', (s) => s.count());
    },

    async notify() {
        window.dispatchEvent(new CustomEvent('sync:changed', { detail: { count: await this.count() } }));
    },

    async flush() {
        if (flushing || !navigator.onLine) return;
        flushing = true;
        try {
            const items = await this.all();
            if (!items.length) return;
            const res = await fetch('/app/sync', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ actions: items.map(({ id, type, payload }) => ({ id, type, payload })) }),
            });
            if (!res.ok) return; // keep queue; try again later (e.g. session expired -> user must log in)
            const { processed = [] } = await res.json();
            await tx(STORE, 'readwrite', (s) => processed.forEach((id) => s.delete(id)));
            await this.notify();
            if (processed.length) window.dispatchEvent(new CustomEvent('sync:done', { detail: { processed: processed.length } }));
        } catch (e) {
            /* network flake — retry on next trigger */
        } finally {
            flushing = false;
        }
    },

    start() {
        window.addEventListener('online', () => this.flush());
        setInterval(() => this.flush(), 60000);
        this.notify();
        if (navigator.onLine) this.flush();
    },

    // Generic key/value helpers for offline caches (Quran text, preferences)
    async put(store, value) {
        return tx(store, 'readwrite', (s) => s.put(value));
    },
    async get(store, key) {
        return tx(store, 'readonly', (s) => s.get(key));
    },
    async keys(store) {
        return tx(store, 'readonly', (s) => s.getAllKeys());
    },
    async remove(store, key) {
        return tx(store, 'readwrite', (s) => s.delete(key));
    },
};
