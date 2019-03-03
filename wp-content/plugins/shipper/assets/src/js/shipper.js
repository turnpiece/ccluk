// Everything and the kitchen sink.
import '@wpmudev/shared-ui';
import './navbar.js';

import './migrate/shared-actions.js';
import './migrate/tick.js';
import './migrate/initial.js';
import './migrate/check-hub.js';
import './migrate/check-system.js';
import './migrate/check.js';
import './migrate/progress.js';
import './tools/tools.js';
import './tools/sysinfo.js';
import './settings/settings.js';
import './settings/notifications.js';
import './migrate/preflight-files.js';

var shipper_sui_version = require('@wpmudev/shared-ui/package.json').version;
;(function($) {
	var pkgv = 'sui-' + shipper_sui_version.replace(/\./g, '-');
	$(function() {
		$('.shipper-sui')
			.addClass(pkgv)
		;
	});
})(jQuery);
