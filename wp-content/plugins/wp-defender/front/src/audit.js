import Vue from 'vue';
import audit from './module/audit/audit';
import audit_free from './module/audit/audit-free';
import * as moment from 'moment'
import overlay from './component/overlay';
import submit_button from './component/submit-button';
import footer from './component/footer';
import doc_link from './component/doc-link';
import summary_box from './component/summary-box';

Vue.filter('moment', function (value, format) {
    if (!value) return moment().format(format)
    return moment(value).format(format);
})
Vue.component('overlay', overlay);
Vue.component('submit-button', submit_button)
Vue.component('app-footer', footer);
Vue.component('doc-link', doc_link);
Vue.component('summary-box', summary_box);
var vm = new Vue({
    el: '#defender',
    components: {
        'audit': audit,
        'audit_free': audit_free
    },
    render: (createElement) => {
        if (parseInt(defender.is_free) === 0) {
            return createElement(audit)
        } else {
            return createElement(audit_free)
        }
    },
})