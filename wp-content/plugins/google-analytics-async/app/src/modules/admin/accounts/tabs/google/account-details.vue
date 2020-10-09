<template>
	<fragment>
		<label class="sui-label">
			{{ $i18n.tooltip.connected_account }}
		</label>
		<div class="sui-border-frame google-account-overview">
			<div class="sui-box-builder sui-flushed">
				<div class="sui-builder-fields">
					<div class="sui-builder-field">
						<div class="sui-builder-field-label">
							<span
								v-if="userPhoto"
								class="beehive-google-user-photo"
							>
								<img :src="userPhoto" :alt="userName" />
							</span>
							<span>
								<span class="beehive-google-user-name">
									{{ userName }}
								</span>
								<span class="beehive-google-user-email">
									{{ userEmail }}
								</span>
							</span>
						</div>
						<div class="sui-dropdown">
							<button class="sui-button-icon sui-dropdown-anchor">
								<i
									class="sui-icon-widget-settings-config"
									aria-hidden="true"
								>
								</i>
								<span class="sui-screen-reader-text">
									{{ $i18n.button.open_options }}
								</span>
							</button>
							<ul>
								<li>
									<button
										type="button"
										id="google-logout-user"
										data-modal-open="beehive-google-logout-confirm"
									>
										<i
											class="sui-icon-logout"
											aria-hidden="true"
										></i>
										{{ $i18n.label.logout }}
									</button>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<logout-modal />
	</fragment>
</template>

<script>
import LogoutModal from './modals/logout'

export default {
	name: 'AccountDetails',

	components: { LogoutModal },

	computed: {
		/**
		 * Get user account name to display.
		 *
		 * If account name is empty, show no account notice.
		 *
		 * @since 3.2.0
		 *
		 * @returns {string}
		 */
		userName() {
			const name = this.getOption('name', 'google_login', '')
			if (name) {
				return name
			} else {
				return this.$i18n.label.no_account_info
			}
		},

		/**
		 * Get the email address of the currently connected account.
		 *
		 * @since 3.2.0
		 *
		 * @returns {string}
		 */
		userEmail() {
			return this.getOption('email', 'google_login', '')
		},

		/**
		 * Get the link to profile photo of the connected account.
		 *
		 * @since 3.2.0
		 *
		 * @returns {string}
		 */
		userPhoto() {
			return this.getOption('photo', 'google_login', '')
		},
	},
}
</script>
