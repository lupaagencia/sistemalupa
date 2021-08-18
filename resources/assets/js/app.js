
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('categoria', require('./components/Categoria.vue'));
Vue.component('articulo', require('./components/Articulo.vue'));
Vue.component('cliente', require('./components/Cliente.vue'));
Vue.component('proveedor', require('./components/Proveedor.vue'));
Vue.component('rol', require('./components/Rol.vue'));
Vue.component('user', require('./components/User.vue'));
Vue.component('ingreso', require('./components/Ingreso.vue'));
Vue.component('costop', require('./components/Costop.vue'));
Vue.component('orden', require('./components/Orden.vue'));
Vue.component('programa', require('./components/Programaprod.vue'));
Vue.component('cartera', require('./components/Cartera.vue'));
Vue.component('ventas', require('./components/Ventas.vue'));
Vue.component('seguimientov', require('./components/SeguimientoVenta.vue'));
Vue.component('nuevoproducto', require('./components/partes/NuevoProducto.vue'));

const app = new Vue({
    el: '#app',
    data :{
        menu : 0
    }
});
