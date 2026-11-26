import { sirajSync } from '../lib/sync';

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;
export const fmtTime = (s) => {
    if (!isFinite(s) || s < 0) return '0:00';
    return `${Math.floor(s / 60)}:${String(Math.floor(s % 60)).padStart(2, '0')}`;
};

/**
 * Quran reader. Text, translation and tafsir arrive verbatim from the verified database tables.
 * Modes: ayah (one at a time), continuous, page (reference pagination from the dataset - not a verified Mushaf layout).
 * User actions (progress, bookmarks, notes, memorisation) go through the offline queue.
 */
export const quranReader = (cfg) => ({
    cfg,
    ayahs: cfg.ayahs,
    meta: cfg.meta,
    mode: cfg.prefs.mode,
    font: cfg.prefs.font,
    showTr: cfg.prefs.showTr,
    showTf: cfg.prefs.showTf,
    index: cfg.meta.kind === 'surah' ? cfg.start - 1 : 0,
    bookmarks: new Set(cfg.bookmarks),
    notes: { ...(cfg.notes || {}) },
    memorised: { ...(cfg.memorised || {}) },
    tafsirOpen: {},
    note: { open: false, ayah: null, text: '' },
    audio: { el: null, playing: false, id: null, time: 0, duration: 0, rate: 1, repeat: false, error: '' },
    page: 0,
    progressTimer: null,
    observer: null,

    init() {
        this.audio.el = new Audio();
        this.audio.el.preload = 'auto';
        this.audio.el.addEventListener('timeupdate', () => {
            this.audio.time = this.audio.el.currentTime;
            this.audio.duration = this.audio.el.duration || 0;
        });
        this.audio.el.addEventListener('ended', () => this.onEnded());
        this.audio.el.addEventListener('play', () => (this.audio.playing = true));
        this.audio.el.addEventListener('pause', () => (this.audio.playing = false));
        this.audio.el.addEventListener('error', () => {
            this.audio.playing = false;
            this.audio.error = navigator.onLine ? 'Audio is unavailable for this ayah right now.' : 'You are offline. Download this surah\'s audio to listen offline.';
        });

        this.$watch('index', () => {
            this.trackCurrent();
            if (this.mode === 'ayah') window.scrollTo({ top: 0 });
        });
        this.$watch('mode', (m) => {
            this.save({ quran_reading_mode: m });
            this.$nextTick(() => this.setupObserver());
        });

        window.addEventListener('keydown', (e) => {
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName) || this.note.open) return;
            if (this.mode === 'ayah') {
                if (e.key === 'ArrowRight' || e.key === 'ArrowDown') this.go(1);
                if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') this.go(-1);
            }
            if (e.key === ' ' && e.target === document.body) {
                e.preventDefault();
                this.toggleAudio();
            }
        });

        this.trackCurrent();
        this.$nextTick(() => {
            this.setupObserver();
            if (this.mode !== 'ayah' && this.meta.kind === 'surah' && this.index > 0) this.scrollTo(this.index);
            if (this.mode === 'page') this.page = this.pageOf(this.index);
        });
    },

    destroy() {
        this.audio.el?.pause();
        this.observer?.disconnect();
    },

    get current() {
        return this.ayahs[this.index];
    },
    get total() {
        return this.ayahs.length;
    },
    get pages() {
        const map = new Map();
        this.ayahs.forEach((a, i) => {
            if (!map.has(a.p)) map.set(a.p, []);
            map.get(a.p).push({ ...a, i });
        });
        return [...map.entries()].map(([p, list]) => ({ page: p, list }));
    },
    pageOf(i) {
        const p = this.ayahs[i]?.p;
        return Math.max(0, this.pages.findIndex((g) => g.page === p));
    },
    isBookmarked(a) {
        return this.bookmarks.has(a.id);
    },

    go(delta) {
        const next = this.index + delta;
        if (next < 0 || next >= this.total) return false;
        this.index = next;
        return true;
    },
    jump(n) {
        const i = this.ayahs.findIndex((a) => a.n === Number(n) && (this.meta.kind !== 'juz' || true));
        if (i >= 0) {
            this.index = i;
            if (this.mode === 'continuous') this.scrollTo(i);
            if (this.mode === 'page') this.page = this.pageOf(i);
            this.$dispatch('close-sheet');
        }
    },
    scrollTo(i) {
        const el = document.getElementById('ayah-' + this.ayahs[i].id);
        el?.scrollIntoView({ block: 'center' });
    },
    jumpSurah(id) {
        window.location.href = this.cfg.urls.surah.replace('__ID__', id);
    },

    setupObserver() {
        this.observer?.disconnect();
        if (this.mode !== 'continuous') return;
        this.observer = new IntersectionObserver(
            (entries) => {
                const visible = entries.filter((e) => e.isIntersecting).sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top)[0];
                if (visible) {
                    const i = this.ayahs.findIndex((a) => 'ayah-' + a.id === visible.target.id);
                    if (i >= 0 && i !== this.index) this.index = i;
                }
            },
            { rootMargin: '-35% 0px -55% 0px' },
        );
        document.querySelectorAll('[data-ayah]').forEach((el) => this.observer.observe(el));
    },

    trackCurrent() {
        clearTimeout(this.progressTimer);
        const a = this.current;
        if (!a) return;
        this.progressTimer = setTimeout(() => sirajSync.enqueue('reading.progress', { ayah_id: a.id }), 2500);
    },

    async toggleBookmark(a) {
        const on = !this.bookmarks.has(a.id);
        on ? this.bookmarks.add(a.id) : this.bookmarks.delete(a.id);
        this.bookmarks = new Set(this.bookmarks);
        window.toast(on ? 'Bookmarked' : 'Bookmark removed');
        await sirajSync.enqueue('bookmark.toggle', { ayah_id: a.id, on });
    },

    openNote(a) {
        this.note = { open: true, ayah: a, text: this.notes[a.id] || '' };
        this.$nextTick(() => this.$refs.noteInput?.focus());
    },
    async saveNote() {
        const text = this.note.text.trim();
        if (!text) return;
        this.notes[this.note.ayah.id] = text;
        await sirajSync.enqueue('note.save', { ayah_id: this.note.ayah.id, body: text });
        window.toast('Note saved');
        this.note.open = false;
    },

    async setMemorised(a, status) {
        if (status === 'none') delete this.memorised[a.id];
        else this.memorised[a.id] = status;
        this.memorised = { ...this.memorised };
        await sirajSync.enqueue('memorisation.set', { ayah_id: a.id, status });
        window.toast(status === 'none' ? 'Removed from memorisation' : 'Marked as ' + status);
        this.$dispatch('close-sheet');
    },

    toggleTafsir(a) {
        this.tafsirOpen[a.id] = !this.tafsirOpen[a.id];
    },

    setFont(delta) {
        this.font = Math.max(22, Math.min(56, this.font + delta));
        this.save({ quran_font_size: this.font });
    },
    toggleTr() {
        this.showTr = !this.showTr;
        this.save({ show_translation: this.showTr });
    },
    toggleTf() {
        this.showTf = !this.showTf;
        this.save({ show_tafsir: this.showTf });
    },
    async save(body) {
        if (this.cfg.offline || !navigator.onLine) return;
        try {
            await fetch(this.cfg.urls.prefs, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf() }, body: JSON.stringify(body) });
        } catch (e) {
            /* preference will re-sync on next change */
        }
    },

    // ---- audio ----
    play(a) {
        const el = this.audio.el;
        this.audio.error = '';
        if (this.audio.id !== a.id) {
            this.audio.id = a.id;
            el.src = this.cfg.urls.audio.replace('__ID__', a.id);
            el.playbackRate = this.audio.rate;
        }
        el.play().catch(() => {});
    },
    toggleAudio() {
        const a = this.current;
        if (!a) return;
        if (this.audio.id === a.id && this.audio.playing) this.audio.el.pause();
        else this.play(a);
    },
    seek(e) {
        if (!this.audio.duration) return;
        const rect = e.currentTarget.getBoundingClientRect();
        const ratio = Math.min(1, Math.max(0, (e.clientX - rect.left) / rect.width));
        this.audio.el.currentTime = ratio * this.audio.duration;
    },
    cycleRate() {
        const rates = [0.75, 1, 1.25, 1.5];
        this.audio.rate = rates[(rates.indexOf(this.audio.rate) + 1) % rates.length];
        this.audio.el.playbackRate = this.audio.rate;
    },
    onEnded() {
        if (this.audio.repeat) {
            this.audio.el.currentTime = 0;
            this.audio.el.play();
            return;
        }
        if (this.go(1)) {
            if (this.mode === 'continuous') this.scrollTo(this.index);
            this.play(this.current);
        } else this.audio.playing = false;
    },
    skip(delta) {
        if (this.go(delta)) {
            if (this.mode === 'continuous') this.scrollTo(this.index);
            if (this.audio.playing) this.play(this.current);
        }
    },
    get progressPct() {
        return this.audio.duration ? (this.audio.time / this.audio.duration) * 100 : 0;
    },
    fmt: fmtTime,

    // ---- sharing ----
    textFor(a) {
        const label = `Quran ${a.s}:${a.n}`;
        return [a.ar, this.showTr && a.tr ? a.tr : null, `— ${label}`].filter(Boolean).join('\n');
    },
    async copy(a) {
        try {
            await navigator.clipboard.writeText(this.textFor(a));
            window.toast('Copied');
        } catch (e) {
            window.toast('Could not copy', 'error');
        }
        this.$dispatch('close-sheet');
    },
    async share(a) {
        const data = { title: `Quran ${a.s}:${a.n}`, text: this.textFor(a), url: this.cfg.urls.surah.replace('__ID__', a.s) + '?ayah=' + a.n };
        if (navigator.share) {
            try {
                await navigator.share(data);
            } catch (e) {
                /* cancelled */
            }
        } else this.copy(a);
        this.$dispatch('close-sheet');
    },
});

