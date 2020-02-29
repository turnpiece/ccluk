<template>
	<div>
		<div class="sui-notice sui-notice-warning" v-show="saving_warning">
			<p>
				{{__("You've removed all recipients. If you save without a recipient, we'll automatically turn of reports")}}
			</p>
		</div>
		<div class="sui-recipients">
			<div v-for="(recipient,i) in observers" class="sui-recipient">
				<span class="sui-recipient-name">{{recipient.first_name}}</span>
				<span class="sui-recipient-email">{{recipient.email}}</span>
				<button type="button" class="sui-button-icon" v-on:click="removeRecipient(i)">
					<i aria-hidden="true" class="sui-icon-trash"></i>
				</button>
			</div>
			<button :data-a11y-dialog-show="id" type="button"
			        class="sui-button sui-button-ghost add-recipient">
				<i aria-hidden="true" class="sui-icon-plus"></i> {{__("Add Recipient")}}
			</button>
		</div>
		<div class="sui-dialog sui-dialog-sm" aria-hidden="true" tabindex="-1" :id="id">
			<div class="sui-dialog-overlay"></div>

			<div class="sui-dialog-content" aria-labelledby="dialogTitle" aria-describedby="dialogDescription"
			     role="dialog">

				<div class="sui-box" role="document">
					<div class="sui-box-header">
						<h3 class="sui-box-title">
							{{__("Add Recipient")}}
						</h3>
						<div class="sui-actions-right">
							<button type="button" data-a11y-dialog-hide class="sui-dialog-close"
							        aria-label="Close this dialog window"></button>
						</div>
					</div>
					<div class="sui-box-body">
						<p>
							{{__("Add as many recipients as you like, they will receive email reports as per the schedule you set.")}}
						</p>
						<div class="sui-form-field">
							<label class="sui-label">{{__("First name")}}</label>
							<input type="text" class="sui-form-control recipient_name" v-model="first_name">
						</div>
						<div class="sui-form-field" :class="{'sui-form-field-error':validate.email.length > 0}">
							<label class="sui-label">{{__("Email")}}</label>
							<input type="text" class="sui-form-control recipient_email" v-model="email">
							<span class="sui-error-message" v-show="validate.email.length > 0"
							      v-text="this.validate.email"></span>
						</div>
					</div>

					<div class="sui-box-footer">
						<button type="button" class="sui-button sui-button-ghost"
						        data-a11y-dialog-hide="recipient-dialog">
							{{__("Cancel")}}
						</button>
						<button type="button" v-on:click="addRecipient" :disabled="can_add===false"
						        class="sui-modal-close sui-button recipient_save">{{__("Add")}}
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
	import helper from '../helper/base_hepler';

	export default {
		mixins: [helper],
		props: ['recipients', 'id'],
		data: function () {
			return {
				first_name: '',
				email: '',
				observers: [],
				can_add: false,
				saving_warning: false,
				validate: {
					email: ''
				}
			}
		},
		created: function () {
			this.observers = this.recipients;
		},
		watch: {
			email: function () {
				if (this.validateEmail(this.email)) {
					let can_add = true;
					let self = this;
					this.observers.forEach(function (value, index) {
						if (value.email === self.email) {
							can_add = false;
							self.validate.email = self.__("This email address is already in use");
							return;
						}
					})
					this.can_add = can_add;
					if (can_add === true) {
						this.validate.email = '';
					}
				} else {
					this.can_add = false;
					this.validate.email = this.__("Invalid email address")
				}
			},
			observers: function () {
				if (this.observers.length === 0) {
					this.saving_warning = true;
				} else {
					this.saving_warning = false;
				}
				//update root recipients
				if (this.event !== undefined) {
					this.$emit('update:recipients', this.observers);
				}
			}
		},
		methods: {
			addRecipient: function () {
				this.observers.push({
					first_name: this.first_name,
					email: this.email
				})
				jQuery.each(SUI.dialogs, function (i, v) {
					v.hide();
				})
				this.first_name = '';
				this.email = '';
			},
			removeRecipient: function (i) {
				this.observers.splice(i, 1);
			},
			validateEmail: function (email) {
				var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
				return re.test(String(email).toLowerCase());
			}
		}
	}
</script>