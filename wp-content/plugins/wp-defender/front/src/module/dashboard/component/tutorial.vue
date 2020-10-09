<template>
  <div v-if="whitelabel.hide_doc_link==false" class="sui-box wd-tutorial">
    <div class="sui-box-header">
      <h3 class="sui-box-title">
        {{__("Tutorials")}}
      </h3>
      <div class="sui-actions-right">
        <a href="https://premium.wpmudev.org/blog/category/tutorials/" target="_blank" class="wd-link">
          <i class="sui-icon-open-new-window icon-link-blue sui-sm"
             aria-hidden="true"></i>{{__('View all')}}
        </a>
        <button @click="hide" class="sui-button-icon sui-tooltip"
                :style="{'marginLeft':'10px'}"
                data-tooltip="Hide tutorials"
                aria-label="Close this dialog window">
          <i class="sui-icon-close"></i>
        </button>
      </div>
    </div>
    <div class="sui-box-body">
      <div class="sui-row">
        <div class="sui-col-lg-3 sui-col-md-4 wd-tutorial-post">
          <div class="wd-tutorial-title">
            <img class="sui-image" :src="assetUrl('assets/img/tutorial1.png')"
                 :srcset="assetUrl('assets/img/tutorial1@2x.png 2x')"
                 aria-hidden="true"/>
            <div class="wd-tutorial-title-text">
              <small class="no-margin-bottom" v-html="tutorialTitle(1, tutorialLink1)"></small>
              <p class="sui-description no-margin-top">
                <i class="sui-icon-clock" aria-hidden="true"></i> 5 {{timeRead}}
              </p>
            </div>
          </div>
          <p class="sui-description wd-tutorial-desc" v-html="tutorialDesc(1, tutorialLink1)"></p>
        </div>
        <div class="sui-col-lg-3 sui-col-md-4 wd-tutorial-post">
          <div class="wd-tutorial-title">
            <img class="sui-image" :src="assetUrl('assets/img/tutorial2.png')"
                 :srcset="assetUrl('assets/img/tutorial2@2x.png 2x')"
                 aria-hidden="true"/>
            <div class="wd-tutorial-title-text">
              <small class="no-margin-bottom" v-html="tutorialTitle(2, tutorialLink2)"></small>
              <p class="sui-description no-margin-top">
                <i class="sui-icon-clock" aria-hidden="true"></i> 6 {{timeRead}}
              </p>
            </div>
          </div>
          <p class="sui-description wd-tutorial-desc" v-html="tutorialDesc(2, tutorialLink2)"></p>
        </div>
        <div class="sui-col-lg-3 sui-col-md-4 wd-tutorial-post" v-if="showMore">
          <div class="wd-tutorial-title">
            <img class="sui-image" :src="assetUrl('assets/img/tutorial3.png')"
                 :srcset="assetUrl('assets/img/tutorial3@2x.png 2x')"
                 aria-hidden="true"/>
            <div class="wd-tutorial-title-text">
              <small class="no-margin-bottom" v-html="tutorialTitle(3, tutorialLink3)"></small>
              <p class="sui-description no-margin-top">
                <i class="sui-icon-clock" aria-hidden="true"></i> 7 {{timeRead}}
              </p>
            </div>
          </div>
          <p class="sui-description wd-tutorial-desc" v-html="tutorialDesc(3, tutorialLink3)"></p>
        </div>
        <div class="sui-col-lg-3 sui-col-md-4 wd-tutorial-post" v-if="showMore">
          <div class="wd-tutorial-title">
            <img class="sui-image" :src="assetUrl('assets/img/tutorial4.png')"
                 :srcset="assetUrl('assets/img/tutorial4@2x.png 2x')"
                 aria-hidden="true"/>
            <div class="wd-tutorial-title-text">
              <small class="no-margin-bottom" v-html="tutorialTitle(4, tutorialLink4)"></small>
              <p class="sui-description no-margin-top">
                <i class="sui-icon-clock" aria-hidden="true"></i> 6 {{timeRead}}
              </p>
            </div>
          </div>
          <p class="sui-description wd-tutorial-desc" v-html="tutorialDesc(4, tutorialLink4)"></p>
        </div>
        <a href="#" class="wd-link wd-link-show-more icon-link-blue"
           @click="showMore = !showMore"
           :aria-expanded="showMore ? true : false"
        >
          {{__('Show')}} {{showMore ? 'less' : 'more'}} <i
            :class="showMore ? 'sui-icon-chevron-up' : 'sui-icon-chevron-down'"
            class="icon-link-blue sui-sm" aria-hidden="true"></i>
        </a>
      </div>
      <a class="slider__control slider__control_left" href="#" role="button" @click="prevSlide">
        <i class="sui-icon-chevron-left sui-sm" aria-hidden="true"></i>
      </a>
      <a class="slider__control slider__control_right slider__control_show" href="#" role="button" @click="nextSlide">
        <i class="sui-icon-chevron-right sui-sm" aria-hidden="true"></i>
      </a>
    </div>
  </div>
</template>

