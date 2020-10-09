import Vue from 'vue';
import tutorial from "./module/tutorial/tutorial";

var vm = new Vue({
    el: '#defender',
    components: {
        'tutorial': tutorial
    },
    render: (createElement) => {
        return createElement(tutorial);
    },
});