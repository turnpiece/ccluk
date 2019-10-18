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
import shXframe from '../component/sh-xframe.vue';
import shXSSPtoection from '../component/sh-xss-protection.vue';
import featurePolicy from '../component/sh-feature-policy.vue';
import referrerPolicy from '../component/sh-referrer-policy.vue';
import strictTransport from '../component/sh-strict-transport.vue';
import contentTypeOptions from '../component/sh-content-type-options.vue';
import contentSecurity from '../component/sh-content-security.vue';
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
    'sh-xframe': shXframe,
    'sh-xss-protection': shXSSPtoection,
    'sh-feature-policy': featurePolicy,
    'sh-referrer-policy': referrerPolicy,
    'sh-strict-transport': strictTransport,
    'sh-content-type-options': contentTypeOptions,
    'sh-content-security': contentSecurity,
    'wp-rest-api': wpRestApi,
    'prevent-enum-users': preventEnumUsers
}