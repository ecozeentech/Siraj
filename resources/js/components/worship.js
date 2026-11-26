import { sirajSync } from '../lib/sync';

const pad = (n) => String(n).padStart(2, '0');

/** Live countdown to the next prayer. Uses the server clock offset so a wrong device clock doesn't skew the display. */
export const prayerCountdown = ({ target, serverNow }) => ({
    display: '--:--:--',
    timer: null,
    offset: 0,
    init() {
        this.offset = new Date(serverNow).getTime() - Date.now();
        this.tick();
        this.timer = setInterval(() => this.tick(), 1000);
    },
    destroy() {
        clearInterval(this.timer);
    },
    tick() {
        const diff = new Date(target).getTime() - (Date.now() + this.offset);
        if (diff <= 0) {
            this.display = '00:00:00';
            clearInterval(this.timer);
            setTimeout(() => window.location.reload(), 1500);
            return;
        }
        const s = Math.floor(diff / 1000);
        this.display = `${pad(Math.floor(s / 3600))}:${pad(Math.floor((s % 3600) / 60))}:${pad(s % 60)}`;
    },
});

/** Qibla compass using the device orientation sensor, with graceful fallbacks and calibration guidance. */
export const qiblaCompass = ({ bearing, calibration = 0 }) => ({
    bearing,
    heading: null,
    status: 'idle', // idle | active | unsupported | denied
    message: '',
    get rotation() {
        if (this.heading === null) return 0;
        return (((this.bearing + calibration - this.heading) % 360) + 360) % 360;
    },
    get aligned() {
        if (this.heading === null) return false;
        const d = Math.abs(((this.rotation + 540) % 360) - 180);
        return d < 4;
    },
    async start() {
        if (!('DeviceOrientationEvent' in window)) {
            this.status = 'unsupported';
            this.message = 'This device has no compass sensor. Use the bearing shown and a physical compass.';
            return;
        }
        try {
            if (typeof DeviceOrientationEvent.requestPermission === 'function') {
                const res = await DeviceOrientationEvent.requestPermission();
                if (res !== 'granted') {
                    this.status = 'denied';
                    this.message = 'Compass permission was denied. Enable motion & orientation access in your browser settings.';
                    return;
                }
            }
        } catch (e) {
            this.status = 'denied';
            this.message = 'Could not access the compass.';
            return;
        }
        const handler = (e) => {
            let h = null;
            if (typeof e.webkitCompassHeading === 'number') h = e.webkitCompassHeading;
            else if (e.absolute && typeof e.alpha === 'number') h = 360 - e.alpha;
            else if (typeof e.alpha === 'number') h = 360 - e.alpha;
            if (h !== null) {
                this.heading = (h + 360) % 360;
                this.status = 'active';
            }
        };
        window.addEventListener('deviceorientationabsolute', handler, true);
        window.addEventListener('deviceorientation', handler, true);
        setTimeout(() => {
            if (this.heading === null) {
                this.status = 'unsupported';
                this.message = 'No compass readings received. Use the bearing shown and a physical compass.';
            }
        }, 2500);
    },
});

/** Tasbih counter: tap to count, session progress is preserved locally and saved to the account. */
export const tasbih = ({ phrases, saved = null }) => ({
    phrases,
    phrase: saved?.phrase ?? phrases[0].text,
    target: saved?.target ?? 33,
    count: 0,
    total: 0,
    saving: false,
    init() {
        const local = JSON.parse(localStorage.getItem('siraj.tasbih') || 'null');
        if (local && local.day === new Date().toDateString()) {
            this.phrase = local.phrase ?? this.phrase;
            this.target = local.target ?? this.target;
            this.count = local.count ?? 0;
            this.total = local.total ?? 0;
        }
    },
    persist() {
        localStorage.setItem('siraj.tasbih', JSON.stringify({ day: new Date().toDateString(), phrase: this.phrase, target: this.target, count: this.count, total: this.total }));
    },
    tap() {
        this.count++;
        this.total++;
        if (navigator.vibrate) navigator.vibrate(this.target && this.count % this.target === 0 ? [30, 40, 30] : 8);
        if (this.target && this.count >= this.target) {
            this.save(true);
        }
        this.persist();
    },
    reset() {
        this.count = 0;
        this.persist();
    },
    async save(auto = false) {
        if (this.count < 1) return;
        const payload = { phrase: this.phrase, count: this.count, target: this.target || null };
        await sirajSync.enqueue('dhikr.session', payload);
        window.toast(auto ? 'Target reached — session saved' : 'Session saved');
        this.count = 0;
        this.persist();
    },
    selectPhrase(p) {
        this.phrase = p.text;
        this.target = p.target;
        this.count = 0;
        this.persist();
    },
    get progress() {
        return this.target ? Math.min(100, Math.round((this.count / this.target) * 100)) : 0;
    },
});

