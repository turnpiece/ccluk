import replaceAdminUser from '../component/replace-admin-username.vue';
import dbPrefix from '../component/db-prefix.vue';
import disableFileEditor from '../component/disable-file-editor.vue';
import disablePingback from '../component/disable-trackback.vue';
import disableXMLRPC from '../component/disable-xml-rpc.vue';
import hideError from '../component/hide-error.vue';
import loginDuration from '../component/login-duration.vue';
import phpVersions from '../component/php-versions.vue';
import preventPHPExecuted from '../component/prevent-php-executed.vue';
import protectInformation from '../component/protect-information.vue';
import securityKey from '../component/security-key.vue';
import wpVersion from '../component/wp-versions.vue';
import wpRestApi from '../component/wp-rest-api.vue';
import preventEnumUsers from '../component/prevent-enum-users.vue';

export default {
    'replace-admin-username': replaceAdminUser,
    'db-prefix': dbPrefix,
    'disable-file-editor': disableFileEditor,
    'disable-trackback': disablePingback,
    'disable-xml-rpc': disableXMLRPC,
    'hide-error': hideError,
    'login-duration': loginDuration,
    'php-version': phpVersions,
    'prevent-php-executed': preventPHPExecuted,
    'protect-information': protectInformation,
    'security-key': securityKey,
    'wp-version': wpVersion,
    'wp-rest-api': wpRestApi,
    'prevent-enum-users': preventEnumUsers
}