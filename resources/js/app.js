import './lib/toast';
import { sirajSync } from './lib/sync';
import { registerPwa } from './lib/pwa';
import { registerComponents } from './components';
import { animateInterface, animateSplash } from './lib/motion';

window.sirajSync = sirajSync;

document.addEventListener('alpine:init', () => registerComponents(window.Alpine));

registerPwa();
sirajSync.start();

const startMotion = () => {
    animateSplash();
    animateInterface();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startMotion);
} else {
    startMotion();
}
document.addEventListener('livewire:navigated', startMotion);
