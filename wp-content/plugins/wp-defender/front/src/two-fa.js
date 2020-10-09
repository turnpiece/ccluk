import Vue from 'vue';
import two_fa from './module/two-fa/two-fa';
import two_fa_free from './module/two-fa/two-fa-free';
import submit_button from './component/submit-button';
import footer from './component/footer';
import doc_link from './component/doc-link';
import summary_box from './component/summary-box';

Vue.component('submit-button', submit_button);
Vue.component('app-footer', footer);
Vue.component('doc-link', doc_link);
Vue.component('summary-box', summary_box);
var vm = new Vue({
	el: '#defender',
	components: {
		'two_fa': two_fa,
		'two_fa_free': two_fa_free
	},
	render: (createElement) => {
		if ('0' === defender.is_free) {
			return createElement(two_fa);
		} else {
			return createElement(two_fa_free);
		}
	},
});