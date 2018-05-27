=== GDPR Cookie Consent ===
Contributors: webtoffee,markwt
Donate link: 
Tags: eu cookie law, GDPR, cookie law, cookie consent, eu privacy directive, privacy directive, cookies, privacy, compliance
Requires at least: 3.3.1
Tested up to: 4.9.6
Stable tag: 1.5.6
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple way to get GDPR Cookie Consent as per EU GDPR/Cookie Law regulations. Style it to match your own website.

== Description ==

NOTE: INSTALLING THIS PLUGIN ALONE DOES NOT MAKE YOUR SITE GDPR COMPLIANT. SINCE EACH SITE USES DIFFERENT COOKIES, YOU MAY NEED TO ENSURE YOU HAVE NECESSARY CONFIGURATIONS IN PLACE.

Our plugin will help you to become GDPR compliant with following features. 

- Plugin will show a notice with Accept and Reject options. By default the cookie value will be set to 'null'. If the user clicks 'Accept' button the value with be changed to 'yes'. IF the user clicks on 'Reject' the value will be set to 'no'. Your developer can check this value to set a cookie

- Admin can add cookie details from the backend. The list of cookies can be displayed in your cookie policy page by using a short code


This plugin adds a subtle banner to your website either in the header or footer so you can show your compliance status regarding the new EU Cookie Law.


You can fully customise the style so it fits in with your existing website- change the colours, fonts, styles, the position on the page and even how it behaves when you click "accept".


You can choose to make the cookie bar disappear after a few seconds (completely configurable) or to accept on scroll (an option available under Italian law).


It also has a Cookie Audit module so you can easily show what cookies your site uses and display them neatly in a table on your Privacy & Cookies Policy page.


This plugin supports WPML and qTranslate so your translation needs are covered.


Features:

* Fully customisable to look just like your own website's style: customise the colours, styles and fonts
* Put the cookie bar in either the header or the footer
* (Optional) accept cookie policy if the user scrolls
* (Optional) automatically close the cookie bar after a delay (delay is configurable)
* (Optional) cookie bar can be permanently dismissed or accessible through a "show again" tab
* (Optional) "show again" tab is fully customisable including position shown on page and styles
* "Cookie Audit" shortcode to construct a nicely-styled 'Privacy & Cookie Policy'
* WPML compatible
* qTranslate support

