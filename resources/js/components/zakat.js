const NISAB = { gold: 85, silver: 595 };
const RATE = 0.025;

/** Live preview only. The server recalculates the same formula on save and is the source of truth. */
export const zakatCalculator = ({ assets, liabilities, old = {} }) => ({
    v: Object.fromEntries([...assets, ...liabilities, 'gold_grams', 'silver_grams'].map((k) => [k, Number(old[k] ?? 0) || ''])),
    gold: Number(old.gold_price_per_gram) || '',
    silver: Number(old.silver_price_per_gram) || '',
    basis: old.nisab_basis || 'silver',
    hawl: true,
    n: (x) => Math.max(0, Number(x) || 0),
    get result() {
        const a = assets.reduce((s, k) => s + this.n(this.v[k]), 0) + this.n(this.v.gold_grams) * this.n(this.gold) + this.n(this.v.silver_grams) * this.n(this.silver);
        const l = liabilities.reduce((s, k) => s + this.n(this.v[k]), 0);
        const net = Math.max(0, a - l);
        const nisab = this.basis === 'gold' ? NISAB.gold * this.n(this.gold) : NISAB.silver * this.n(this.silver);
        const meets = nisab > 0 && net >= nisab;
        let note = 'Enter the gold or silver price to set the nisab.';
        if (nisab > 0) note = !meets ? 'Your net wealth is below the nisab, so zakat is not due.' : !this.hawl ? 'Zakat becomes due once the wealth has been held for a full lunar year.' : 'Zakat is due at 2.5% of your net wealth.';
        return { assets: a, liabilities: l, net, nisab, due: meets && this.hawl ? Math.round(net * RATE * 100) / 100 : 0, note };
    },
    money: (x) => '₦' + Number(x).toLocaleString('en-NG', { minimumFractionDigits: 0, maximumFractionDigits: 2 }),
});
