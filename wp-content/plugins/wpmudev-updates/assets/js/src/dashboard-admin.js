// shared ui
import '@wpmudev/shared-ui/dist/js/_src/code-snippet';
import '@wpmudev/shared-ui/dist/js/_src/dropdowns';
import '@wpmudev/shared-ui/dist/js/_src/select';
import '@wpmudev/shared-ui/dist/js/_src/select2.full'
import '@wpmudev/shared-ui/dist/js/_src/password'
import 'ajaxq'
import './plugins';
import './support';
import './tools';
import './settings';
import './login';
import './dashboard';

// import '@wpmudev/shared-ui/dist/js/_src/accordion'
// import '@wpmudev/shared-ui/dist/js/_src/modals'
// import '@wpmudev/shared-ui/dist/js/_src/notifications'
// import '@wpmudev/shared-ui/dist/js/_src/scores'
// import '@wpmudev/shared-ui/dist/js/_src/select2'
// import '@wpmudev/shared-ui/dist/js/_src/sidenav-input'
// import '@wpmudev/shared-ui/dist/js/_src/tabs'
// import '@wpmudev/shared-ui/dist/js/_src/upload'

// export A11yDialog
window.wpmudevDashboardAdminDialog = A11yDialog;
jQuery(document).ready(function () {
	jQuery('body.wpmud-plugins').wpmudevDashboardAdminPluginsPage();
	jQuery('body.wpmud-support').wpmudevDashboardAdminSupportPage();
	jQuery('body.wpmud-tools').wpmudevDashboardAdminToolsPage();
	jQuery('body.wpmud-settings').wpmudevDashboardAdminSettingsPage();
	jQuery('body.wpmud-login').wpmudevDashboardAdminLoginPage();
	jQuery('body.wpmud-dashboard').wpmudevDashboardAdminDashboardPage();
	jQuery(document).trigger('wpmud.ready');
});
