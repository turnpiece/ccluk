<template>
	<fragment>
		<div class="sui-footer" v-html="$vars.whitelabel.footer_text"></div>

		<fragment v-if="!$vars.whitelabel.hide_branding">
			<ul v-if="$vars.flags.is_pro" class="sui-footer-nav">
				<li>
					<a href="https://wpmudev.com/hub2/" target="_blank">
						{{ $i18n.footer.hub }}
					</a>
				</li>
				<li>
					<a
						href="https://wpmudev.com/projects/category/plugins/"
						target="_blank"
					>
						{{ $i18n.footer.plugins }}
					</a>
				</li>
				<li>
					<a href="https://wpmudev.com/roadmap/" target="_blank">
						{{ $i18n.footer.roadmap }}
					</a>
				</li>
				<li>
					<a href="https://wpmudev.com/hub/support/" target="_blank">
						{{ $i18n.footer.support }}
					</a>
				</li>
				<li>
					<a href="https://wpmudev.com/docs/" target="_blank">
						{{ $i18n.footer.docs }}
					</a>
				</li>
				<li>
					<a
						href="https://wpmudev.com/hub2/community/"
						target="_blank"
					>
						{{ $i18n.footer.community }}
					</a>
				</li>
				<li>
					<a
						href="https://wpmudev.com/terms-of-service/"
						target="_blank"
					>
						{{ $i18n.footer.tos }}
					</a>
				</li>
				<li>
					<a
						href="https://incsub.com/privacy-policy/"
						target="_blank"
					>
						{{ $i18n.footer.privacy }}
					</a>
				</li>
			</ul>
			<ul class="sui-footer-nav" v-else>
				<li>
					<a
						href="https://profiles.wordpress.org/wpmudev#content-plugins"
						target="_blank"
					>
						{{ $i18n.footer.free_plugins }}
					</a>
				</li>
				<li>
					<a href="https://wpmudev.com/features/" target="_blank">
						{{ $i18n.footer.membership }}
					</a>
				</li>
				<li>
					<a href="https://wpmudev.com/roadmap/" target="_blank">
						{{ $i18n.footer.roadmap }}
					</a>
				</li>
				<li>
					<a
						href="https://wordpress.org/support/plugin/beehive-analytics"
						target="_blank"
					>
						{{ $i18n.footer.support }}
					</a>
				</li>
				<li>
					<a href="https://wpmudev.com/docs/" target="_blank">
						{{ $i18n.footer.docs }}
					</a>
				</li>
				<li>
					<a href="https://wpmudev.com/hub-welcome/" target="_blank">
						{{ $i18n.footer.hub }}
					</a>
				</li>
				<li>
					<a
						href="https://wpmudev.com/terms-of-service/"
						target="_blank"
					>
						{{ $i18n.footer.tos }}
					</a>
				</li>
				<li>
					<a
						href="https://incsub.com/privacy-policy/"
						target="_blank"
					>
						{{ $i18n.footer.privacy }}
					</a>
				</li>
			</ul>
		</fragment>

		<ul v-if="!$vars.whitelabel.hide_branding" class="sui-footer-social">
			<li>
				<a href="https://www.facebook.com/wpmudev" target="_blank">
					<i class="sui-icon-social-facebook" aria-hidden="true"></i>
					<span class="sui-screen-reader-text">
						{{ $i18n.footer.facebook }}
					</span>
				</a>
			</li>
			<li>
				<a href="https://twitter.com/wpmudev" target="_blank">
					<i class="sui-icon-social-twitter" aria-hidden="true"></i>
					<span class="sui-screen-reader-text">
						{{ $i18n.footer.twitter }}
					</span>
				</a>
			</li>
			<li>
				<a href="https://www.instagram.com/wpmu_dev/" target="_blank">
					<i class="sui-icon-instagram" aria-hidden="true"></i>
					<span class="sui-screen-reader-text">
						{{ $i18n.footer.instagram }}
					</span>
				</a>
			</li>
		</ul>
	</fragment>
</template>
