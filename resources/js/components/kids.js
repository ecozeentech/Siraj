const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;
const shuffle = (a) => {
    const r = [...a];
    for (let i = r.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [r[i], r[j]] = [r[j], r[i]];
    }
    return r;
};

/** Counts active screen time (only while the tab is visible) and sends the child to the limit page when time is up. */
export const kidsTimer = (url, childId) => ({
    timer: null,
    init() {
        if (!childId) return;
        this.timer = setInterval(() => {
            if (document.visibilityState !== 'visible' || !navigator.onLine) return;
            fetch(url, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf() }, body: JSON.stringify({ seconds: 30 }) })
                .then((r) => (r.ok ? r.json() : null))
                .then((d) => {
                    if (d && d.remaining <= 0) window.location.href = '/app/kids/limit';
                })
                .catch(() => {});
        }, 30000);
    },
    destroy() {
        clearInterval(this.timer);
    },
});

/** Data-driven games. "match": flip cards to pair Arabic with English. "quiz": multiple choice. */
export const kidsGame = ({ type, config, completeUrl }) => ({
    type,
    cards: [],
    flipped: [],
    matched: [],
    moves: 0,
    qIndex: 0,
    correct: 0,
    picked: null,
    done: false,
    score: 0,
    stars: 0,
    saving: false,
    locked: false,
    init() {
        if (type === 'match') {
            const cards = config.pairs.flatMap((p, i) => [
                { id: `a${i}`, pair: i, text: p.ar, ar: true },
                { id: `e${i}`, pair: i, text: p.en, ar: false },
            ]);
            this.cards = shuffle(cards);
        }
    },
    get question() {
        return config.questions?.[this.qIndex];
    },
    flip(i) {
        if (this.locked || this.flipped.includes(i) || this.matched.includes(this.cards[i].pair)) return;
        this.flipped.push(i);
        if (this.flipped.length === 2) {
            this.moves++;
            const [a, b] = this.flipped.map((x) => this.cards[x]);
            this.locked = true;
            if (a.pair === b.pair && a.ar !== b.ar) {
                this.matched.push(a.pair);
                this.flipped = [];
                this.locked = false;
                if (this.matched.length === config.pairs.length) this.finishMatch();
            } else {
                setTimeout(() => {
                    this.flipped = [];
                    this.locked = false;
                }, 800);
            }
        }
    },
    isUp(i) {
        return this.flipped.includes(i) || this.matched.includes(this.cards[i].pair);
    },
    finishMatch() {
        const n = config.pairs.length;
        this.score = Math.max(30, Math.min(100, Math.round((n / this.moves) * 100)));
        this.finish();
    },
    pick(i) {
        if (this.picked !== null) return;
        this.picked = i;
        if (i === this.question.answer) this.correct++;
        setTimeout(() => {
            this.picked = null;
            if (this.qIndex + 1 >= config.questions.length) {
                this.score = Math.round((this.correct / config.questions.length) * 100);
                this.finish();
            } else this.qIndex++;
        }, 1100);
    },
    async finish() {
        this.done = true;
        this.saving = true;
        try {
            const res = await fetch(completeUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf() }, body: JSON.stringify({ score: this.score }) });
            const data = await res.json();
            this.stars = data.stars;
        } catch (e) {
            this.stars = this.score >= 90 ? 3 : this.score >= 60 ? 2 : 1;
        }
        this.saving = false;
    },
    restart() {
        Object.assign(this, { flipped: [], matched: [], moves: 0, qIndex: 0, correct: 0, picked: null, done: false, score: 0, stars: 0, locked: false });
        if (type === 'match') this.cards = shuffle(this.cards);
    },
});