/** Offline manager: downloads surah text (IndexedDB) and optional audio (Cache Storage) for use without a connection. */
export const offlineQuran = (cfg) => ({
    cfg,
    items: {},
    busy: null,
    progress: 0,
    message: '',
    estimate: null,
    cancelled: false,
    translationId: cfg.translationId,
    tafsirId: cfg.tafsirId,

    async init() {
        if (this.cfg.reciter) await sirajSync.put('kv', { key: 'audioUrl', value: this.cfg.audioUrl });
        await this.refresh();
        this.updateEstimate();
    },
    async refresh() {
        const keys = await sirajSync.keys('quran');
        const out = {};
        for (const k of keys) {
            if (String(k).startsWith('surah:')) {
                const rec = await sirajSync.get('quran', k);
                out[rec.surah.id] = { saved_at: rec.saved_at, audio: false, ayahs: rec.ayahs.length, t: rec.sources?.translation?.name };
            }
        }
        for (const k of keys) {
            if (String(k).startsWith('audio:')) {
                const rec = await sirajSync.get('quran', k);
                if (out[rec.surah]) out[rec.surah].audio = rec.complete;
            }
        }
        this.items = out;
    },
    async updateEstimate() {
        if (navigator.storage?.estimate) {
            const e = await navigator.storage.estimate();
            this.estimate = `${(e.usage / 1048576).toFixed(1)} MB used of ${(e.quota / 1048576).toFixed(0)} MB available`;
        }
    },
    async downloadText(s) {
        this.busy = 'text-' + s.id;
        this.message = '';
        try {
            const res = await fetch(`${this.cfg.dataUrl.replace('__ID__', s.id)}?t=${this.translationId || ''}&s=${this.tafsirId || ''}`, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error('failed');
            const data = await res.json();
            await sirajSync.put('quran', { key: 'surah:' + s.id, ...data });
            window.toast(`${s.name_en} saved for offline reading`);
        } catch (e) {
            this.message = 'Download failed. Check your connection and try again.';
        }
        this.busy = null;
        await this.refresh();
        this.updateEstimate();
    },
    async downloadAudio(s) {
        if (!this.items[s.id]) await this.downloadText(s);
        if (!this.items[s.id]) return;
        const rec = await sirajSync.get('quran', 'surah:' + s.id);
        this.busy = 'audio-' + s.id;
        this.cancelled = false;
        this.progress = 0;
        this.message = '';
        const cache = await caches.open('siraj-audio-v1');
        let done = 0;
        let failed = 0;
        const queue = [...rec.ayahs];
        const worker = async () => {
            while (queue.length && !this.cancelled) {
                const a = queue.shift();
                const url = this.cfg.audioUrl.replace('__ID__', a.id);
                try {
                    if (!(await cache.match(url))) {
                        const res = await fetch(url, { credentials: 'omit' });
                        if (!res.ok) throw new Error('bad');
                        await cache.put(url, res);
                    }
                } catch (e) {
                    failed++;
                }
                done++;
                this.progress = Math.round((done / rec.ayahs.length) * 100);
            }
        };
        await Promise.all([worker(), worker(), worker()]);
        const complete = !this.cancelled && failed === 0;
        await sirajSync.put('quran', { key: `audio:${this.cfg.reciter}:${s.id}`, surah: s.id, complete, saved_at: Date.now() });
        this.message = this.cancelled ? 'Download cancelled.' : failed ? `${failed} ayah(s) could not be downloaded. Try again when you have a stronger connection.` : '';
        if (complete) window.toast(`${s.name_en} audio saved`);
        this.busy = null;
        await this.refresh();
        this.updateEstimate();
    },
    cancel() {
        this.cancelled = true;
    },
    async remove(s) {
        const rec = await sirajSync.get('quran', 'surah:' + s.id);
        const cache = await caches.open('siraj-audio-v1');
        for (const a of rec?.ayahs || []) await cache.delete(this.cfg.audioUrl.replace('__ID__', a.id));
        await sirajSync.remove('quran', 'surah:' + s.id);
        await sirajSync.remove('quran', `audio:${this.cfg.reciter}:${s.id}`);
        await this.refresh();
        this.updateEstimate();
        window.toast(`${s.name_en} removed from this device`);
    },
    get count() {
        return Object.keys(this.items).length;
    },
});

/** Reader for the public /offline/quran page: reads only from IndexedDB, needs no server. */
export const offlineReader = (cfg) => ({
    cfg,
    surahs: [],
    data: null,
    showTr: true,
    showTf: false,
    font: 32,
    audio: null,
    playingId: null,
    async init() {
        const saved = await sirajSync.get('kv', 'audioUrl');
        if (saved?.value) this.cfg.audioUrl = saved.value;
        const keys = await sirajSync.keys('quran');
        const list = [];
        for (const k of keys) if (String(k).startsWith('surah:')) list.push((await sirajSync.get('quran', k)).surah);
        this.surahs = list.sort((a, b) => a.id - b.id);
        const fromHash = Number(location.hash.replace('#', ''));
        if (fromHash && this.surahs.find((s) => s.id === fromHash)) this.open(fromHash);
    },
    async open(id) {
        this.data = await sirajSync.get('quran', 'surah:' + id);
        location.hash = id;
        window.scrollTo({ top: 0 });
    },
    close() {
        this.data = null;
        history.replaceState(null, '', location.pathname);
    },
    play(a) {
        if (!this.audio) this.audio = new Audio();
        this.audio.pause();
        this.audio.src = this.cfg.audioUrl.replace('__ID__', a.id);
        this.playingId = a.id;
        this.audio.onended = () => (this.playingId = null);
        this.audio.onerror = () => {
            this.playingId = null;
            window.toast?.('Audio for this ayah is not downloaded.', 'warning');
        };
        this.audio.play().catch(() => {});
    },
    stop() {
        this.audio?.pause();
        this.playingId = null;
    },
});
