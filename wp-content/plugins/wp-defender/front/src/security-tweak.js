import Vue from 'vue';
import securityTweaks from './module/security-tweak/security-tweak';
import store from './module/security-tweak/store/store';
import submit_button from './component/submit-button';
import footer from './component/footer';
import doc_link from './component/doc-link';
import summary_box from './component/summary-box';
Vue.component('submit-button', submit_button)
Vue.component('app-footer', footer);
Vue.component('doc-link', doc_link);
Vue.component('summary-box', summary_box);
var vm = new Vue({
    el: '#defender',
    components: {
        'securityTweaks': securityTweaks
    },
    data: {
        store: store.state
    },
    render: (createElement) => {
        return createElement(securityTweaks)
    },
})