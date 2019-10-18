import Vue from 'vue';
import advanced_tools from './module/advanced-tools/advanced-tools';
import submit_button from './component/submit-button';
import footer from './component/footer';
import doc_link from './component/doc-link';
Vue.component('app-footer', footer);
Vue.component('doc-link', doc_link);
Vue.component('submit-button',submit_button)
var vm = new Vue({
    el: '#defender',
    components: {
        'advanced_tools': advanced_tools
    },
    render: (createElement) => {
        return createElement(advanced_tools)
    }
})