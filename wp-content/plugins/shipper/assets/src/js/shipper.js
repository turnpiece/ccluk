// Everything and the kitchen sink.
import '@wpmudev/shared-ui';
import './navbar.js';

import './migrate/shared-actions.js';
import './migrate/tick.js';
import './migrate/initial.js'; // @deprecated
import './migrate/check-hub.js'; // @deprecated?
import './migrate/check-system.js';
import './migrate/check.js'; // @deprecated
import './migrate/progress.js';
import './tools/tools.js';
import './tools/sysinfo.js';
import './settings/settings.js';
import './settings/notifications.js';
import './settings/permissions.js';
import './migrate/preflight-files.js';

import './migrate/site-selection.js';
import './migrate/preflight.js';
import './migrate/exclusion.js'
import './migrate/dbprefix.js'

import './packages/meta.js';
import './packages/preflight.js';
import './packages/build.js';
import './packages/settings.js';

var shipper_sui_version = require('@wpmudev/shared-ui/package.json').version;
;(function($) {
	var pkgv = 'sui-' + shipper_sui_version.replace(/\./g, '-');
	$(function() {
		$('.shipper-sui')
			.addClass(pkgv)
		;
	});
})(jQuery);
