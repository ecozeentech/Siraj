const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;

/** Resumes audio/video from the last saved position and saves position periodically while playing. */
export const lessonPlayer = ({ url, position = 0, enabled = true }) => ({
    last: 0,
    init() {
        const media = this.$refs.media;
        if (!media) return;
        media.addEventListener('loadedmetadata', () => {
            if (position > 5 && position < media.duration - 5) media.currentTime = position;
        });
        if (!enabled) return;
        media.addEventListener('timeupdate', () => {
            const now = Math.floor(media.currentTime);
            if (now - this.last >= 15) {
                this.last = now;
                this.save(now);
            }
        });
        media.addEventListener('pause', () => this.save(Math.floor(media.currentTime)));
    },
    save(seconds) {
        if (!navigator.onLine) return;
        fetch(url, { method: 'POST', credentials: 'same-origin', keepalive: true, headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf() }, body: JSON.stringify({ seconds }) }).catch(() => {});
    },
});
