/**
 * Various scripts for modules.
 */
require('./modules/admin-main.js');
require('./modules/admin-performance.js');
require('./modules/admin-gzip.js');
require('./modules/admin-caching.js');
require('./modules/admin-minification.js');
require('./modules/admin-dashboard.js');
require('./modules/admin-dashboard-cloudflare.js');
require('./modules/admin-uptime.js');
require('./modules/admin-cloudflare.js');
require('./modules/admin-advanced.js');
require('./modules/admin-settings.js');

jQuery(document).ready( function() {
  WPHB_Admin.init();
});
