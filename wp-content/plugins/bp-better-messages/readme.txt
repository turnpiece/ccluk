=== BP Better Messages ===
Contributors: wordplus
Donate link: https://www.wordplus.org/donate/
Tags: BuddyPress, messages, bp messages, private messages, pm, chat, live, realtime, chat system, communication, messaging, social, users, ajax, websocket
Requires at least: 4.0
Tested up to: 5.1.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

**BP Better Messages** – is a fully featured replacement for standard BuddyPress Messages and can run without BuddyPress as well.
Plugin is fully backward compatible with BuddyPress Messages.

**[More Info & Demo](https://www.wordplus.org/downloads/bp-better-messages/)**

https://www.youtube.com/watch?v=WdsZb8SB0S8

**Improved features comparing to standard system:**

* AJAX or WebSocket powered realtime conversations
* Reworked email notifications ([More info](https://wordpress.org/plugins/bp-better-messages/faq/))
* Fully new concept and design
* Files Uploading
* Embedded links with thumbnail, title, etc...
* Emoji selector (using cloudflared CDN to serve EmojiOne)
* Message sound notification
* Whole site messages notifications (User will be notified anywhere with small notification window)
* Mass messaging feature

**Supported features from standard messages system:**

* Private Conversations
* Multiple Users Conversations
* Subjects
* Searching
* Mark messages as favorite

**WebSocket version:**

WebSocket version is a paid option, you can get license key on our website.

We are using our server to implement websockets communications between your site and users.

Our websockets servers are completely private and do not store or track any private data.

* **Significantly** reduces the load on your server
* **Instant** conversations and notifications
* Messages Delivery Status (sent, delivered, seen)
* Typing indicator (indicates if another participant writing message at the moment)
* Online indicator
* Works with shared hosting
* More features coming!

[Why WebSockets are a game-changer?](https://pusher.com/websockets)

**[Get WebSocket version license key](https://www.wordplus.org/downloads/bp-better-messages/) | [Terms of Use](https://www.wordplus.org/end-user-license-agreement/)**

Languages:

* English
* Russian

**This is a new plugin, so please use support forums if you have found any bug or have any other question please use forums to contact us! :)**

== Frequently Asked Questions ==

= How email notifications works? =

Instead of standard notification on each new message, plugin will group messages by thread and send it every 15 minutess with cron job.

* User will not receive notifications, if they are disabled in user settings.
* User will not receive already read messages.
* User will not receive notifications, if he was online last 10 minutes or he has tab with opened site

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/bp-better-messages` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings -> BP Better Messages to configure the plugin

== Screenshots ==

1. Thread screen
1. Embedded links
1. Thread list screen
1. New Thread screen
1. Writing notification
1. Onsite notification
1. Files attachments

== Changelog ==

= 1.9.7.9 =
* Security Update

= 1.9.7.8 =
* Fix for PHP 7.2

= 1.9.7.7 =
* Fix some bugs

= 1.9.7.6 =
* This is minor release, not related to main development, which fixes some problems related to Youzer plugin compatibility
* Added JetPack Lazy Load support

= 1.9.7.5 =
* Added lightbox to the images
* Improved drag & drop files to window
* Other bugfixes and improvements

= 1.9.7.4 =
* Fixed button in new BuddyPress template
* Fixed tab appearing in some themes
* Added drag & drop files to window
* Added `bp_better_messages_mini_chat_username` filter
* Other bugfixes and improvements which I dont remember :o

= 1.9.7.3 =
* Some fixes for mobile version
* Added missing localization string

= 1.9.7.2 =
* Bugfixes & Improvements

= 1.9.7 =
* Improvements for the group thread
* Improvements for fast threads
* Many bugfixes and improvements

= 1.9.6 =
**Many changes, we tested it alot and it shouldnt create problems for you, but if you found any bug, please write us**
* Added Fixed Friends Tab
* Added Fast Mode (starting thread in 1 click)
* Added Friends Only Mode
* Many bugfixes and improvements

= 1.9.5 =
* Added Fixed Threads to WebSocket version
* Other bugfixes and improvements

= 1.9.4 =
* Added Mass Messaging Feature
* Changed the way to handle licenses
* Other minor bugfixes and improvements

= 1.9.3 =
* New Mobile Layout
* New File Uploader
* Other minor bugfixes and improvements

= 1.9.2.6 =
* Some CSS improvement
* Improvements for writing notifications
* Some bug fixes for websocket version

= 1.9.2.5 =
* Fix increased load when MiniChats disabled on WebSocket

= 1.9.2.4 =
* Fixed PHP Notice
* URL parsing improvements

= 1.9.2.3 =
* Fixed icons conflict

= 1.9.2 =
* **Add Messages Delivery Status (WebSocket version)**
* Other minor bugfixes and improvements

= 1.9.1 =
* **Added ability to use plugin without BuddyPress**
* Added setting to enable Search among all users
* Added setting to disable Subject
* Added setting to disable send on enter for touch screens
* Mobile Improvements
* Other minor bugfixes and improvements

= 1.9.0.1 =
* Fixed Firefox on Mac OS
* Other minor bugfixes and improvements

= 1.9.0 =
* Added mini chats for the WebSocket version
* Other minor bugfixes and improvements

= 1.8.3 =
* Mark notifications as unread on thread read
* Other minor bugfixes

= 1.8.2 =
* Minor bugfix

= 1.8.1 =
* Transforming -- to —
* bp_better_messages_current_template filter
* Minor bugfixes

= 1.8 =
* Search feature
* Minor attachment validation improvement
* Couple of minor improvements

= 1.7.9.1 =
* WebSocket version back

= 1.7.9 =
* Randomize attachments filenames
* Fixed security error on uploading allowed extension
* Improved emojies
* Couple of minor improvements

= 1.7.8 =
* AJAX loading for old messages
* Couple of minor improvements

= 1.7.7 =
**Improved attachments:**
* Attachments can be disabled or enabled
* Attachments removed from media screen
* Added settings for max file size and allowed formats
* Changed upload dir
* Autodelete old attachments

**Other**
* Better mobile adatation
* Localization loaded earlier
* WebSocket version not available anymore and will be available later.

= 1.7.6.1 =
* Many immprovements for WebSocket version

= 1.7.6 =
* WebSocket speed improvement
* Bugfixes
* Settings initial


= 1.7.5.2 =
* Message counters improvements

= 1.7.5.1 =
* Better avatar compability with other plugins

= 1.7.5 =
* Avatars improvement
* Fallback to AJAX if connect to WebSocket server failed

= 1.7.4 =
* Popups will be stacked now if same thread
* CSS Improvements

= 1.7.3 =
* Added pre sent message hooks

= 1.7.2 =
* Security improvement

= 1.7 =
* Possible to create new lines with Shift + Enter
* Paste files fixed multiple files sending
* Private browser bug
* Line breaks not removing in new thread anymore

= 1.6.5 =
* BP Notification will not added on each message anymore
* Improved files design

= 1.6.4 =
* Multiple bugfixes and improvements
* Improved emojies

= 1.6.3 =
* Fixed files uploading for default users.
* Another bugfixes

= 1.6.2 =
* Fixed fatal error, when BP Messages component wasnt active

= 1.6.1 =
* Nice attached files and images styling
* Attached video embed
* Attached audio embed
* Multiple bugfixes and improvements

= 1.6 =
* File Uploading initial
* Multiple bugfixes and improvements

= 1.5.1 =
* Online indication (websocket version)
* Multiple bugfixes and improvements

= 1.5 =
* Replaced Standard Email notifications with grouped messages
* Multiple bugfixes and improvements

= 1.4.4 =
* WebSocket Method polished and should work perfect now
* Multiple bugfixes and improvements
* CSS improvements

= 1.4.3 =
* AJAX Method polished and should work perfect now
* CSS polished

= 1.4.2 =
* Embedded links 404 fix
* No more double notifications if 2 threads opened in different tabs
* Added AJAX Loader

= 1.4.1 =
* Embedded links improvements

= 1.4 =
* Multiple bugfixes and improvements
* Embedded links feature!

= 1.3.2 =
* Prefix fix

= 1.3.1 =
* Remove BBPress functions

= 1.3 =
* Multiple bugfixes 
* Messages menu in topbar replaced

= 1.2 =
* Added starred messages screen
* Added thread delete/restore buttons
* Added empty screens

= 1.1 =
* Code refactoring and minor improvements

= 1.0 =
* Initial release