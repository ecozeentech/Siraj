import { toastHost } from '../lib/toast';
import { pwaStatus, pushSetup } from '../lib/pwa';
import { kidsTimer, kidsGame } from './kids';
import { prayerCountdown, qiblaCompass, tasbih, adhkarCounter, locationPicker, prayerTracker } from './worship';
import { quranReader, offlineQuran, offlineReader } from './quran';
import { lessonPlayer } from './academy';
import { composer, shareButton } from './social';
import { zakatCalculator } from './zakat';

export const registerComponents = (Alpine) => {
    Alpine.data('toastHost', toastHost);
    Alpine.data('pwaStatus', pwaStatus);
    Alpine.data('pushSetup', pushSetup);
    Alpine.data('kidsTimer', kidsTimer);
    Alpine.data('kidsGame', kidsGame);
    Alpine.data('prayerCountdown', prayerCountdown);
    Alpine.data('qiblaCompass', qiblaCompass);
    Alpine.data('tasbih', tasbih);
    Alpine.data('adhkarCounter', adhkarCounter);
    Alpine.data('locationPicker', locationPicker);
    Alpine.data('prayerTracker', prayerTracker);
    Alpine.data('quranReader', quranReader);
    Alpine.data('offlineQuran', offlineQuran);
    Alpine.data('offlineReader', offlineReader);
    Alpine.data('lessonPlayer', lessonPlayer);
    Alpine.data('composer', composer);
    Alpine.data('shareButton', shareButton);
    Alpine.data('zakatCalculator', zakatCalculator);
};