/** Per-entry repetition counter for adhkar. Completion is queued for the account (offline-safe). */
export const adhkarCounter = ({ entryId, repeat, initial = 0, day }) => ({
    count: initial,
    repeat,
    get done() {
        return this.count >= this.repeat;
    },
    tap() {
        if (this.done) return;
        this.count++;
        if (navigator.vibrate) navigator.vibrate(6);
        sirajSync.enqueue('adhkar.progress', { entry_id: entryId, count: this.count, day });
        if (this.done) window.toast('Completed');
    },
    reset() {
        this.count = 0;
        sirajSync.enqueue('adhkar.progress', { entry_id: entryId, count: 0, day });
    },
});

/** City selector + optional geolocation filling latitude / longitude / timezone fields. */
export const locationPicker = ({ locations = [], current = {} }) => ({
    locations,
    form: { name: current.name ?? '', latitude: current.latitude ?? '', longitude: current.longitude ?? '', timezone: current.timezone ?? '' },
    busy: false,
    error: '',
    pick(e) {
        const l = this.locations.find((x) => String(x.id) === e.target.value);
        if (!l) return;
        this.form = { name: `${l.name}, ${l.country}`, latitude: l.latitude, longitude: l.longitude, timezone: l.timezone };
    },
    locate() {
        if (!navigator.geolocation) {
            this.error = 'Location is not available on this device. Please choose a city.';
            return;
        }
        this.busy = true;
        this.error = '';
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                this.form = {
                    name: 'My location',
                    latitude: pos.coords.latitude.toFixed(5),
                    longitude: pos.coords.longitude.toFixed(5),
                    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || this.form.timezone,
                };
                this.busy = false;
            },
            () => {
                this.busy = false;
                this.error = 'We could not get your location. You can still choose a city manually.';
            },
            { enableHighAccuracy: false, timeout: 10000 },
        );
    },
});

/** Prayer tracker: optimistic UI, writes go through the offline queue so logging works without a connection. */
export const prayerTracker = ({ grid }) => ({
    grid,
    statuses: { on_time: 'On time', late: 'Late', qada: 'Qada', missed: 'Missed' },
    async set(day, prayer, status) {
        const row = this.grid.find((r) => r.date === day);
        const existing = row.logs[prayer];
        const next = existing?.status === status ? 'none' : status;
        if (next === 'none') delete row.logs[prayer];
        else row.logs[prayer] = { status: next, congregation: existing?.congregation ?? false };
        this.grid = [...this.grid];
        await sirajSync.enqueue('prayer.log', { day, prayer, status: next, in_congregation: row.logs[prayer]?.congregation ?? false });
    },
    async toggleCongregation(day, prayer) {
        const row = this.grid.find((r) => r.date === day);
        const log = row.logs[prayer];
        if (!log) return;
        log.congregation = !log.congregation;
        this.grid = [...this.grid];
        await sirajSync.enqueue('prayer.log', { day, prayer, status: log.status, in_congregation: log.congregation });
    },
    get done() {
        return this.grid.reduce((n, r) => n + Object.values(r.logs).filter((l) => l.status !== 'missed').length, 0);
    },
    get onTime() {
        return this.grid.reduce((n, r) => n + Object.values(r.logs).filter((l) => l.status === 'on_time').length, 0);
    },
    dot(log) {
        if (!log) return 'bg-surface-2 text-muted';
        return { on_time: 'bg-forest-700 text-white', late: 'bg-gold-300 text-forest-950', qada: 'bg-sky-200 text-sky-900', missed: 'bg-red-100 text-red-600' }[log.status];
    },
});
