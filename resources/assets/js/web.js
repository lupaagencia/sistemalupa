require('./bootstrap');

window.Vue = require('vue');
Vue.component('app', require('./web/App.vue'));

const app = new Vue({
    el: '#web',
    data :{
        menu : 0
    }
});