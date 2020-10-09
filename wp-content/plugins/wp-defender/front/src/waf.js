import Vue from 'vue';
import submit_button from './component/submit-button';
import footer from './component/footer';
import doc_link from './component/doc-link';
import waf from "./module/waf/waf";
import waf_free from './module/waf/waf-free'

Vue.component('submit-button', submit_button);
Vue.component('app-footer', footer);
Vue.component('doc-link', doc_link);
var vm = new Vue({
    el: '#defender',
    components: {
        'waf': waf,
        'waf_free': waf_free
    },
    render: (createElement) => {
        if ('0' === defender.is_free) {
            return createElement(waf);
        } else {
            return createElement(waf_free);
        }
    },
});