[Read more about the EU Cookie Law](http://cookielawinfo.com/ "More information about the EU Cookie Law")

<blockquote>

= GDPR Cookie Consent Premium Version Features =
<ul>
	<li>Manage list of cookies ( Name, CookieID, Description, Duration, Type, Category, Header Script, Footer Script)</li>
	<li>Manage Cookie Categories</li>
	<li>Allow to display Cookie Settings popup where site visitors can opt-in or give consent to Cookie Categories</li>
	<li>Fully customisable to look just like your own website’s style: customise the colours, styles and fonts</li>
	<li>Put the cookie bar in either the header or the footer</li>
	<li>(Optional) accept cookie policy if the user scrolls</li>
	<li>(Optional) automatically close the cookie bar after a delay (delay is configurable)</li>
	<li>(Optional) cookie bar can be permanently dismissed or accessible through a “show again” tab</li>
	<li>(Optional) “show again” tab is fully customisable including position shown on page and styles</li>
	<li>“Cookie Audit” shortcode to construct a nicely-styled ‘Privacy & Cookie Policy’</li>
	<li>WPML compatible</li>
	<li>qTranslate support</li>
</ul>

For complete list of features and details, Please visit <a rel="nofollow" href="https://www.webtoffee.com/product/gdpr-cookie-consent/">GDPR Cookie Consent Premium Plugin</a> for more details

</blockquote>

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the `cookie-law-info` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to "Cookie Law Info / Cookie Law Settings" to configure the banner with your own text, colours and styles

To set up your Privacy & Cookie Policy Page:

1. Add descriptions of the cookies your site uses. Go to "Cookie Law Info / Add New".
2. Add a new page called e.g. Privacy and Cookie Policy
3. Add the [cookie_audit] shortcode to your Privacy & Cookie Policy Page

Cookie table shortcode usage:

	[cookie_audit]
	[cookie_audit style="winter"]
	[cookie_audit not_shown_message="No records found"]
	[cookie_audit style="winter" not_shown_message="Not found"]
	
	Parameters:
	
	style (optional) - choose one of several table styles included with the plugin. Styles included: simple, classic, modern, rounded, elegant, winter (styles are cAsE sensitive). Default style applied: classic.
	not_shown_message (optional) - if no cookie records are found, display this text. Default is blank (i.e. no message shown).
	

Delete header cookie usage:

	[delete_cookies]
	[delete_cookies linktext="delete cookies"]
	
	Parameters:
	
	linktext (optional) - the text shown in the link. Default is "Delete Cookies".


== Frequently Asked Questions ==

There's a lot of help available on the main plugin website. See:

http://cookielawinfo.com/faq
http://cookielawinfo.com/user-guide
http://cookielawinfo.com/support

= The cookie header isn't displaying =
First check you have installed the plugin and have activated it in the plugins panel.
To check if it is switched on or not, go to 'Settings / Cookie Law Info' and then enable the header by setting 'Display cookie bar?' to 'yes'.

= The header doesn't work on my browser =
Please report a bug on the support forum. Be sure to include the following information:

* Your URL (I will need this in order to help you!)
* WordPress version e.g. 4.2.2 (found in bottom right hand corner of dashboard)
* Browser e.g. FireFox, Chrome, IE
* Describe the problem

The more information you give, the quicker I can respond.

= What does this plugin do? =
This plugin will:

1. Add a banner to the top/bottom of all pages on your website, notifying the visitor that you have a clearly defined privacy and cookie policy.
2. Allow you to record which cookies your site uses, and:
3. Neatly display a list of these cookies (via a shortcode). You can put this list on your Privacy & Cookie Policy page, for example.

= Does this plugin block all cookies? =
No. This plugin restricts cookies by optionally loading the scripts. For this you have to add all cookies using the add cookie feature. It is not currently technically possible to completely block all cookies on your WordPress website without first updating all plugins that use cookies in some way. That is beyond the scope of any single plugin.
A more realistic approach for WordPress website owners is to move the scripts that place cookies( for eg Google Analytics tracking code) to the GDPR Cookie Consent plugin and then allow visitors to control it by giving consent.


= Do visitors now have to accept/refuse cookies in order to use websites? =
No. Only the 'necessary' cookies will be loaded till the user gives consent. 

= Does this plugin stop cookies from being stored? =
We provide you the facility to add cookies and the corresponding scripts (This works if the cookies are placed when a script is added to the site header or footer as in the case of Google analytics, Hotjar etc..). In our premium version, visitors can reject the category of cookies they don't want to get installed. In such cases all cookies in that category will be blocked for the user. In the free version you can still block the scripts but doesn't have category level granularity.

But if another plugin adds scripts or places a cookie then we do not have any control. We have no way of knowing what plugins you use or how they work, so blocking/deleting cookies on a WordPress website/blog would possibly/probably break your site. Larger websites with huge budgets may well invest in such technology, but this plugin aims to help you provide a certain level of compliance without taking drastic action. 

= Does this plugin guarantee that I comply with this law? =
No.
As a generic plugin there's no way we can know anything about your specific circumstances. It can be used as part of an overall plan of action to comply, but just installing it and doing nothing more does nothing to help you. In all cases, you need to assess your own website's use of cookies and decide an appropriate course of action. If you are looking for specialist legal advice relating to your website you should always consult a lawyer.
See http://cookielawinfo.com for more information on what is required.


== Screenshots ==

1. Header (with default styles)
2. Admin panel
3. Admin panel - styling the form with colour pickers
4. Header (with custom styles)

== Changelog ==

= 1.5.6 =

* Changed Reject button colour for Open URL and Close Header options.
* Padding for message header
* Audit table mobile view compatible

= 1.5.5 =
* GDPR compliance updates.

= 1.5.4 =
* Tested OK with WordPress 4.9.5
* GDPR compliance updates.

= 1.5.3 =
* Bug fix: Buttons now handle apostrophes correctly
* Bug fix: Added <tr> to table head for [cookie_audit] table for W3 Validator (thanks to davidebabylonia for finding and suggesting the solution)

= 1.5.2 =
* Minor bug fix: adds version number to cli-admin.css

= 1.5.1 =
* Bug fix: HTML5 validation fix for shortcode links (thanks to davidebabylonia)
* Added JavaScript version number for greater compatibility

= 1.5 =
* Major update: the cookie bar is now inserted into the page via wp_footer rather than using jQuery (for better performance and greater browser compatibility)
* Update: if the cookie bar is in the header, there is now an option to fix the bar to the header using position:fixed

= 1.4.3 =
* jQuery 'reload' bug fix on accept

= 1.4.1 =
* Bug fix: fixed browser compatibility issue in cookielawinfo.js

= 1.4 =
* New feature: accept policy & close cookie bar on scroll (an option available under Italian law)
* New feature: if cookie bar is set to header it is fixed to the top of the screen (using CSS "position:fixed")

= 1.3.2 =
* Bug fix: changed filename from wpml.xml to wpml-config.xml

= 1.3.1 =
* Fixing header/SVN tagging issue

= 1.3 =
* Adding WPML support (wpml.xml)

= 1.2.2 =
* Bug fix: removed extra '{' from cli-tables.css
* Bug fix: fixed cookie_button shortcode text bug
* Modified help pages, text and contact information

= 1.2.1 =
* Added plugin settings page link to plugins.php
* Bug fix: custom posts bug affecting some other plugins

= 1.2 =
* Removed 3rd party JavaScript "jQuery.cookie" which can cause issues with certain versions of Apache server
* Added native JavaScript cookie getter/setter
* Removed JavaScript debug routine
* Replaced JavaScript 'eval' with JSON.parse() for improved security: requires IE8+ (all other browsers fine though- who would have thought?)
* Improved JavaScript performance and compatibility by removing global variables and running as inline function

= 1.1 =
* New feature: auto-hide cookie bar after (configurable) delay
* New feature: added responsive design to cookie audit table (thanks to Mark Wiltshire)
* Upgrade: now using WP3.5 color picker
* Bug fix: Cookie Audit table now shows maximum of 50 posts (was 10, which was a bit restrictive)
* Bug fix: Cookie Law Info now only visible to admins
* Bug fix: fixed typo on Dashboard help section for the [delete_cookies] shortcode
* Bug fix: fixed "invalid header" bug
* Performance enhancement: removed jQueryUI from admin panel and added custom (slimline) code
* Performance enhancement: compressed CSS a bit
* Performance enhancement: cookie audit CSS is now only downloaded on the page on which it is needed

= 1.0.3 =
* Fixed bug where JavaScript generated an http 404 error.

= 0.9 =
* Improved design & appearance
* Cookie Law Info bar can be shown in header or footer
* Customise fonts
* New animations on page load / close header
* Option to switch off "show again tab" - or position it anywhere horizontally in the header or footer. Additionally the styling has been improved and you are now able to customise the message.
* New button styles: better styling and effects, greater control
* Customise your message using HTML and 5 shortcodes with quick-start default options
* "Cookie Audit" module - document the cookies your site uses then display them in your privacy policy via a shortcode
* Enhanced dashboard
* Enhanced help section
* Refactored codebase, improved jQuery performance
* Bugs fixed: no more slashes in Message Box

= 0.8.3 =
* First public release.

== Upgrade Notice ==

= 1.5.6 =

* Changed Reject button colour for Open URL and Close Header options.
* Padding for message header
* Audit table mobile view compatible