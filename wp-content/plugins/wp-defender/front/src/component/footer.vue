<template>
    <div>
        <div class="sui-footer" v-if="whitelabel.change_footer===true">
            {{whitelabel.footer_text}}
        </div>
        <div class="sui-footer" v-else>Made with <i class="sui-icon-heart"></i> by WPMU DEV</div>

        <div v-if="whitelabel.hide_doc_link===false">
            <ul v-if="is_free" class="sui-footer-nav">
                <li><a href="https://profiles.wordpress.org/wpmudev#content-plugins" target="_blank">Free
                    Plugins</a>
                </li>
                <li><a href="https://premium.wpmudev.org/features/" target="_blank">Membership</a></li>
                <li><a href="https://premium.wpmudev.org/roadmap/" target="_blank">Roadmap</a></li>
                <li><a href="https://wordpress.org/support/plugin/plugin-name" target="_blank">Support</a></li>
                <li><a href="https://premium.wpmudev.org/docs/" target="_blank">Docs</a></li>
                <li><a href="https://premium.wpmudev.org/hub/" target="_blank">The Hub</a></li>
                <li><a href="https://premium.wpmudev.org/terms-of-service/" target="_blank">Terms of Service</a>
                </li>
                <li><a href="https://incsub.com/privacy-policy/" target="_blank">Privacy Policy</a></li>
            </ul>
            <ul v-else class="sui-footer-nav">
                <li><a href="https://premium.wpmudev.org/hub/" target="_blank">The Hub</a></li>
                <li><a href="https://premium.wpmudev.org/projects/category/plugins/" target="_blank">Plugins</a>
                </li>
                <li><a href="https://premium.wpmudev.org/roadmap/" target="_blank">Roadmap</a></li>
                <li><a href="https://premium.wpmudev.org/hub/support/" target="_blank">Support</a></li>
                <li><a href="https://premium.wpmudev.org/docs/" target="_blank">Docs</a></li>
                <li><a href="https://premium.wpmudev.org/hub/community/" target="_blank">Community</a></li>
                <li><a href="https://premium.wpmudev.org/terms-of-service/" target="_blank">Terms of Service</a>
                </li>
                <li><a href="https://incsub.com/privacy-policy/" target="_blank">Privacy Policy</a></li>
            </ul>
            <ul class="sui-footer-social">
                <li><a href="https://www.facebook.com/wpmudev" target="_blank">
                    <i class="sui-icon-social-facebook" aria-hidden="true"></i>
                    <span class="sui-screen-reader-text">Facebook</span>
                </a></li>
                <li><a href="https://twitter.com/wpmudev" target="_blank">
                    <i class="sui-icon-social-twitter" aria-hidden="true"></i></a>
                    <span class="sui-screen-reader-text">Twitter</span>
                </li>
                <li><a href="https://www.instagram.com/wpmu_dev/" target="_blank">
                    <i class="sui-icon-instagram" aria-hidden="true"></i>
                    <span class="sui-screen-reader-text">Instagram</span>
                </a>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
    export default {
        data: function () {
            return {
                whitelabel: defender.whitelabel,
                is_free: defender.is_free
            }
        }
    }
</script>