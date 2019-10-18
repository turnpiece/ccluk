import base_helper from '../../../helper/base_hepler';
import store from '../store/store';

export default Vue.util.mergeOptions(base_helper, {
    methods: {
        /**
         * Trigger a remote request to the scan endpoint, for creating a new scan
         */
        requestNewScan: function (callback) {
            this.httpPostRequest('newScan', {}, function (response) {
                callback(response);
            });
        },
        /**
         * Ask the endpoint to process the data and return status
         * @param callback
         */
        processScan: function (callback) {
            this.httpPostRequest('processScan', {}, function (response) {
                callback(response);
            });
        },
        /**
         * Start a new scan
         */
        newScan: function () {
            let self = this;
            //backup the last state
            store.state.old_scan = store.state.scan;
            this.requestNewScan(function (response) {
                if (response.data.message !== undefined) {
                    Defender.showNotification('error', response.data.message);
                } else {
                    store.updateScan(response.data);
                    self.state.on_saving = false;
                }
            })
        },
        /**
         * Ignore a scan issue
         * @param item
         */
        ignoreIssue: function (item) {
            let self = this;
            this.httpPostRequest('ignoreIssue', {
                'id': item.id
            }, function (response) {
                store.updateScan(response.data.scan);
                self.$nextTick(() => {
                    jQuery('.sui-accordion-item').removeClass('sui-accordion-item--open');
                    self.rebindSUI()
                })
            })
        },
        /**
         * unignore an issue
         * @param item
         */
        unignoreIssue: function (item) {
            let self = this;
            this.httpPostRequest('unignoreIssue', {
                'id': item.id
            }, function (response) {
                store.updateScan(response.data.scan);
                self.$nextTick(() => {
                    jQuery('.sui-accordion-item').removeClass('sui-accordion-item--open');
                    self.rebindSUI()
                })
            })
        },
        /**
         * delete an issue
         * @param item
         */
        deleteIssue: function (item) {
            let self = this;
            this.httpPostRequest('deleteIssue', {
                'id': item.id
            }, function (response) {
                if (response.success === true) {
                    store.updateScan(response.data.scan);
                    jQuery('.sui-accordion-item').removeClass('sui-accordion-item--open');
                }
            })
        },
        /**
         * Resolve an item
         * @param item
         */
        solveIssue:function (item) {
            let self = this;
            this.httpPostRequest('solveIssue', {
                'id': item.id
            }, function (response) {
                if (response.success === true) {
                    //a bit hacky
                    store.updateScan(response.data.scan);
                    self.$nextTick(() => {
                        jQuery('.sui-accordion-item').removeClass('sui-accordion-item--open');
                        self.rebindSUI()
                    })
                }
            })
        }
    }
});