<script>
  import base_helper from '../../../helper/base_hepler';

  export default {
    mixins: [base_helper],
    name: "tutorial",
    props: ['link'],
    data: function () {
      return {
        showMore: false,
        whitelabel: defender.whitelabel,
        width: {
          document: 0
        },
        suiBreakpoints: {
          tablet: 782,
          largeDevice: 1200
        },
        timeRead: '',
        tutorialLink1: 'https://premium.wpmudev.org/blog/stop-hackers-with-defender-wordpress-security-plugin/',
        tutorialLink2: 'https://premium.wpmudev.org/blog/delete-suspicious-code-defender/',
        tutorialLink3: 'https://premium.wpmudev.org/blog/how-to-get-the-most-out-of-defender-security/',
        tutorialLink4: 'https://premium.wpmudev.org/blog/defender-ip-address-lockout-firewall/',
        state: {
          on_saving: false
        },
        nonces: dashboard.tutorials.nonces,
        endpoints: dashboard.tutorials.endpoints
      }
    },
    created: function () {
      this.showMore = ! this.isMobile() ? true : false;
      this.timeRead = this.__('min read');
    },
    computed: {
      documentWidth: function () {
        return this.width.document
      }
    },
    watch: {
      documentWidth () {
        this.reload()
      }
    },
    mounted() {
      let that = this;
      this.$nextTick(() => {
        // Refresh on screen change
        window.addEventListener('resize', that.getWidthDocument)
        // Calculating the window width
        that.getWidthDocument()
      })
    },
    methods: {
      isMobile() {
          return screen.width <= 760 ? true : false;
      },
      tutorialTitle: function (item = 1, tutorialLink) {
        let text = '';
        switch(item) {
          case 1:
            text = this.__('How to Stop Hackers in Their Tracks with Defender');
            break;
          case 2:
            if ( 1640<=this.width.document || (500<this.width.document && 793>this.width.document) ) {
              text = this.__("Find Out if You’re Hacked: How to Find and Delete Suspicious Code with Defender");
            } else {
              text = this.__("Find Out if You’re Hacked: How to Find and Delete Suspicious Code...");
            }
            break;
          case 3:
            text = this.__('How to Get the Most Out of Defender Security');
            break;
          case 4:
            if (1540<=this.width.document || (430<this.width.document && 793>this.width.document) ) {
              text = this.__('How to Create a Powerful and Secure Customized Firewall with Defender');
            } else {
              text = this.__('How to Create a Powerful and Secure Customized Firewall...');
            }
            break;
          default:
            break;
        }
        return this.vsprintf('<a href="%s" target="_blank">%s</a>', tutorialLink, text );
      },
      tutorialDesc: function (item = 1, tutorialLink) {
        let text, postLink = this.__('Read article');
        switch(item) {
          case 1:
            text = this.__('Defender deters hackers with IP banning, login lockout, updating security keys, and more.');
            break;
          case 2:
            text = this.__('Detecting suspicious code within a site isn’t always that simple and can easily go unnoticed.');
            break;
          case 3:
            text = this.__('Keeping your WordPress site safe often requires no more than the click of a button with Defender.');
            break;
          case 4:
            text = this.__('Hackers can be persistent at trying to get into your site and drop malicious code...');
            break;
          default:
              break;
        }
        return this.vsprintf('<a href="%s" target="_blank">%s <span>%s</span></a>', tutorialLink, text, postLink );
      },
      nextSlide() {
        jQuery('.wd-tutorial-post').hide().last().show();
        jQuery('.slider__control_right').hide();
        jQuery('.slider__control_left').show();
      },
      prevSlide() {
        jQuery('.wd-tutorial-post').show().last().hide();
        jQuery('.slider__control_right').show();
        jQuery('.slider__control_left').hide();
      },
      // Size of the browser window
      getWidthDocument() {
        this.width.document = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth
      },
      reload () {
        // Show slider arrows for 782 < width < 1200
        if(
            this.suiBreakpoints.largeDevice <= this.width.document
            || this.suiBreakpoints.tablet >= this.width.document
        ) {
          jQuery('.slider__control').hide();
          jQuery('.wd-tutorial-post').show();
        } else {
          this.prevSlide();
        }
      },
      hide: function () {
          let that = this;
          this.state.on_saving = true;
          let url = ajaxurl + '?action=' + this.endpoints['hide'] + '&_wpnonce=' + this.nonces['hide'];
          jQuery.ajax({
            url: url,
            method: 'post',
            data: this.model,
            success: function (response) {
              let data = response.data;
              that.state.on_saving = false;
              if (data !== undefined && data.message !== undefined) {
                if (response.success) {
                  jQuery('.wd-tutorial').hide();
                  Defender.showNotification('success', data.message, true);
                } else {
                  Defender.showNotification('error', data.message);
                }
              }
            }
          })
      }
    },
    beforeDestroy () {
      window.removeEventListener('resize', this.getWidthDocument);
    }
  }
</script>
<style scoped>
.slider__control {
  position: absolute;
  top: 50%;
  display: none;
}

.slider__control_left {
  left: 15px;
}

.slider__control_right {
  right: 15px;
}

@media (min-width: 783px) and (max-width: 1199px){
  .slider__control_show {
    display: flex;
  }
}
</style>