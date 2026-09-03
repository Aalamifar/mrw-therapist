import './bootstrap';

import 'jalalidatepicker/dist/jalalidatepicker.min.js';
import 'jalalidatepicker/dist/jalalidatepicker.min.css';
import * as jalaali from 'jalaali-js';

window.jalaali = jalaali;

window.jalaliDatepicker.startWatch({
    time: true,
    autoHide: true,
    persianDigits: false,
});

document.addEventListener('livewire:navigated', () => window.jalaliDatepicker.updateLoad());