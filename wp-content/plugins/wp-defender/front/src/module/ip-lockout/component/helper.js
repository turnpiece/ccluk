import Vue from 'vue';
import base_helper from '../../../helper/base_hepler';

export default Vue.util.mergeOptions(base_helper, {
    methods: {
        toggle: function (value, type = 'auth') {
            let that = this;
            let envelope = {};
            envelope[type] = value;
            this.httpPostRequest('updateSettings', {
                data: JSON.stringify(envelope)
            }, function () {
                that.model[type] = value;
            })
        },
        updateSettings: function () {
            let data = this.model;
            this.httpPostRequest('updateSettings', {
                data: JSON.stringify(data)
            });
        }
    },
});