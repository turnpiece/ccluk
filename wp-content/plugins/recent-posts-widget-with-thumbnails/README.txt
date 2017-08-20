=== Recent Posts Widget With Thumbnails ===
Contributors: Hinjiriyo
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=TKZZ3US2R56RY
Tags: arabic, aspect ratio, author, category, categories, category, current post, excerpt, extended, featured, featured images, first post image, height, image, images, listed posts, post date, post categories, post category, post title, random, recent posts, sticky, thumb, thumbnail, thumbnails, thumbs, widget, widgets, width, persian, farsi, russian
Requires at least: 2.9
Tested up to: 4.8
Stable tag: 5.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

List of your site&#8217;s most recent posts, with clickable title and thumbnails.

== Description ==

The plugin is available in English, German (Deutsch), Persian (فارسی), Arabic (العربية), Polish (Polski) and Russian (русский).

= Lightweight, simple and effective =

No huge widget with hundreds of options. This plugin is based on the well-known WordPress default widget 'Recent Posts' and extended to display more informations about the posts like e.g. thumbnails, excerpts and assigned categories.

The thumbnails will be built from the featured image of a post or of the first image in the post content. If there is neither a featured image nor a content image then you can define a default thumbnail.

You can set the width and heigth of the thumbnails in the list. The thumbnails appear left-aligned to the post titles in left-to-right languages. In right-to-left languages they appear right-aligned.

= What users said =

