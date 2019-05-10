require('@wpmudev/shared-ui/dist/js/_src/modals');

const WPHBReports = {
  modal: document.getElementById('wphb-performance-dialog'),
  contentContainer: document.getElementById('wphb-performance-content'),
  timer: false,
  progress: 0,
  name: '',
  nonce: '',
  settings: {
    scanning: false,
    finished: false,
    email: '',
  },

  /**
   * Initialize the module.
   */
  init: function() {
    if ( ! this.modal ) {
      return;
    }

    this.renderTemplate();
  },

  /**
   * Update the template, register new listeners.
   */
  renderTemplate: function() {
    const template = WPHBReports.template('wphb-performance');
    const content = template(this.settings);

    if ( content ) {
      this.contentContainer.innerHTML = content;
      this.contentContainer.classList.add('loaded');
    }

    this.mapActions();
  },

  /**
   * Map the "Run Test" actions.
   */
  mapActions: function() {
    const form = this.modal.querySelector('form');
    const self = this;

    if ( form ) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();

        self.name = self.modal.querySelector('input[id="name"]').value;
        self.email = self.modal.querySelector('input[id="email"]').value;
        self.nonce = document.getElementById('_wpnonce').value;

        self.settings.scanning = true;
        self.renderTemplate();
        self.runTest();
      });
    }
  },

  /**
   * Run performance test.
   */
  runTest: function() {
    if ( ! this.name || ! this.email || ! this.nonce ) {
      return;
    }

    const self = this;

    // Update progress bar.
    this.updateProgressBar();

    const xhr = new XMLHttpRequest();
    xhr.open('POST', ajaxurl+'?action=wphb_performance_run_test', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = () => {
      const response = JSON.parse(xhr.response);

      if ( ! response['data']['finished'] ) {
        // Try again 3 seconds later
        window.setTimeout(() => self.runTest(), 3000);
      } else {
        self.progress = 100;
        self.updateProgressBar();
        self.settings.finished = true;
        self.settings.email = self.email;
        self.renderTemplate();
      }
    };

    xhr.send('user='+this.name+'&email='+this.email+'&url='+window.location.href+'&_ajax_nonce='+this.nonce);
  },

  updateProgressBar: function() {
    // Test has been initialized.
    if ( 0 === this.progress ) {
      this.progress = 2;

      this.timer = window.setInterval(() => {
        this.progress += 1;
        this.updateProgressBar();
      }, 100 );
    }

    const progressStatus = this.modal.querySelector('.sui-progress-state-text');

    if ( 3 === this.progress ) {
      progressStatus.innerHTML = wphbGlobal.scanRunning;
    }

    if ( 73 === this.progress ) {
      clearInterval( this.timer );
      this.timer = false;

      this.timer = window.setInterval(() => {
        this.progress += 1;
        this.updateProgressBar();
      }, 1000 );

      progressStatus.innerHTML = wphbGlobal.scanAnalyzing;
    }

    if ( 99 === this.progress ) {
      progressStatus.innerHTML = wphbGlobal.scanWaiting;
      clearInterval( this.timer );
      this.timer = false;
    }

    this.modal.querySelector('.sui-progress-text span')
        .innerHTML = this.progress + '%';
    this.modal.querySelector('.sui-progress-bar span')
        .style.width = this.progress + '%';

    if ( 100 === this.progress ) {
      const progressIcon = this.modal.querySelector('i.sui-icon-loader');
      progressIcon.classList.remove('sui-icon-loader', 'sui-loading');
      progressIcon.classList.add('sui-icon-check');
      progressStatus.innerHTML = wphbGlobal.scanComplete;
      clearInterval( this.timer );
      this.timer = false;
    }
  },
};

/**
 * Template function (underscores based).
 *
 * @type {Function}
 */
WPHBReports.template = _.memoize((id) => {
  let compiled;
  const options = {
    evaluate: /<#([\s\S]+?)#>/g,
    interpolate: /{{{([\s\S]+?)}}}/g,
    escape: /{{([^}]+?)}}(?!})/g,
    variable: 'data',
  };

  return (data) => {
    _.templateSettings = options;
    compiled = compiled || _.template(document.getElementById(id).innerHTML);
    return compiled(data);
  };
});

export default WPHBReports;
