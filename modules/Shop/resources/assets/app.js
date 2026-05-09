import axios from 'axios';
import JsBarcode from 'jsbarcode';
import QRCode from 'qrcode';

window.axios = axios;
window.QRCode = QRCode;
window.JsBarcode = JsBarcode;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// import { Livewire, Alpine } from '../../../../vendor/livewire/livewire/dist/livewire.esm';
// import focus from '@alpinejs/focus';

// Alpine.plugin(focus);
// Livewire.start();
