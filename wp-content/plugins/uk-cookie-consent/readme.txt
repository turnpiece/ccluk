=== Cookie Consent ===
Contributors: Catapult_Themes, husobj, jraczynski
Donate Link: https://www.paypal.me/catapultthemes
Tags: cookie law, cookies, gdpr, eu privacy directive, cookie compliance, cookie law, cookie notice, cookie notification, wpml, geo ip
Requires at least: 4.3
Tested up to: 4.9.6
Stable tag: 2.3.11
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
The only cookie consent plugin you'll ever need.

== Description ==
Cookie Consent is one of the simplest and most popular cookie notification plugins out there. It's been actively developed and supported since 2012 and has over 100 5 star reviews.

> <strong>New in 2.3.0:</strong><br>Geo IP support - choose which continents to display notifications (requires GeoIP Detect plugin to be installed)<br>
> <strong>New in 2.2.0:</strong><br>Easily select pages or posts where the cookie notification doesn't display. You now have complete control over which pages display a notification and which don't.<br>Close on scroll - optionally choose scrolling as closure method.

= Simple set up =
One of the most popular aspects of the plugin is its simplicity of use - simply install and activate the plugin to automatically add the cookie consent notification bar. There is no need to configure it. On activation, the plugin creates and populates a page on your site with information about your cookie policy and automatically links to the page from the notification bar. So if you're using the default settings, it's a matter of seconds to get up and running.

= Features =
* Choice of dismissal method - on click, on scroll, or timed
* Choice of dismissal element - either button or 'x' close
* Option to show the notification on the first page only - subsequent pages visited by the user will not display the message
* Option to exclude selected pages and posts from displaying the notification
* Choice of position - either top or bottom bar, or floating in one of the corners of the screen
* Translation support
* Responsive
* Customization options - including the ability to update styles from within the customizer
* Inherits your theme styles where possible
* The option to use an absolute or external URL to link to for further information
* Set the cookie duration
* Set the cookie version - updating a version will reset the cookie on all user sites

= Translations =

* English
* Polish (jraczynski)
* Russian
* Slovakian (lacike)
* Italian (wocmultimedia)

= EU Directive =
We think this is the simplest but most effective method of dealing with the legislation.

The plug-in is a straightforward approach to help you comply with the EU regulations regarding usage of website cookies. It follows the notion of "implied consent" as described by the UK's Information Commissioner and makes the assumption that most users who choose not to accept cookies will do so for all websites. A user to your site is presented with a clear yet unobtrusive notification that the site is using cookies and may then acknowledge and dismiss the notification or click to find out more. The plug-in automatically creates a new page with pre-populated information on cookies and how to disable them, which you may edit further if you wish.

Importantly, the plug-in does not disable cookies on your site or prevent the user from continuing to browse the site. Several plug-ins have adopted the "explicit consent" approach which obliges users to opt in to cookies on your site. This is likely to deter visitors.

== Installation ==
1. Upload the `uk-cookie-consent` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Browse to the Cookie Consent option page in Settings to configure
== Frequently Asked Questions ==

