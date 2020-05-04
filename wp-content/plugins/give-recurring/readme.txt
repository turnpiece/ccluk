=== Give - Recurring Donations ===
Contributors: givewp
Tags: donations, donation, ecommerce, e-commerce, fundraising, fundraiser, paymill, gateway
Requires at least: 4.8
Tested up to: 5.4
Stable tag: 1.10.0
Requires Give: 2.5.5
License: GPLv3
License URI: https://opensource.org/licenses/GPL-3.0

Create powerful subscription based donations with the GiveWP Recurring Donation Add-on.

== Description ==

This plugin requires the GiveWP core plugin activated to function properly. When activated, it adds the ability to accept recurring (subscription) donations to various payment gateways such as PayPal Standard, Stripe, PayPal Pro, and more.

== Installation ==

= Minimum Requirements =

* WordPress 4.8 or greater
* PHP version 5.3 or greater
* MySQL version 5.0 or greater
* Some payment gateways require fsockopen support (for IPN access)

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install of Give, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "Give" and click Search Plugins. Once you have found the plugin you can view details about it such as the the point release, rating and description. Most importantly of course, you can install it by simply clicking "Install Now".

= Manual installation =

The manual installation method involves downloading our donation plugin and uploading it to your server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.10.1: April 17th, 2020 =
* Important: In this version we have patched an important security vulnerability. The fix prevents malicious actors from gaining unauthorized access to donor subscriptions where they would then be able to update subscription amounts or cancel the subscription. Please note: no credit card data or information was ever compromised as we never store card sensitive information to your database. Please update immediately and [contact our support](https://givewp.com/support/) if you have any issues or questions.

= 1.10.0: March 30th, 2020 =
* New: Added the Stripe payment gateway SEPA Direct Debit as a compatible gateway option to accept recurring donations through. This is a popular payment method in Europe and only requires a single IBAN payment field to process a one time or recurring donation.
* Fix: Resolved an issue with the "Subscription Payment Failed Email" formatting being incorrect for new installs and also a typo contained within the email's default content.

= 1.9.14: March 17th, 2020 =
* Fix: The Authorize.net eCheck renewals were recording under the incorrect Authorize.net payment option. Now when eChecks renew they will display properly as coming from the eCheck integration.

= 1.9.13: March 4th, 2020 =
* Fix: Resolved an issue with Currency Switcher in wp-admin where the "Subscription Value" would no appear in the default currency, not the currency the donor gave in.
* Fix: An update card error could occur when a donor attempted to update their subscription credit card after previously giving using Stripe. This has been resolved so now subscriptions are properly updated regardless of giving via Google, Apple, or Credit Card payments.

= 1.9.12: February 20th, 2020 =
* Fix: Razorpay had an issue which in some circumstances would create two subscription records incorrectly for donors giving through the gateway. This has since been resolved and now only a singular record will display in GiveWP.

= 1.9.11: February 7th, 2020 =
* Fix: Resolved an issue with Razorpay incorrectly clearing a donor's session once donated.

= 1.9.10: January 22nd, 2020 =
* Fix: Resolved an issue with Stripe ACH (via Plaid) not properly cancelling the subscription when a donor elects to unsubscribe from their recurring donation.

= 1.9.9: January 15th, 2020 =
* New: Added a "Blank Slate" for the Subscribers screen when no subscriptions are yet created.
* New: Added additional filters for developers to determine who has access to the Sync tool.
* Fix: Resolved an issue with renewal emails not being sent properly due to an incorrect permission check preventing webhooks from triggering the email.

= 1.9.8: December 18th, 2019 =
* Fix: Added checks in place to ensure that when a subscription is cancelled or completed that multiple emails are not sent to the donor or admin incorrectly.
* Fix: If using Stripe Checkout 2.0 then it does not support displaying an image within the checkout so we removed code that caused an issue with their API. The error returning from Stripe was "The Stripe Gateway returned an error while creating the Checkout Session" due to the image.
* Fix: Clicking on the "Resend Receipt" option on a renewal would not send the correct email template. Now it sends the "Renewal Receipt Email" template.

= 1.9.7: December 5th, 2019 =
* Fix: There was an issue with manually syncing a Stripe subscription that would bring in failed or on-hold payments as renewals incorrectly. This has been fixed so that only invoices marked as paid are brought into GiveWP as renewals.
* Fix: Resolved an invalid capability argument within the `give_subscriber` WP user role. This resolves issues with membership plugins unable to correctly use the role.
* Improvement: Optimized the subscription count query to reduce the number of database queries when viewing certain screens in WP-Admin.

= 1.9.6: November 15th, 2019 =
* New: The Razorpay gateway now supports Recurring Donations!
* Fix: Resolved an error with Authorize.net webhooks deactivating improperly when renewals were processed.

= 1.9.5: October 28th, 2019 =
* Fix: The "Cancel Subscription" button for the Stripe Checkout gateway  was not properly displaying for donors and admins who had subscribed using that gateway type.

= 1.9.4: September 19th, 2019 =
* New: Added support for Stripe's new Checkout 2.0 for recurring donations. Now you can accept recurring payments that support Strong Customer Authentication, Google and Apple Pay, Credit Cards and more!

= 1.9.3: August 20th, 2019 =
* Fix: Resolved a Stripe + Plaid recurring issue when certain donation amounts were passed it resulted in a 500 error due to a missing method name. Now all amounts will be properly passed to the Stripe API.
* Fix: Resolved an issue with Stripe properly cancelling recurring subscriptions for some configurations when the webhook is properly set in Stripe's dashboard.
* Fix: Resolved and added additional unit tests to ensure that releases have more reliable automated testing.

= 1.9.2: July 30th, 2019 =
* New:  If a donor updates their subscription amount the note that is displayed for the admin in the subscription profile is improved by recording the time and date that the renewal amount was updated.
* Fix: Corrected an issue with Stripe preventing donors from updating their subscription amount properly. Please update to this version or higher if you're using Stripe with Recurring Donations so donors can successfully cancel or update their subscription amounts.

= 1.9.1: July 12th, 2019 =
* Fix: Authorize.net recurring donations were not displaying the subscription giving period correctly. For instance, monthly subscriptions were listed incorrectly as "One Time" and weekly subscriptions are listing an empty string for the period. The actual subscriptions themselves were created correctly, but they appeared incorrectly to admins / donors in Recurring version 1.9.0.

= 1.9.0: July 11th, 2019 =
* Important: This update requires Give 2.5.0+ and the latest versions of add-ons to work properly. Please perform a site backup and ensure you have activated your license keys to ensure your add-ons update properly before updating.
* New: Added "Quarterly" as a giving option for admins to collect.
* Optimization: The add-on tables are now only registered when the plugin is activated or updated.
* Tweak: An additional check is now in place to see whether the subscription history page is set or not. If it is set then the donor will be able to update the subscription amount via a link in recurring donation receipt.
* Fix: Resolved an issue with native WP comments incorrectly displaying in subscription comments.
* Fix: The Recurring goal formats for donation forms was not properly displaying past 20 subscriptions.
* Fix: The add-on now properly catches and updates payments that return a declined transaction responses from Authorize.net when the initial transaction is batch processed.
* Fix: The add-on now plugin displays dates properly when using a localized date format.
* Fix: Stripe now processes the "Renewal Failure" webhook event and updates the corresponding subscriptions properly.
* Fix: The "Donation Amount" text would incorrectly display when closing a donation form modal window.
* Fix: The recurring integration for Authorize.net was improperly throwing a fatal error when receiving a webhook for a payment that already existed.

= 1.8.13: April 30th, 2019 =
* Fix: Resolved an issue with Stripe payment gateway renewal dates not being properly being set when a new renewal is processed via Stripe's new API. The renewal dates for all Stripe gateway offerings will now reflect properly in your subscriptions dashboard within wp-admin.

= 1.8.12: April 17th, 2019 =
* Tweak: Adjusted how the Give Core Subscription API endpoint returns results so that amounts are formatted properly and also includes the donation form title and currency code. This is useful for Zapier and other API integrations.

= 1.8.11: April 3rd, 2019 =
* New: Added manual subscription sync functionality to the Stripe + Plaid gateway.
* New: Added an additional validation method to ensure that a donor attempting to donate recurringly through a non-recurring supported gateway receives a useful message preventing the subscription attempting and receiving an API error or processing as a one-time donation incorrectly.
* Fix: Resolved an issue with webhooks being properly recorded for the Stripe + Plaid gateway.

= 1.8.10: March 13th, 2019 =
* Fix: Stripe fix to ensure existing donors who process a recurring donation with a new card or a new donor who use their card first time within the Stripe Checkout modal don't receive a "No Such Source" error.

= 1.8.9: March 6th, 2019 =
* Fix: Added additional checks for repeat donors with single and recurring donations to prevent "No Such Source" errors from displaying and preventing donations from that repeat donor. Please update the Stripe add-on the conjunction with this add-on.

= 1.8.8: March 1st, 2019 =
* Fix: Resolved the "Source not found" issue for Stripe Recurring using the Checkout modal option.

= 1.8.7: February 26th, 2019 =
* Fix: Ensure that new installs properly set the column types for subscription amounts. Previously if a admin installed recurring 1.8.5 - 1.8.6 the column type would have an incorrect decimal number which resulted in incorrect amounts displayed. This update includes an auto-upgrade check to set the column type correctly.

= 1.8.6: February 22nd, 2019 =
* Fix: Record renewals properly when process through Plaid + Stripe.
* Fix: Added support for date formats with translated strings in them.
* Fix: Ensure that the original payment source for Stripe subscriptions is not updated incorrectly when donating to multiple campaigns.

= 1.8.5: February 7th, 2019 =
* New: Added a new email template tag `{subscriptions_link}` that takes donors to their subscription history.
* Fix: Links to subscription details from receipts now will never expire or require a login. This is to improve the donor experience and not require them to confirm their email or login.

= 1.8.4: January 31st, 2019 =
* Fix: Adjusted the column types for recurring amounts to resolve decimal amount formatting when using non-US separators.
* Fix: Removed code preventing Mollie annual (yearly) donations because the gateway now supports it.

= 1.8.3: December 13th, 2018 =
* New: There is now a new admin email that can be enabled to notify when renewals are successfully processed.
* New: We also added a new admin email to notify when subscriptions have been cancelled.
* Tweak: Improved the donation listing page to be more performant when Recurring Donations is active.
* Fix: Ensure donation renewal frequencies are translatable.
* Fix: We improved the search within the subscriptions listing page within wp-admin to return more accurate results.

= 1.8.2: November 8th, 2018 =
* New: Added support for sorting the subscriptions list columns withing WP-Admin.
* Fix: PayPal Pro (Payflow) issue with syncing donations resolved due to incorrect conditional check.
* Fix: Corrected issue with creating subscription tables on WP Multisite installs when activating network wide.
* Fix: Display proper singular and plural donation counts when only one donation is present in WP-Admin donor details.
* Fix: Stripe was incorrectly cancelling subscriptions when the period ends rather than waiting until the lifetime of the subscription was due.

= 1.8.1: October 4th, 2018 =
* New: PayPal gateways are now updated to send the invoice prefix to the gateway so you can more easily differentiate between Give donation transactions and other transactions.
* Tweak: Adjusted how Stripe completes recurring donations using theie new API cancel at plan end functionality.
* Tweak: Removed usage of AJAX to speed up updating the recurring text when changing levels.
* Fix: Resolved an issue where the helpful text next to the final donation amount wasn't displaying properly on page load and changing gateways.
* Fix: Resolved amount formatting and position in recurring text when using a comma for your decimal and thousands separator.
* Fix: Corrected a sessions error occuring when donors would access their subscription history page.
* Fix: Important - if you are using PayPal Payments Pro (Payflow) please ensure in PayPal your IPN endpoint points to "https://mywebsite.com/?give-listener=IPN". This ensures that if you are using PayPal Standard alongside Payflow then the IPN will properly work for both endpoints and your renewals will properly be recorded in Give via IPN.
* Fix: PayPal Payments Pro (Payflow) will not incorrectly sync in non-completed renewals when manually syncing.
* Fix: PayPal Payments Pro (Payflow) will not sync "completed" subscriptions to "expired" when manually syncing.

= 1.8.0: September 6th, 2018 =
* New: Donors now have the ability to adjust their subscription amounts.
* New: Added the Authorize.net eCheck recurring payment gateway.
* New: Added a "Subscriptions" section to the donor details screen.
* New: You can now customize Recurring email headings directly within the specific emails found at WP-admin > Settings > Emails.
* New: There is a new subscription notes area where the system and users can add notes similar to payments.
* New: Recurring now has it's own meta table for developers to store subscription related metadata.
* New: Provided a way for users to update the transaction ID of a subscription manually.
* New: When a subscription is created at Authorize.net the donor's email addresses is passed with the payment.
* Fix: Stripe 3D Secure payments are now supported for subscriptions.
* Fix: Ensure that two donations are not created whenever a user gives a recurring donation while using PayPal Pro.

= 1.7.2: July 30th, 2018 =
* Important: You must be running Give Core 2.2.0+ in order to use this add-on update. Please update Give Core to the latest version prior to completing this update.
* Fix: There is an upgrade routine to correct renewal donations that are not properly associated with the correct payment level.
* Fix: If for some reason the parent transaction ID from the gateway isn't properly recorded you can now edit it manually.

= 1.7.1: July 5th, 2018 =
* Fix: Resolved conflict wirenewal donations for recurring donations are not associated with the correct payment levelth Stripe 2.0.6+ causing the Update Payment Method for recurring to have a JS error.
* Fix: Ensure that the word "Donation" doesn't appear incorrectly after the recurring donation checkbox.

= 1.7.0: June 28th, 2018 =
* New: Donors now have the ability to adjust the credit card information (within supported gateways) for their subscriptions so that if a card expires or they would like to use a new card they can update it through your website.
* New: Additional emails have been added so that you can send your donors reminders when their subscription is renewing, completing, and cancelling.
* New: The export donation tool will now export subscription related information such as billing period, times, and frequency.
* New: Now you can modify your donation form goals to only account for subscriptions. For example, "$50 per month raised toward a goal of $500 per month".
* New: Added an indication at the bottom donation amount field that the donation they are going to give is recurring to improve the donor's awareness and experience.
* Tweak: Increased the IPN window time for PayPal Pro to account for delays of renewals being sent from PayPal to your website so they can record properly.
* Fix: Resolved an error with Stripe's webhook preventing renewals from being processed properly. Webhooks now process renewals correctly and payments are added again. No more manual syncing is necessary.
* Fix: The "Renewals" payments list screen is now properly accessable from the payments view in wp-admin.
* Fix: The {date} email tag was returning the date of the original subscription payment within the renewal email notification. The email tag now displays the appropriate renewal date.
* Fix: The email access verification text for the subscriptions page displays different content than the donation history page as to not confuse the donor or redirect them to the donor history page.
* Fix: In wp-admin when drag-and-dropping donation form levels the recurring options could be lost.
* Fix: Removed the "#" prefix from the subscriptions ID within the wp-admin subscription list table to better match Give Core.
* Fix: Improved the way recurring fields are displayed within the donation form admin screens on fresh installs to help admins setup their donation forms.
* Fix: Admin UI improvement for the Subscription Information metabox within the payment details screen. If there were many subscriptions the scrolling was getting thrown off.
* Fix: Recurring now prevents donation forms from sending more than 20 metadata keys to Stripe. The limit is 20 and if it goes over that limit an API error would occurr.

= 1.6.1: May 10th, 2018 =
* Fix: Resolved Stripe Error with Form Field Manager long labels or meta keys being rejected by Stripe's API. They are now trimmed to the appropriate length before being sent over.
* Fix: Resolved a PHP notice from displaying when deleting a donation.
* Fix: Updated several SQL queries to be using Give 2.0+ paymentmeta table.

= 1.6: May 2nd, 2018 =
* New: Added the ability for admins to set recurring frequencies much more flexibly. It's now easy to set quarterly, semi-annually, bi-monthly, and many other giving frequencies.
* New: Added the ability to accept recurring donations through Stripe + Plaid (ACH).
* New: Added the ability to accept recurring donations through Stripe's Google and Apple Pay integration.
* New: Exporting donations in Give Core 2.1 now has support to added columns for recurring donation data such as payment type (one time, renewal, subscription).
* New: Stripe now passes meta data to the gateway when a recurring subscription is created.
* New: Added pagination to the [give_subscriptions] shortcode for donors with many subscriptions.
* Fix: Improved caching strategy for subscription based queries.
* Fix: Adding Multiple Shortcodes on same page should not ask for login multiple times.
* Fix: Display frequency tag correctly for PayPal Pro donations.
* Fix: The {subscriptions_completed} email tag should output correct data for one time donations.

= 1.5.7: February 20th, 2018 =
* Tweak: The {subscription_frequency} now outputs "one-time" for non-recurring donations rather than nothing.
* Fix: A PHP notice when making a donation would display if you have WP_DEBUG turned on.

= 1.5.6: February 19th, 2018 =
* Fix: Custom amounts can now be either recurring or non-recurring based on your giving preference.
* Fix: The "Subscriptions Page" setting was missing in Give's general settings due a change in the previous version. It has now been restored.
* Fix: PHP warning "invalid subscription_id error" while donating and using the {subscription_frequency} email tag.

= 1.5.5: February 1st, 2018 =
* Tweak: Improved internationalization of the donor's choice checkbox language string.
* Fix: Improved SQL query structure for DB upgrade having issues on some database environments.
* Fix: PHP notice when clicking on a custom amount level.

= 1.5.4: January 12th, 2018 =
* Fix: The update routine supposed to be release in 1.5.3 wasn't properly merged therefore we need this new release to ensure it goes out to everyone as expected.

= 1.5.3: January 3rd, 2018 =
* Fix: Resolved issue with upgrade routine not displaying and running correctly for older versions updating to the latest version.
* Fix: The {subscription_frequency} now returns the text "One Time" when it's not a recurring donation rather than nothing at all.

= 1.5.2: January 3rd, 2018 =
* New: There is now a hidden field that can be used by other plugin authers to determine whether the given donation was recurring or not.
* Fix: Currency formatting was incorrect (too many decimal values would show) for the recurring helper text when choosing a recurring level.

= 1.5.1: December 26th, 2017 =
* New: Added bulk actions within the subscriptions listing page in wp-admin.
* Fix: An incorrect number of decimals would display if using admin defined recurring donations with the helper text enabled.
* Fix: The wp-admin conditional field logic was incorrect for set donation forms recurring option fields. This would lead to admin confusion when creating new recurring forms and has been resolved.
* Fix: If using admin choice multi-level donations certain non-recurring levels would be incorrectly interpretted as recurring which could cause donor confusion on the receipt page.
* Fix: Stripe would send the subscription cancelled email twice if the donor cancelled the subscription themselves and when the subscription cancelled in the gateway. This has been resolved so that the email will only send once when the subscription is cancelled.
* Fix: When hovering over a subscription report the income was missing a currency symbol.
* Fix: Minor PHP notices when making a subscription donation if using certain version of Give core.
* Tweak: Various updates for the upcoming Give 2.0 release.
* Tweak: Added page title to the "Recurring Donations" page on the wp-admin page listing screen.

= 1.5: November 21st, 2017 =
* New: Now you can provide your donors with the option to select their recurring subscription giving frequency. This provides more flexibility to their giving choices and should help increase recurring subscription conversions.
* Fix: Renewals would show incorrectly when filtering on the donations listing page.
* Fix: Multi-level recurring forms with dropdown were not updating the recurring amount description correctly.
* Fix: Conflict with manual donations and 1.4.x of recurring.

= 1.4.2: November 21st, 2017 =
* Fix: Resolved issue with PHP 5.2 - 5.4 causing PHP error when the plugin attempts to load.

= 1.4.1: November 20th, 2017 =
* Fix: Resolved hook priority issue preventing "Sync" and "Cancel" subscription options from displaying properly in wp-admin.

= 1.4: November 20th, 2017 =
* New: You now have the ability to add renewal payments manually to subscriptions within the subscription details screen in wp-admin.
* New: Subscription profile IDs are now linked to the gateway subscription profile for easy access.
* New: Added foundation of PHP unit tests for easier bug detection, increased code coverage, and quality.
* New: Added a filter for PayPal Payflow IPN window timeframe called "give_recurring_payflow_ipn_window_timeframe" and increased the amount of time threshold to better match recurring payments.
* New: Added explanatory text for recurring admin choice forms that appears below levels to make final donation more clear to the donor.
* New: Recurring now adds an API endpoint to the Give core API for developers to work with.
* New: Admins now have the ability to search through the subscription list as well as filter by date and donation form.
* New: Moved the "Subscriptions Page" settings field under Settings > General it is generated by default for new installs. If you haven't set it yet, it's a good idea to set it.
* Tweak: Adjusted code to use GIVE_RECURRING_* constants so developers can flexibly override them for various requirements. Thanks @JJJ
* Tweak: Removed usage of Give core deprecated hooks causing PHP notices when debug is enabled.
* Tweak: Donation History page now has a "recurring" indicator for donors to more easily which donations are recurring compared to non-recurring.
* Tweak: Notices that display are now standardized to use the Give core notices class.
* Tweak: Changed the default option for "Donor's Choice" checkbox to be unchecked by default.
* Tweak: Updated the output for when a donation form is recurring admin defined set donation so that the "Donate Now" button always appears below the main.
* Tweak: Authorize.net now uses webhooks for the main pingback mechanism rather than the unreliable Silent Post URL which is now the fallback.
* Tweak: The Renewal reports  graph now labels data as "Renewals" rather than "Subscriptions".
* Tweak: Improved the settings page layout to be better organized.
* Fix: The dashboard stats widget and other reports not properly count renewal donations amounts and number of donations properly.
* Fix: PayPal Pro Gateway (NVP API) - Request body bug with sending state in place of country causing API issues. Thanks @wesdunn
* Fix: Added security with additional nonces throughout plugin.
* Fix: When a custom recurring amount is donated for a multi-level form, the plugin didn't properly show the "custom" as the label in Donations (admin dashboard), instead it showed the label of the first level of the multi level donation form.
* Fix: A donation form's recurring options were not correctly show/hiding sub-fields on the edit donation form screen within wp-admin.
* Fix: If special characters were sent to PayPal Pro in the PROFILENAME field an error would return. Now the field is sanitized so no special characters should ever be sent to prevent this error.
* Fix: Removed duplicate Give API fields appearing under the user profile.
* Fix: Preventing undimissible JS alert when setting a recurring donations times to 1.
* Fix: Recurring specific email tags were not working in the core donation receipt email.
* Fix: When a renewal donation is added, it is now being properly reflected in the list at Donations > Donors > Total donated in both the number of donations or the total amount donated columns.
* Fix: Renewal donations are now properly displayed when filtering in wp-admin within the donations listing screen.
* Fix: Pagination reliability is now fixed for when you page through the subscriptions list screen under Donations > Subscriptions in wp-admin.
* Fix: The Renewal Donations report now matches the Income report found in Give 1.8+.
* Fix: Issue where some donors couldn't cancel their subscriptions due to being a guest on the site.
* Fix: If you disabled guest donations on a donor's choice recurring form with email access disabled, the registration/login fields would be still toggle, but would be incorrectly required with recurring unchecked.
* Fix: Authorize.net - a renewal would incorreclty be created a day after the first donation for some subscriptions due to a delay with the Silent Post URL.
* Fix: The plugin now will delete all data if the Give core settings is set to delete all data on uninstall.
* Fix: The give_is_form_recurring() would return false positives incorrectly.

= 1.3.1 =
* New: Recurring gateways that add API keys now use Give core's api_key field type to help prevent viewing of API key content.
* Fix: Prevent errors when attempting to activate plugin with an older unsupported Give version or Give is not active at all.
* Various bug fixes and code improvements.

= 1.3 =
* New: Introduced the Subscription Synchronizer tool. Subscription can get out of sync with the gateway for a variety of data. This tool allows you to connect to the specific subscription's gateway and sync the transactions and subscription information so that the data is no longer out of sync.
* New: A new subscriptions column now appears under WP-Admin > Donations > Donors so you can quickly see who is a subscriber and how many subscriptions they have. You can also click on the it to see that donor's specific subscriptions.
* New: is_subscription_valid() method added to Give_Recurring_Gateway().
* Fix: Authorize.net now properly processes the MD5 Hash option via Silent Post URL. This means subscription renewals should come in fine now from the Authorize.net API with this option enabled.
* Fix: PayPal Payments Pro now properly process Instant Payment Notifications from PayPal to accept renewals. Use the synchronizer tool if your current subscriptions are out of date.
* Fix: Renewal emails now have a proper heading for the receipt that is emailed.
* Fix: Don't allow recurring checkboxes to bump to two lines for certain themes like TwentySeventeen.
* Various bug fixes and minor text updates.

= 1.2.3 =
* New: Added functionality to require that Give core be active in order to use the plugin. #301
* Fix: Logs filling with "Error Processing IPN Transaction" incorrectly for non-recurring payments. #293
* Fix: Refactored JS for how the recurring checkbox toggle displays required fields. - https://github.com/impress-org/give-recurring-donations/pull/303
* Fix: Authorize.net Subscription names need to be limited to 50 characters. #298

= 1.2.2 =
* New: Pass the billing address information to Authorize.net about the donor when creating the subscription if present. #271
* New: Better error reporting for Authorize.net. #271
* New: Method to confirm whether the Payflow recurring feature is enabled at the gateway to better ensure the subscription is created successfully. #288
* New: Subscription status indicator icon added to Donations > Subscriptions > Subscription Details page. #132
* Fix: Intermittent issue with the donor's subscription cancel option displaying properly due to an issue with the PayPal Payflow gateway cancel logic. #260
* Fix: Resolved translation strings without textdomains or incorrect text domains for better translations. #275

= 1.2.1 =
* Fix: License activation notice would improperly display even though the license was activated when the admin viewed the Recurring Donations settings tab. #265

= 1.2 =
* New: Support for PayPal Payments Pro (Payflow). #256
* New: Support for the PayPal REST API. #224
* New: The ability to filter subscriptions by status under Donations > Subscriptions
* New: The ability to edit a number of subscription detail fields including profile ID, expiration date, and subscription status
* New: Improved UI for [give_subscriptions] shortcode and also customizable attributes. #143
* New: Filter "give_recurring_multilevel_text_separator" to control multi-level separator between level text and recurring duration text. #142
* New: Added span wrapped tag to recurring language in multi-level buttons for easier styling. #142
* New: Allow the admin to delete or cancel renewal in the subscription details. #204
* New: New form for adding manual renewal payments added to individual subscription details admin page. #205
* New: Stripe now supports refunding and cancelling subscriptions from the donation details screen. #239
* Tweak: Consolidate subscriptions listing column & improved UI in WP-admin under Donations > Subscriptions. #251
* Tweak: Subscription donations are now referred to as "Renewals" for better clarity and easier understanding of the status. #215
* Fix: When Give is opening the donation form in a modal, the "Make this donation recurring" Donor's choice checkbox appears before the button. #253
* Fix: Properly show/hide the "Recurring Opt-in Default" field in the admin when toggling recurring options
* Fix: Incorrect false negative in conditional check for whether a donation form is recurring is_recurring() method
* Fix: Issue with checking if a Transaction payment is a Subscription parent payment which was causing the recurring label to be incorrectly output on the parent payments' transaction. #214
* Fix: Reports filter field not displayed on the Recurring reports sections. #211
* Fix: Reports tooltips not properly formatted. #217
* Fix: Donor details renewal stat incorrect. #245
* Fix: Renewal date incorrectly calculating for certain donation form configurations. #201
* Fix: Multiple Donors Choice Recurring Forms cause first Checkbox to always be selected. #254
* Fix: Require the last name field for Authorize.net - the gateway requires that the last name be passed when creating subscriptions. #262

= 1.1.1 =
* Fix: PHP fatal error for some hosting configurations "Can't use function return value in write context". #192
* New: New link to plugin settings page with new base name constant. #190

= 1.1 =
* New: Don't require a login or registration for subscription donations when email access is enabled. #169
* New: Show a login form for [give_subscriptions] shortcode for non-logged-in users. #163
* New: Donation form option for admins to set whether subscription checkbox is checked or unchecked by default. #162
* Tweak: Provide Statement Descriptor when Creating Stripe Plans. #164
* Tweak: Don't register post status within Recurring; it's already in Core. #174
* UX: Added scrolling capability to subscription parent payments' metabox because it was getting too long for ongoing subscriptions. #130
* Fix: PHP Fatal error when Stripe event is not returned. #176
* Fix: PayPal Pro Gateway message issue "Something has gone wrong, please try again" response . #177
* Fix: Blank notice appears when updating / saving settings in Give. #171

= 1.0.1 =
* Fix: Security fix added to prevent non-subscribers from seeing others subscriptions within the [give_subscriptions] shortcode.

= 1.0 =
* Initial plugin release. Yippee!
