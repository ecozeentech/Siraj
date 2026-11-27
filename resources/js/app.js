import './lib/toast';
import { sirajSync } from './lib/sync';
import { registerPwa } from './lib/pwa';
import { registerComponents } from './components';
import { animateSplash } from './lib/motion';

window.sirajSync = sirajSync;

document.addEventListener('alpine:init', () => registerComponents(window.Alpine));

registerPwa();
sirajSync.start();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => animateSplash());
} else {
    animateSplash();
}
