import store from '../store/store';

export default {
	methods: {
		resolve: function (data, callback) {
			let url = ajaxurl + '?action=' + security_tweaks.endpoints['processTweak'] + '&_wpnonce=' + security_tweaks.nonces['processTweak'];
			jQuery.ajax({
				url: url,
				type: 'POST',
				data: data,
				success: function (data) {
					store.update(data);
					callback(data);
				}
			})
		},
		ignore: function () {
			this.state.on_saving = true;
			let url = ajaxurl + '?action=' + security_tweaks.endpoints['ignoreTweak'] + '&_wpnonce=' + security_tweaks.nonces['ignoreTweak'];
			let self = this;
			jQuery.ajax({
				url: url,
				type: 'POST',
				data: {
					slug: this.slug
				},
				success: function (response) {
					//self.$parent.$emit('refresh', response);
					store.update(response);
					self.state.on_saving = false;
				}
			})
		},
		restore: function () {
			this.state.on_saving = true;
			let url = ajaxurl + '?action=' + security_tweaks.endpoints['restoreTweak'] + '&_wpnonce=' + security_tweaks.nonces['restoreTweak'];
			let self = this;
			jQuery.ajax({
				url: url,
				type: 'POST',
				data: {
					slug: this.slug
				},
				success: function (response) {
					store.update(response);
					self.state.on_saving = false;
				}
			})
		},
		revert: function () {
			this.state.on_saving = true;
			let url = ajaxurl + '?action=' + security_tweaks.endpoints['revertTweak'] + '&_wpnonce=' + security_tweaks.nonces['revertTweak'];
			let self = this;
			jQuery.ajax({
				url: url,
				type: 'POST',
				data: {
					slug: this.slug
				},
				success: function (response) {
					store.update(response);
					self.state.on_saving = false;
				}
			})
		},
		reCheck: function (type) {
			this.state.on_saving = true;
			let self = this;
			let url = ajaxurl + '?action=' + security_tweaks.endpoints['reCheck'] + '&_wpnonce=' + security_tweaks.nonces['recheck'];
			jQuery.ajax({
				url: url,
				type: 'POST',
				data: {
					type: type
				},
				success: function (response) {
					self.state.on_saving = false;
					location.reload();
				}
			})
		}
	},
	computed: {
		titleIcon: function () {
			if (this.status === 'issues') return 'sui-icon-warning-alert sui-warning';
			if (this.status === 'fixed') return 'sui-icon-check-tick sui-success';
			if (this.status === 'ignore') return 'sui-icon-eye-hide';
		},
		cssClass: function () {
			if (this.status === 'issues') return 'sui-warning';
			if (this.status === 'fixed') return 'sui-success';
			return 'sui-default';
		},
	}
};