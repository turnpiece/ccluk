import Vue from 'vue';
import submit_button from './component/submit-button';
import footer from './component/footer';
import settings from './module/settings/settings';
import doc_link from './component/doc-link';

Vue.component('submit-button', submit_button)
Vue.component('app-footer', footer);
Vue.component('doc-link', doc_link);
var vm = new Vue({
    el: '#defender',
    components: {
        'settings': settings
    },
    render: (createElement) => {
        return createElement(settings)
    },
    data: {
        high_contrast: defender.misc.high_contrast
    }
})