=== BuddyPress User Blog ===
Contributors: buddyboss
Requires at least: 3.8
Tested up to: 4.8.2
Stable tag: 1.3.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Provide your members with their own blog and a great writing experience.

== Description ==

Whether you have a community site and would like to give your users the ability to blog, or want to create a blogging platform focused on publishing, this plugin is for you.

== Installation ==

1. Make sure BuddyPress is activated.
2. Visit 'Plugins > Add New'
3. Click 'Upload Plugin'
4. Upload the file 'buddypress-user-blog.zip'
5. Activate BuddyPress User Blog from your Plugins page.

= Configuration =

1. Visit 'BuddyBoss > User Blog' and select your desired options.

== Changelog ==

= 1.3.1 =
* Fix - Restrain bp sub nav slug translation
* Fix - Link font color are black in black background while editing link in the post make link completely invisible
* Fix - Add New Posts tab disappear when the Bookmarks or In Review tabs are selected
* Fix - Post saving twice as a draft
* Fix - Draft and In Pending tabs disappear if site language is not english

= 1.3.0 =
* Fix - Image upload spinning icon showing even after publish post
* Fix - Recommendation button not appearing for the logged out users
* Fix - Unable to remove a featured image from the published post
* Fix - Translation strings
* Fix - Post always save as a draft when published from the home or blog index page
* New - Notify author when user recommends the post
* New - Added bookmarks tab under users profile
* New - Added an extension for medium editor for auto creates list
* New - Added an table extension for medium editor
* New - Added setting to limit minimum and maximum number of words to publish the post
* New - Added setting to restrict the number of images the user can upload at once
* New - Added setting to exclude categories from the frontend post add page
* New - Added setting to permanently delete the post media upon post deletion
* New - Added setting to disable post auto save as a draft

= 1.2.1 =
* Enhancement – License Module

= 1.2.0 =
* New - Filter 'sap_post_category_args' to add posts into activity stream by category
* Tweak - Updated FontAwesome to version 4.7.0
* Fix - Cannot remove tags while editing post
* Fix - Translation fix for renaming the text "Blog"
* Fix - Cursor issue while uploading multiple images
* Fix - Added missing translation strings
* Fix - jQuery.noConflict() is wrong which create errors with 3rd party plugins
* Fix - Remove unecessary Edit link on custom posts
* Fix - Submit for review doesn't work for the published posts
* Fix - JetPack sharing module conflict
* Fix - Sort function on the Blog menu does not work correctly
* Fix - Typo on author info
* Fix - Typo on Post Author - Published line
* Fix - Uploading image fails sometimes
* Fix - PHP error notices

= 1.1.2 =
* New - Ability to post into multiple categories
* Fix - Facebook video link used in post content not appearing in the single post
* Fix - After uploading image in post content, if a user adds video, uploaded image disappears
* Fix - With OneSocial theme, alert 'post title cannot be empty' on Internet Explorer
* Fix - <p> tags not syncing correctly between frontend and backend editors
* Fix - When uploading an image in post content, after 1 upload the second image doesn't appear
* Fix - Video link overlapping the editor icons
* Fix - Translation errors

= 1.1.1 =
* Fix - Post content image uploader doesn't work with iPhone 6S & 6S+ (iOS 9)
* Fix - Tags not appearing in edit post

= 1.1.0 =
* Fix - Media Upload issue on iPhone
* Fix - Bookmarks page assignment issue
* Fix - String Translation issue with  "\t" and "\n"
* Fix - Recommends are lost after post update
* Fix - When I paste URL for video, the uploaded image got lost
* Fix - Add loading animated icon when uploading a Featured Image
* Fix - Issue with recommended count on Single Post
* Fix - Not able to translate word "Blog" in BP nav menu
* Fix - On BuddyBoss theme, Pagination links on posts getting Edit link.
* Fix - Title issue on Featured Image
* Fix - Translation Issue
* Fix - 404 on user profiles when root profile url structure is activated
* Fix - Conflict with JetPack
* Fix - Alignment Issue on BuddyBoss Theme
* Fix - Post image is not always uploading in content
* Localization - French translations added, credits to Jean-Pierre Michaud

= 1.0.6 =
* Tweak - Updated FontAwesome to version 4.5.0
* Fix - Issues when plugin is network activated
* Fix - No featured image in blog posts for certain setups

= 1.0.5 =
* Tweak - Better edit icons for ordered/unordered lists

= 1.0.4 =
* Fix - Content not displaying in single post
* Fix - Category display in front-end
* Fix - Assigning category from front-end
* Fix - Translation issues with blog slug
* Fix - Added formatting for ordered and unordered lists
* Fix - Removed bookmark icons from custom post types
* Fix - z-index issues with sidebar
* Fix - Link on username not working, in post creator
* Fix - Comment disabled issue when post is updated
* Fix - Responsive CSS issues

= 1.0.3 =
* Fix - Allow subscribers to edit their drafts
* Fix - Improved auto-save behavior
* Fix - Inline image uploader improvements
* Fix - Correctly prepare oEmbeds when editing posts
* Fix - Better loading method for FontAwesome

= 1.0.2 =
* Fix - Conflict with BuddyPress Member Types

= 1.0.1 =
* Fix - Inline image uploader improvements

= 1.0.0 =
* Initial Release
