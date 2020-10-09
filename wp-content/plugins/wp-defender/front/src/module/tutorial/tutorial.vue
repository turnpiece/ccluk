<template>
  <div id="tutorial" class="sui-wrap" :class="maybeHighContrast()">
    <div class="sui-header">
      <h1 class="sui-header-title">
        {{__("Tutorials")}}
      </h1>
      <div class="sui-actions-right">
        <a href="https://premium.wpmudev.org/blog/category/tutorials/" target="_blank" class="sui-button">
          <i class="sui-icon-open-new-window sui-sm"></i> {{__("View All")}}
        </a>
      </div>
    </div>
    <div class="sui-box">
      <div class="sui-box-header">
        <h3 class="sui-box-title">
          {{__("Defender Tutorials")}}
        </h3>
      </div>
      <div class="sui-box-body">
        <div class="sui-row">

          <div class="sui-col-lg-3 sui-col-md-4 sui-col-sm-6 wd-tutorial-post">
            <div class="wd-tutorial-post-wrapper">
              <div class="wd-tutorial-post-img">
                <img class="sui-image" aria-hidden="true"
                     :src="assetUrl('assets/img/tab-tutorial1@1x.png')"
                     :srcset="assetUrl('assets/img/tab-tutorial1@2x.png')"/>
              </div>
              <div class="wd-tutorial-post-body">
                <small class="no-margin-bottom wd-tutorial-post-body-title" v-html="tutorialTitle(1, tutorialLink1)"></small>
                <p class="sui-description wd-tutorial-post-body-desc" v-html="tutorialDesc(1, tutorialLink1)"></p>
                <footer class="wd-tutorial-post-body-footer">
                  <div class="wd-read-more"><a :href="tutorialLink1" target="_blank">{{titleReadLink}}</a></div>
                   <span class="time-read"><i class="sui-icon-clock" aria-hidden="true"></i> 5 {{timeRead}}</span>
                </footer>
              </div>
            </div>
          </div>

          <div class="sui-col-lg-3 sui-col-md-4 sui-col-sm-6 wd-tutorial-post">
            <div class="wd-tutorial-post-wrapper">
              <div class="wd-tutorial-post-img">
                <img class="sui-image" aria-hidden="true"
                     :src="assetUrl('assets/img/tab-tutorial2@1x.png')"
                     :srcset="assetUrl('assets/img/tab-tutorial2@2x.png')"/>
              </div>
              <div class="wd-tutorial-post-body">
                <small class="no-margin-bottom wd-tutorial-post-body-title" v-html="tutorialTitle(2, tutorialLink2)"></small>
                <p class="sui-description wd-tutorial-post-body-desc" v-html="tutorialDesc(2, tutorialLink2)"></p>
                <footer class="wd-tutorial-post-body-footer">
                  <div class="wd-read-more"><a :href="tutorialLink2" target="_blank">{{titleReadLink}}</a></div>
                  <span class="time-read"><i class="sui-icon-clock" aria-hidden="true"></i> 6 {{timeRead}}</span>
                </footer>
              </div>
            </div>
          </div>

          <div class="sui-col-lg-3 sui-col-md-4 sui-col-sm-6 wd-tutorial-post">
            <div class="wd-tutorial-post-wrapper">
              <div class="wd-tutorial-post-img">
                <img class="sui-image" aria-hidden="true"
                     :src="assetUrl('assets/img/tab-tutorial3@1x.png')"
                     :srcset="assetUrl('assets/img/tab-tutorial3@2x.png')"/>
              </div>
              <div class="wd-tutorial-post-body">
                <small class="no-margin-bottom wd-tutorial-post-body-title" v-html="tutorialTitle(3, tutorialLink3)"></small>
                <p class="sui-description wd-tutorial-post-body-desc" v-html="tutorialDesc(3, tutorialLink3)"></p>
                <footer class="wd-tutorial-post-body-footer">
                  <div class="wd-read-more"><a :href="tutorialLink3" target="_blank">{{titleReadLink}}</a></div>
                  <span class="time-read"><i class="sui-icon-clock" aria-hidden="true"></i> 7 {{timeRead}}</span>
                </footer>
              </div>
            </div>
          </div>

          <div class="sui-col-lg-3 sui-col-md-4 sui-col-sm-6 wd-tutorial-post">
            <div class="wd-tutorial-post-wrapper">
              <div class="wd-tutorial-post-img">
                <img class="sui-image" aria-hidden="true"
                     :src="assetUrl('assets/img/tab-tutorial4@1x.png')"
                     :srcset="assetUrl('assets/img/tab-tutorial4@2x.png')"/>
              </div>
              <div class="wd-tutorial-post-body">
                <small class="no-margin-bottom wd-tutorial-post-body-title" v-html="tutorialTitle(4, tutorialLink4)"></small>
                <p class="sui-description wd-tutorial-post-body-desc" v-html="tutorialDesc(4, tutorialLink4)"></p>
                <footer class="wd-tutorial-post-body-footer">
                  <div class="wd-read-more"><a :href="tutorialLink4" target="_blank">{{titleReadLink}}</a></div>
                  <span class="time-read"><i class="sui-icon-clock" aria-hidden="true"></i> 6 {{timeRead}}</span>
                </footer>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import base_hepler from "../../helper/base_hepler";
  export default {
    mixins: [base_hepler],
    name: "tutorial",
    data: function () {
      return {
        width: {
          document: 0
        },
        tutorialLink1: 'https://premium.wpmudev.org/blog/stop-hackers-with-defender-wordpress-security-plugin/',
        tutorialLink2: 'https://premium.wpmudev.org/blog/delete-suspicious-code-defender/',
        tutorialLink3: 'https://premium.wpmudev.org/blog/how-to-get-the-most-out-of-defender-security/',
        tutorialLink4: 'https://premium.wpmudev.org/blog/defender-ip-address-lockout-firewall/',
        timeRead : tutorial.time_read,
        titleReadLink : tutorial.title_read_link
      }
    },
    computed: {
      documentWidth: function () {
        return this.width.document
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
        let text;
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
            if ( 860>this.width.document ) {
              text = this.__('Hackers can be persistent at trying to get into your site and drop malicious code...');
            } else {
              text = this.__('Hackers can be persistent at trying to get into your site and drop malicious code, figuring out your credentials, leaving spam.');
            }
            break;
          default:
            break;
        }
        return this.vsprintf('<a href="%s" target="_blank">%s</a>', tutorialLink, text );
      },
      // Size of the browser window
      getWidthDocument() {
        this.width.document = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth
      }
    },
    beforeDestroy () {
      window.removeEventListener('resize', this.getWidthDocument);
    }
  }
</script>