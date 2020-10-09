<template>
	<div class="sui-row">
		<div class="sui-col-md-5">
			<div class="inline-form">
				<label class="sui-checkbox">
					<input type="checkbox" v-model="filter.bulk">
					<span aria-hidden="true"></span>
				</label>
				<select class="sui-select-sm bulk-select" v-model="filter.bulk_action">
					<option value="">{{__("Bulk action")}}</option>
					<option v-if="scenario==='issue'" value="ignore">{{__("Ignore")}}</option>
					<option v-if="scenario==='ignored'" value="unignore">{{__("Restore")}}</option>
					<option v-if="scenario==='issue'" value="delete">{{__("Delete")}}</option>
				</select>
				<button type="button" @click="bulkAction"
					:class="[{'sui-button-onload':state.on_saving}]" :disabled="bulkable() === false"
					class="sui-button sui-button-ghost">
					<span class="sui-loading-text">
						{{__("Bulk Update")}}
					</span>
					<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
				</button>
			</div>
		</div>
		<div class="sui-modal sui-modal-sm">
			<div
					role="dialog"
					id="bulk-delete-confirm"
					class="sui-modal-content"
					aria-modal="true"
					aria-labelledby="bulk-delete-notice-title"
					aria-describedby="bulk-delete-notice-desc"
			>
				<div class="sui-box" role="document">
					<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
						<button data-modal-close="" class="sui-button-icon sui-button-float--right">
							<i class="sui-icon-close sui-md" aria-hidden="true"></i>
							<span class="sui-screen-reader-text">{{__('Close this dialog.')}}</span>
						</button>
						<h3 id="bulk-delete-notice-title" class="sui-box-title sui-lg">{{__('Are you sure?')}}</h3>
						<p id="bulk-delete-notice-desc" class="sui-description">
							{{__('Deleting files can be dangerous if they are needed for a plugin, theme or the' +
							' WordPress core. Please double-check the file list and confirm the deletion' +
							' when you are ready.')}}
						</p>
					</div>
					<div class="sui-box-footer sui-flatten sui-content-center sui-spacing-bottom--60">
						<button class="sui-button sui-button-ghost" data-modal-close="">{{__('Cancel')}}</button>
						<submit-button type="submit" css-class="sui-button-red sui-button-ghost"
							@click="applyDeleting" :state="state"
						>
							{{__('Confirm')}}
						</submit-button>
					</div>

				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import base_helper from '../../../helper/base_hepler';
	import store from '../store/store';

	export default {
		mixins: [base_helper],
		name: "nav-and-filter",
		props: ['scenario'],
		data: function () {
			return {
				filter: {
					bulk: false,
					bulk_action: ''
				},
				state: {
					on_saving: false,
					on_deleting: false
				},
				nonces: scanData.nonces,
				endpoints: scanData.endpoints,
			}
		},
		methods: {
			bulkAction: function () {
				let self = this;
				if ( this.filter.bulk_action === 'delete' && this.state.on_deleting === false ) {
					this.showDialog();
				} else {
					this.httpPostRequest('bulkAction', {
						'items': self.$root.store.bulk_ids,
						'bulk': self.filter.bulk_action
					}, function (response) {
						self.filter.bulk = false;
						self.state.on_deleting = false;
						self.$nextTick(() => {
							store.updateScan(response.data.scan);
							self.$nextTick(() => {
								self.rebindSUI();
							})
						})
					})
				}
			},
			bulkable: function () {
				return this.$root.store.bulk_ids.length > 0 && this.filter.bulk_action !== ''
			},
			showDialog: function () {
				const modalId = 'bulk-delete-confirm',
					focusAfterClosed = jQuery('body'),
					focusWhenOpen = undefined,
					hasOverlayMask = false
				;
				if (typeof SUI === 'undefined') {
					document.onreadystatechange = () => {
						if (document.readyState === "complete") {
							SUI.openModal(
								modalId,
								focusAfterClosed,
								focusWhenOpen,
								hasOverlayMask
							);
						}
					}
				} else {
					SUI.openModal(
						modalId,
						focusAfterClosed,
						focusWhenOpen,
						hasOverlayMask
					);
				}
			},
			applyDeleting: function () {
				this.state.on_deleting = true;
				this.bulkAction();
				this.$nextTick(() => {
					SUI.closeModal();
				})
			}
		},
		watch: {
			'filter.bulk': function (value, old) {
				this.$emit('bulk:selected', value)
			}
		},
		mounted: function () {
			let self = this;
			jQuery('.bulk-select').change(function () {
				self.filter.bulk_action = jQuery(this).val()
			})
		}
	}
</script>