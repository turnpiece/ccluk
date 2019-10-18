export default {
    debug: true,
    state: {
        /**
         * store the scan data
         */
        scan: scanData.scan,
        /**
         * we use this to flag if the screen was changing by script or load by request
         */
        state_changed: false,
        /**
         * store the old scan data, case we use when canceling a scan and return back
         */
        old_scan: null,
        /**
         * Current filter
         */
        active_filter: null,
        /**
         * store the ids for bulk action
         */
        bulk_ids: []
    },
    updateScan: function (status) {
        this.state.scan = status;
        this.state.state_changed = true;
    },
    update: function (key, value) {
        this.state[key] = value;
    }
}