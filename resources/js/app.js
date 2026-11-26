import './lib/toast';
import { sirajSync } from './lib/sync';
import { registerPwa } from './lib/pwa';
import { registerComponents } from './components';

window.sirajSync = sirajSync;

document.addEventListener('alpine:init', () => registerComponents(window.Alpine));

registerPwa();
sirajSync.start();