= Where can I find out more about this plug-in? =
You can find out more about the plug-in on [its plug-in page](http://catapultthemes.com/cookie-consent/).
= Is there a demo? =
Yep. Take a look at [the demo](http://cookieconsent.catapultthemes.com/). Reset the cookie by [going to this URL](http://cookieconsent.catapultthemes.com/?cookie=delete).
= Does this definitely cover me under the legislation? =
You have to make up your own mind about that or consult a legal expert.
= Where can I find out more about the UK laws regarding cookies? =
You will find more details of the regulations on the [Information Commissioner's Office site](http://www.ico.gov.uk/for_organisations/privacy_and_electronic_communications/the_guide/cookies.aspx).

== Screenshots ==
1. Notification bar along the top of the screen
2. Detail of notification bar on the bottom of the screen
3. Notification box in corner
4. Customization panel
5. Example settings page

== Changelog ==

= 2.3.11 =
* Added: activation notice
* Fixed: removed additional body margin for hidden bar

= 2.3.10 =
* Fixed: fixed security vulnerability identified by James Boughey

= 2.3.9 =
* Updated: removed tracking

= 2.3.8 =
* Updated: tracking class

= 2.3.7 =
* Updated: tracking class

= 2.3.6 =
* Updated: tracking class

= 2.3.5 =
* Fixed: removed debug code from main file
* Updated: tracking class

= 2.3.4 =
* Updated: minor admin error

= 2.3.3 =
* Added: Polylang support for more_info setting
* Added: setting to opt out of tracking
* Updated: tracking class

= 2.3.2 =
* Updated: incorrect text domains
* Updated: tracking class to 1.0.1
* Updated: removed unnecessary keys from wpml-config.xml

= 2.3.1 =
* Added: link to create privacy policy
* Added: tracking

= 2.3.0 =
* Added: GeoIP support (suggested by jeffrobbins)

= 2.2.5 =
* Fixed: dismissed notification bar obscuring content

= 2.2.4 =
* Fixed: correct escaping for more info URL

= 2.2.3 =
* Updated: stable tag

= 2.2.2 =
* Fixed: text domain in class-ctcc-metafield (spotted by mmmisiek)

= 2.2.1 =
* Updated: better escaping (suggested by djpaul)

= 2.2.0 =
* Added: optionally exclude pages and posts from displaying the cookie notification (suggested by jeffrobbins)
* Added: close notification on scroll option (suggested by jeffrobbins)
* Added: class name for more info link
* Fixed: x-close responsive margin
* Updated: allow shortcodes in notification text
* Updated: better settings sanitization

= 2.1.7 =
* Updated: admin styles

= 2.1.6 =
* Updated: wpml-config.xml file (thanks to vukvukovich)

= 2.1.5 =
* Fixed: added missing Italian translation files

= 2.1.4 =
* Fixed: replaced wpml-config.xml file

= 2.1.3 =
* Updated: tags

= 2.1.2 =
* Updated: translation list

= 2.1.1 =
* Fixed: missing images in admin

= 2.1.0 =
* Added: Italian translation (thanks to wocmultimedia)
* Added: wpml-config.xml file
* Added: filters on all content
* Fixed: x button colour set by button colour
* Updated: changed button and 'read more' elements' tabindex to 0
* Updated: x button now CSS to avoid missing Unicode character

= 2.0.12 =
* Fixed: added flex-shrink: 0 to prevent button overflowing container on mobile
= 2.0.11 =
* Fixed: syntax error in class-ctcc-public.php
= 2.0.10 =
* Added: priority on add_js
= 2.0.9 =
* Fixed: prevent JavaScript conflict by calling color picker script on non-plugin pages
= 2.0.8 =
* Updated: admin images in assets folder
= 2.0.7 =
* Added: Slovakian translation (thanks to lacike)
= 2.0.6 =
* Added: flat button option
* Added: Russian translation
= 2.0.5 =
* Fixed: notification hides correctly when stylesheet is dequeued
= 2.0.4 =
* Added: Polish translation (thanks to jraczynski for all items in this update)
* Updated: .pot file generated with l18n tool
* Updated: correct text domain in customizer.php
* Updated: removed spaces in translator functions
* Updated: plugin name translatable
= 2.0.3 =
* Fixed: more_info_target option not saving
* Fixed: button text getting cropped
* Changed: default position of accept button with notification text
= 2.0.2 =
* Fixed: retain settings from previous version
= 2.0.1 =
* Fixed: admin formatting
= 2.0.0 =
* Major rewrite
* Added: Choice of dismissal method - either on click by the user or timed
* Added: Choice of dismissal element - either button or 'x' close
* Added: Option to show the notification on the first page only - subsequent pages visited by the user will not display the message
* Added: Choice of position - either top or bottom bar, or floating in one of the corners of the screen
* Changed: Better translation support
* Changed: Better responsive support
* Changed: More customization options - including the ability to update styles from within the customizer
* Changed: Inherits your theme styles where possible
* Changed: The option to use an absolute or external URL to link to for further information
* Added: Set the cookie duration
* Added: Set the cookie version - updating a version will reset the cookie on all user sites
= 1.8.2 =
* Admin update

= 1.8.1 =
* Fixed empty space at top of screen when bar is located at the bottom of screen
= 1.8 =
* Move HTML down to accommodate notification bar rather than obscuring content
* Enqueues JS in footer
* Improved translation support
= 1.7.1 =
* Ready for WP 3.8
= 1.7 =
* Updates to settings page
= 1.6 =
* Moved JS to footer (thanks to Andreas Larsen for the suggestion)
= 1.5 =
* Switched the logic so that the bar is initially hidden on the page and only displays if user has not previously dismissed it.
* Gives a slightly better performance.
* Thanks to chrisHe for the suggestion.
= 1.4.2. =
* Policy page created on register_activation_hook now
= 1.4.1 =
* Tweak to ensure jQuery is a dependency
= 1.4 =
* This plug-in now uses JavaScript to test whether the user has dismissed the front-end notification in order to solve issues with caching plug-ins.
* Added configuration options for colour and position of bar.
* Set notification button and link to first element in tab list.
* Thanks to husobj for contributions and suggestions including localisation and enqueueing scripts and stylesheets
= 1.3 =
* Reinstated user-defined permalink field
= 1.25 =
* Minor admin update
= 1.24 =
* Fixed text alignment issue with Thesis framework (thanks to cavnit for pointing this one out)
= 1.23 =
* Minor admin update
= 1.22 =
* Minor admin update

= 1.21 =
* Added resources to Settings page
= 1.2 =
* Change title of Cookies page to Cookie Policy and removed option to change title
* Added trailing slash to Cookie Policy url (thanks to mikeotgaar for spotting this)
= 1.1 =
* Added default text to messages

== Translations ==
* English
* Polish
* Russian
* Slovakian
* Italian

== Upgrade Notice ==
Please note that the upgrade to version 2.x is significant. Although we've made every effort to ensure your settings are retained from previous versions, you may notice minor design differences to the notification bar.
