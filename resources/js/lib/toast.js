let uid = 0;

window.toast = (message, type = 'success') => {
    window.dispatchEvent(new CustomEvent('toast', { detail: { message, type, id: ++uid } }));
};

export const toastHost = () => ({
    items: [],
    push(t) {
        this.items.push(t);
        setTimeout(() => (this.items = this.items.filter((i) => i.id !== t.id)), 3800);
    },
});
