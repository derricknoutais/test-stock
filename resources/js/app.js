require('./bootstrap');
import Multiselect from 'vue-multiselect'

window.Vue = require('vue');



const files = require.context('./', true, /\.vue$/i)
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('multiselect', Multiselect)

const app = new Vue({
    el: '#app',
});


