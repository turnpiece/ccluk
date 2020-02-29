import Vue from 'vue';
import file_scanning from './module/scan/scan';
import store from './module/scan/store/store';
import overlay from './component/overlay';
import submit_button from './component/submit-button';
import footer from './component/footer';
import doc_link from './component/doc-link';
import summary_box from './component/summary-box';

Vue.component('overlay', overlay);
Vue.component('submit-button', submit_button)
Vue.component('app-footer', footer);
Vue.component('doc-link', doc_link);
Vue.component('summary-box', summary_box);
var vm = new Vue({
	el: '#defender',
	components: {
		'file-scanning': file_scanning
	},
	data: {
		store: store.state,
		//prism: Prism
	},
	render: (createElement) => {
		return createElement(file_scanning)
	},
})