<template>
	<div class="sui-modal sui-modal-sm">
		<div
			role="dialog"
			class="sui-modal-content sui-content-fade-in"
			aria-modal="true"
			:id="modal"
			:aria-labelledby="`${modal}-title`"
			:aria-describedby="`${modal}-desc`"
		>
			<div class="sui-box">
				<div
					class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60"
				>
					<button
						class="sui-button-icon sui-button-float--right"
						@click="closeModal"
						:id="`${modal}-close`"
					>
						<i class="sui-icon-close sui-md" aria-hidden="true"></i>
						<span class="sui-screen-reader-text">
							{{ $i18n.dialog.close }}
						</span>
					</button>

					<h3 :id="`${modal}-title`" class="sui-box-title sui-lg">
						{{ $i18n.title.add_user }}
					</h3>

					<p :id="`${modal}-desc`" class="sui-description">
						{{ $i18n.desc.add_user }}
					</p>
				</div>

				<div class="sui-box-body">
					<div class="sui-form-field">
						<label
							:for="`${modal}-search`"
							:id="`${modal}-search-label`"
							class="sui-label"
						>
							{{ $i18n.label.search_users }}
						</label>
						<sui-remote-search
							parent-element="beehive-settings-permissions-exclude-modal"
							v-model="selected"
							:id="`${modal}-search`"
							:label-id="`${modal}-search-label`"
							:placeholder="$i18n.label.type_user_name"
							:ajax="ajaxObject"
						/>
					</div>
				</div>

				<div class="sui-box-footer sui-flatten sui-content-separated">
					<button
						class="sui-button sui-button-ghost"
						@click="closeModal"
					>
						{{ $i18n.dialog.cancel }}
					</button>

					<button
						type="button"
						class="sui-button"
						aria-live="polite"
						:class="loadingClass"
						:disabled="!selected"
						@click="addUser"
					>
						<span class="sui-button-text-default">
							<i class="sui-icon-check" aria-hidden="true"></i>
							{{ $i18n.button.add }}
						</span>
						<span class="sui-button-text-onload">
							<i
								class="sui-icon-loader sui-loading"
								aria-hidden="true"
							></i>
							{{ $i18n.button.adding }}
						</span>
					</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import Modal from '@/components/mixins/modal'
import SuiRemoteSearch from '@/components/sui/sui-remote-search'

export default {
	name: 'UsersForm',

	components: { SuiRemoteSearch },

	mixins: [Modal],

	data() {
		return {
			modal: 'beehive-settings-permissions-exclude-modal',
			adding: false,
			type: 'exclude',
			selected: '',
		}
	},

	created() {
		// Open modal on event.
		this.$root.$on('openSettingsPermissionsUsersForm', (data) => {
			this.type = data.type
			this.closeFocus = data.focus

			// Open current modal.
			this.openModal()
		})

		// Open modal on event.
		this.$on('modal:close', (modal) => {
			this.selected = ''
		})
	},

	computed: {
		/**
		 * Get the loading class for the button.
		 *
		 * @since 3.2.5
		 *
		 * @returns {*}
		 */
		loadingClass() {
			return {
				'sui-button-onload-text': this.adding,
			}
		},

		/**
		 * Computer model object for the included users settings.
		 *
		 * @since 3.2.5
		 *
		 * @returns {array}
		 */
		includeUsers: {
			get() {
				return this.getOption(
					'settings_include_users',
					'permissions',
					[]
				)
			},
			set(value) {
				this.setOption('settings_include_users', 'permissions', value)
			},
		},

		/**
		 * Computer model object for the excluded users settings.
		 *
		 * @since 3.2.5
		 *
		 * @returns {array}
		 */
		excludeUsers: {
			get() {
				return this.getOption(
					'settings_exclude_users',
					'permissions',
					[]
				)
			},
			set(value) {
				this.setOption('settings_exclude_users', 'permissions', value)
			},
		},

		/**
		 * Get the ajax object for the select2.
		 *
		 * We need to use the api for the search and format the
		 * data result so that select2 can process it.
		 *
		 * @since 3.2.5
		 *
		 * @returns {*}
		 */
		ajaxObject() {
			const vm = this

			return {
				url: this.$vars.rest.base + 'data/users',
				dataType: 'json',
				data: this.formatParams,
				beforeSend: (xhr) => {
					xhr.setRequestHeader('X-WP-Nonce', vm.$vars.rest.nonce)
				},
				processResults: this.processData,
			}
		},
	},

	methods: {
		/**
		 * Add the selected user to the list.
		 *
		 * We will not update the changes in db.
		 *
		 * @since 3.2.5
		 *
		 * @return {void}
		 */
		addUser() {
			// Loading flag.
			this.adding = true

			// Make sure the value exist.
			if (this.selected > 0) {
				// If excluded users form.
				if ('exclude' === this.type) {
					this.addExcludedUser(this.selected)
				} else {
					// If included users form.
					this.addIncludedUser(this.selected)
				}
			}

			// Close the modal after processing.
			this.closeModal()

			this.adding = false
		},

		/**
		 * Add the selected user to the included list.
		 *
		 * @param {int} id User ID.
		 *
		 * @since 3.2.5
		 *
		 * @returns {void}
		 */
		addIncludedUser(id) {
			// Only if not already added.
			if (!this.includeUsers.includes(id)) {
				this.includeUsers.push(id)
			}
		},

		/**
		 * Add the selected user to the excluded list.
		 *
		 * @param {int} id User ID.
		 *
		 * @since 3.2.5
		 *
		 * @returns {void}
		 */
		addExcludedUser(id) {
			// Only if not already added.
			if (!this.excludeUsers.includes(id)) {
				this.excludeUsers.push(id)
			}
		},

		/**
		 * Process the ajax response and format.
		 *
		 * @since 3.2.5
		 * @see https://select2.org/data-sources/ajax#transforming-response-data
		 *
		 * @returns {*}
		 */
		processData(data) {
			let vm = this
			let options = []

			// Loop through each items.
			data.data.forEach((user) => {
				vm.$store.dispatch('helpers/setUser', user)
				options.push({
					id: user.ID,
					text: `${user.display_name} (${user.user_email})`,
				})
			})

			return {
				results: options,
			}
		},

		/**
		 * Format the default params to API format.
		 *
		 * Select2 default params format is not supported
		 * by our API. Override it.
		 *
		 * @since 3.2.5
		 * @see https://select2.org/data-sources/ajax#jquery-ajax-options
		 *
		 * @returns {object}
		 */
		formatParams(params) {
			let result = {
				network: this.isNetwork() ? 1 : 0,
				search: params.term, // Search param.
				exclude_ids: [...this.excludeUsers, ...this.includeUsers],
			}

			if ('exclude' === this.type) {
				result.include_roles = this.getOption(
					'settings_roles',
					'permissions',
					[]
				)
			} else {
				result.exclude_roles = this.getOption(
					'settings_roles',
					'permissions',
					[]
				)
			}

			return result
		},
	},
}
</script>