* **"A first class plugin"** in the [support forum](https://wordpress.org/support/topic/a-first-class-plugin) by lakenjr on August 298 2016
* **"&hellip;definitely the best Recent Posts plugin I've found&hellip;"** in the [support forum](https://wordpress.org/support/topic/google-chrome-blurry-thumbnail-85x85) by devintshawn on April 9, 2016
* **"&hellip;you just have to try it out"** in [WordPress Tools That Use Visuals to Grab Visitors’ Attention](http://www.onextrapixel.com/2016/02/25/wordpress-tools-that-use-visuals-to-grab-visitors-attention/) by Gayane Mar on February 25, 2016
* **"Easy and lightweight"** in [8 Useful WordPress Widgets for Your Site](http://dinevthemes.com/8-useful-wordpress-widgets-for-your-site/) by Lucy Barret on January 21, 2016
* **Number 5** in [12 Useful WordPress Sidebar Widgets to Engage Visitors](https://85ideas.com/plugins/best-widgets-wordpress/) by Pawan Kumar on December 8, 2015
* **Number 1** in [Los 10 widgets de WordPress más prácticos](http://wpdirecto.com/los-10-widgets-de-wordpress-mas-practicos-1860/) by Jorge López on November 13, 2015
* [How to show recent posts in WordPress blog](http://mayifix.com/how-to-show-recent-posts-in-wordpress-blog.html) by Robin on June 28, 2015
* **Number 5** in [Best List of Free Recent Posts Widgets for WordPress](http://dotcave.com/wordpress/free-recent-posts-widgets-for-wordpress/) by jerry on November 29, 2014
* **Number 1** in [25 Most Useful WordPress Widgets for Your Site](http://www.wpbeginner.com/showcase/25-most-useful-wordpress-widgets-for-your-site/) by Editorial Staff on September 18, 2014

= Options you can set =

1. Title of the widget
2. Number of listed posts
3. Open post links in new windows
4. Random order of posts
5. Hide current post in list
6. Keep sticky posts on top of the list
7. Hide post title
8. Maximum length of post title
9. Show post author
10. Show post categories
11. Show post date
12. Show post excerpt
13. Show number of comments
14. Excerpt length
15. Signs after excerpt
16. Ignore post excerpt field as excerpt source (builds excerpts automatically from the post content)
17. Print slugs of post categories in class attribute of LI elements
18. Show posts of selected categories (or of all categories)
19. Show post thumbnail (featured image)
20. Registered thumbnail dimensions
21. Thumbnail width in px
22. Thumbnail height in px
23. Keep aspect ratio of thumbnails
24. Try to take the first post image as thumbnail
25. Only use the first post image as thumbnail
26. Use default thumbnail if no thumbnail is available
27. Default thumbnail URL

= Much more options available =

If you want to build your special posts lists with additional options for layout, informations about each post and embedding via shortcode [take a look at the plugin Ultimate Post List Pro](http://shop.stehle-internet.de/downloads/ultimate-post-list-pro/).

= Useful hints for developers: Hooks and CSS =

See [Other Notes](https://wordpress.org/plugins/recent-posts-widget-with-thumbnails/other_notes/) for supported hooks and available CSS selectors.

= Languages =

The user interface is available in

* English
* German (Deutsch)
* Persian (فارسی), kindly drawn up by [Sajjad Panahi](https://profiles.wordpress.org/asreelm)
* Arabic (العربية), kindly drawn up by [Shadi AlZard](https://wordpress.org/support/profile/salzard)
* Polish (Polski), kindly drawn up by [Marcin Mikolajczyk](https://wordpress.org/support/profile/marcinmik)
* Russian (ру́сский), kindly drawn up by [dmitriynn](https://wordpress.org/support/profile/dmitriynn)

Further translations are welcome. If you want to give in your translation please leave a notice in the [plugin's support forum](https://wordpress.org/support/plugin/recent-posts-widget-with-thumbnails).

== Other Notes ==

= Supported Hooks =

The plugin considers the output of actions hooked on:

1. widget_title
2. rpwwt_widget_posts_args
3. rpwwt_excerpt_more
4. rpwwt_excerpt_length
5. rpwwt_list_cats

= Available CSS Selectors =

To design the list and its items you can use these CSS selectors:

The elements which contain the posts lists:
`.rpwwt-widget`

The lists which contain the list items:
`.rpwwt-widget ul`

All list items in the lists:
`.rpwwt-widget ul li`

All list items of sticky posts in the lists:
`.rpwwt-widget ul li.rpwwt-sticky`

All links in the lists; every link contains the image and the post title:
`.rpwwt-widget ul li a`

All images in the lists (use that to set the margins around images):
`.rpwwt-widget ul li a img`

All post titles in the lists:
`.rpwwt-widget ul li a span.rpwwt-post-title`

All post author in the lists:
`.rpwwt-widget ul li div.rpwwt-post-author`

All post categories in the lists:
`.rpwwt-widget ul li div.rpwwt-post-categories`

All post dates in the lists:
`.rpwwt-widget ul li div.rpwwt-post-date`

All post excerpts in the lists: 
`.rpwwt-widget ul li div.rpwwt-post-excerpt`

All numbers of comments in the lists: 
`.rpwwt-widget ul li div.rpwwt-post-comments-number`

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'Recent Posts Widget With Thumbnails'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard
5. Go to 'Appereance' => 'Widgets' and select 'Recent Posts Widget With Thumbnails'

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `recent-posts-widget-with-thumbnails.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard
6. Go to 'Appereance' => 'Widgets' and select 'Recent Posts Widget With Thumbnails'

= Using FTP =

1. Download `recent-posts-widget-with-thumbnails.zip`
2. Extract the `recent-posts-widget-with-thumbnails` directory to your computer
3. Upload the `recent-posts-widget-with-thumbnails` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard
5. Go to 'Appereance' => 'Widgets' and select 'Recent Posts Widget With Thumbnails'

== Frequently Asked Questions ==

= What are the requirements for this plugin? =

The WordPress version should be at least 2.9 to use featured images.

The theme should support `wp_head()` in the HTML header section to print the CSS code for a beautiful alignment of the thumbnails.

= Can I set a default thumbnail? =

Yes.

= Can I set the width and height of the thumbnail? =

Yes. You can enter the desired width and height of the thumbnails or select one of the sizes as set in 'Settings' > 'Media'.

= Can I change the alignment of the thumbnails in the list? =

This feature will come in a future version of the plugin. Set the alignment in the CSS of your theme instead.

= Where can I set the CSS of the list? =

This feature will come in a future version of the plugin. Set the CSS in the style.css of your theme instead.

= Can the plugin take the first image of a post as thumbnail image? =

Yes. It works with images previously uploaded into the media library. You can select to prefer the first image to the featured image or to use the first image only.

= Where is the *.pot file for translating the plugin in any language? =

If you want to contribute a translation of the plugin in your language it would be great! You would find the *.pot file in the 'languages' directory of this plugin. If you would send the *.po file to me I would include it in the next release of the plugin.

== Screenshots ==

1. The first screenshot shows the widget in the sidebar with five teasers of current posts. Each list item shows the title, image, date, assigned categories and excerpt of a post.
2. The second screenshot shows the widget on the Widget Management Page in the backend.

== Changelog ==

= 5.1 =
* Revised sanitations for texts and URLs on the pages
* Revised translations
* Tested successfully with WordPress 4.8

= 5.0 =
* Removed usage of cache
* Removed usage of extract()
* Improved: Faster check for found first image against being an image
* Tested successfully with WordPress 4.7.2

= 4.13.3 =
* Revised translation of author line

= 4.13.2 =
* Revised widget template for more conformity to WP standard widget output

= 4.13.1 =
* Tested successfully with WordPress 4.7

= 4.13 =
* Added option to print the post category slugs as class names at LI elements
* Fixed outdated URL to reviews
* Updated *.pot file and german translation

= 4.12 =
* Added option to ignore the post excerpt field as source of the excerpt
* Updated *.pot file and german translation

= 4.11 =
* Revised uninstall function for WordPress 4.6 due to the introduction of WP_Site_Query class
* Narrowed down loading of plugin's admin CSS file to Widgets page only
* Tested successfully with WordPress 4.6

= 4.10.2 =
* Fixed wrong length of excerpts

= 4.10.1 =
* Added chmod after creation of public.css to ensure correct file permissions
* Revised excerpt creation

= 4.10 =
* Fixed old-to-new posts sort order in some installations to force new-to-old sort order
* Fixed outdated translation
* Added russian translation. Thank you very much, [dmitriynn](https://wordpress.org/support/profile/dmitriynn)
* Tested successfully with WordPress 4.5.2

= 4.9.2 =
* Added polish translation. Thank you very much, [Marcin Mikolajczyk](https://wordpress.org/support/profile/marcinmik)
* Improved: Manual excerpts are taken unchanged ("as is")
* I18n description in the backend's plugin list
* Added link to more versatile plugin [Ultimate Post List Pro](http://shop.stehle-internet.de/downloads/ultimate-post-list-pro/)
* Tested successfully with WordPress 4.5
* Updated *.pot file and translations

= 4.9.1 =
* Improved integration of 3rd party plugins for effects on the thumbnail

= 4.9 =
* Added option: Open post links in new windows
* Renamed back: Hook 'rpwwt-widget-title' to 'widget-title' to let 3rd party plugins change the title
* Improved sanitizing of stored variables
* Updated *.pot file and translations
* Updated screenshot of widget in the backend

= 4.8 =
* Added option: Show post author
* Updated *.pot file and translations
* Updated screenshot of widget in the backend

= 4.7 =
* Added option: Random order of posts
* Updated *.pot file and translations
* Updated screenshot of widget in the backend
* Tested successfully with WordPress 4.4.2

= 4.6.2 =
* Renamed the hook names to avoid interferences with other functions of plugins and the theme. If you use these hooks for that plugin please change them: just place 'rpwwt_' before the hook names
* Improved: Last list item has no space anymore to the next widget to keep same spaces between widgets

= 4.6.1 =
* Fixed: widget title. Now if no title is entered no title is displayed (instead of showing the plugin's name)
* Fixed: commas in categories list. Commas are now internationalized (translated)

= 4.6 =
* Added option: Post categories
* Updated *.pot file and translations
* Updated screenshot of widget in the backend

= 4.5.1 =
* Moved comment checkbox to position after form fields for the excerpt options
* Tested successfully with WordPress 4.4

= 4.5 =
* Added option: Post title length
* Updated *.pot file and translations

= 4.4 =
* Added option: Show number of comments
* Updated *.pot file and translations

= 4.3.4 =
* Fixed search stop at more link
* Deleted visual intend of the linklist in some themes
* Refactored thumbnail size variable

= 4.3.3 =
Improved data sanitization

= 4.3.2 =
* Added widget description based on backend language
* Corrected text domain name for translate.wordpress.org
* Renamed translation files

= 4.3.1 =
* Little adaptions for language files, ready for translate.wordpress.org
* Updated *.pot file and translations

= 4.3 =
* Added arabic translation. Thank you very much, [Shadi AlZard](https://wordpress.org/support/profile/salzard)
* Tested successfully with WordPress 4.3.1

= 4.2.1 =
* Fixed alignment of text and thumbnail in right-to-left (RTL) languages. Please re-save the widget to get the correct layout in RTL languages.

= 4.2 =
* Added persian translation (Farsi). Thank you very much [Sajjad Panahi](https://wordpress.org/support/profile/asreelm)
* Tested successfully with WordPress 4.3

= 4.1 =
* Changed single selection of a category to selection of multiple categories
* Added DIV with id `rpwwt-{widget_id}` and class `rpwwt-widget` around list for available container with ensured attribute for CSS selectors
* Updated admin CSS
* Updated *.pot file and german translation
* Updated screenshot of widget in the backend
* Revised readme.txt

= 4.0 =
* Added category option: widget only lists posts of a selected category, else lists posts of all categories
* Added sticky posts option: widget shows sticky posts on top of the list, else lists them in normal order
* Added hide current post option: widget does not list the post where the user is currently on, else lists it
* Added CSS class names for easy designing of the list and its list items; see Description for details
* Added style sheet for Widget page in the backend
* Fixed missing custom image sizes in frontend
* Formatted the code more readable
* Updated *.pot file and german translation
* Updated screenshots
* Revised readme.txt

= 3.0 =
* Added default image sizes dropdown menu
* Added options to print out excerpts
* Refactored: HTML output moved into include files
* Slight improvements for security and performance
* Updated *.pot file and german translation
* Revised readme.txt

= 2.3.3 =
* Fixed error message on trial to open the CSS file
* Tested successfully with WordPress 4.2.2

= 2.3.2 =
* Fixed bug of wrong path to public.css file
* Changed HTML class names, now they start with 'rpwwt-'

= 2.3.1 =
* Set CSS for the list style to prevent dots in some themes
* Added span element with class "post-title" around the title
* Tested successfully with WordPress 4.2

= 2.3 =
* Added option to keep aspect ratios of the original images
* Added option to hide the post title in the list
* Moved inline CSS to external file
* Revised *.pot file and german translation

= 2.2.2 =
* Successfully tested with WordPress 4.1
* Fixed bug which threw a warning in debug mode when accessing options

= 2.2.1 =
* Fixed bug which prevented to find the first content image
* Slightly revised algorithm for detecting the first image in post content

= 2.2 =
Revised algorithm to detect the first image in post content.

= 2.1.1 =
Successfully tested with WordPress 4.0

= 2.1 =
* Improve uninstall routine
* Tested successfully with WordPress 3.9.2

= 2.0 =
* Added option to set width and height of the thumbnails
* Added option to prefer first content image to featured image
* Added option to use only first content image as thumbnail
* Added option to set a default thumbnail
* Added function to delete plugin's settings in the database if the plugin is deleted
* Improved code for more robustness
* Updated *.pot file and german translation

= 1.0 =
* The plugin was released.

== Upgrade Notice ==

= 5.1 =
Revised sanitations and translations, tested with WordPress 4.8

= 5.0 =
Revised code

= 4.13.3 =
Revised translation of author line

= 4.13.2 =
Revised widget template

= 4.13.1 =
Tested successfully with WordPress 4.7

= 4.13 =
Added category names option, updated german translation

= 4.12 =
Added ignore excerpt field option, updated german translation

= 4.11 =
Revised uninstall function, CSS file in Widgets page only, tested with WP 4.6

= 4.10.2 =
Fixed wrong length of excerpts

= 4.10.1 =
Added chmod, revised excerpt creation. Please readjust the excerpt length if neccessary!

= 4.10 =
Force new-to-old sort order, added russian translation, tested with WP 4.5.2

= 4.9.2 =
Some text improvements, polish translation, tested with WP 4.5

= 4.9.1 =
Improved integration of 3rd party plugins on the thumbnail

= 4.9 =
Added option: Open link in new window; renamed back: hook 'rpwwt-widget-title' to 'widget-title'

= 4.8 =
Added option: Show post author

= 4.7 =
Added option: Random posts order

= 4.6.2 =
Renamed the hook names to avoid interferences: just place 'rpwwt_' before the hook names. Small CSS improvement

= 4.6.1 =
Fixed empty widget title, comma internationalization

= 4.6 =
Added option: Post categories

= 4.5.1 =
Moved comment checkbox, tested with WordPress 4.4

= 4.5 =
Added option: Post title length

= 4.4 =
Added option: Show number of comments

= 4.3.4 =
Fixed search stop at more link

= 4.3.3 =
Improved data sanitization

= 4.3.2 =
Added widget description based on backend language, corrected text domain name

= 4.3.1 =
Little adaptions for language files, updated translations

= 4.3 =
Added arabic translation

= 4.2.1 =
Fixed alignment of text and thumbnail in right-to-left (RTL) languages. Please re-save the widget to get the correct layout in RTL languages.

= 4.2 =
Added persian translation (Farsi)

= 4.1 =
Changed single selection of a category to multiple categories

= 4.0 =
Added options: sticky posts, current post, category filter; revised code

= 3.0 =
Added options: image sizes and excerpt

= 2.3.3 =
Fixed error message on trial to open the CSS file

= 2.3.2 =
Fixed CSS bug

= 2.3.1 =
Slight CSS improvements, tested successfully with WordPress 4.2

= 2.3 =
Refactored. Please update the settings of the widget after upgrading the plugin

= 2.2.2 =
Successfully tested with WordPress 4.1, fixed a minor bug

= 2.2.1 =
Bugfixed and improved algorithm for detecting the first image in post contents

= 2.2 =
Revised algorithm for detecting the first image in post content

= 2.1.1 =
Successfully tested with WordPress 4.0

= 2.1 =
Improved uninstall routine, tested with WordPress 3.9.2

= 2.0 =
More options and improved code

= 1.0 =
First release.
