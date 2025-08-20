
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

// require('./bootstrap');

// window.Vue = require('vue');
window.Chart = require('chart.js');
require('Chart.PieceLabel.js');
require('chartjs-plugin-annotation');
window.numeral = require('numeral');  

// load a locale
numeral.register('locale', 'id', {
    delimiters: {
        thousands: '.',
        decimal: ','
    },
    abbreviations: {
        thousand: 'rb',
        million: 'jt',
        billion: 'm',
        trillion: 't'
    },
    currency: {
        symbol: 'Rp. '
    }
});

// switch between locales
numeral.locale('id');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// Vue.component('example-component', require('./components/ExampleComponent.vue'));

// const app = new Vue({
//     el: '#app'
// });
