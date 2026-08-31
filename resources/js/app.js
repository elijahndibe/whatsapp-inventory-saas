import './bootstrap';

import Alpine from 'alpinejs';
import { Chart, registerables } from 'chart.js';
import geoPicker from './geo';

window.Alpine = Alpine;
Chart.register(...registerables);
window.Chart = Chart;

Alpine.data('geoPicker', geoPicker);

Alpine.start();
