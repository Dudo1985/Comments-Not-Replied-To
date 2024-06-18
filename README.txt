=== Comments Not Replied To ===
Contributors: Dudo
Tags: comments, comments reply, replied, not replied
Requires at least: 3.5
Tested up to: 6.5
Stable tag: 1.5.9

Easily see which comments have not received a reply yet.

== Description ==

"Comments Not Replied To" is a plugin that makes it easy to manage the comments on your blog, especially for those of you who have a significant amount of comments.

Simply put, "Comments Not Replied To" introduces a new area in the administrative dashboard that allows you to see what comments to which you - or someone else you can decide - have not yet replied.

== GitHub ==
* [Follow development on GitHub](https://github.com/Dudo1985/comments-not-replied-to)

== Installation ==

= In The WordPress Dashboard =

1. Navigate to the 'Add New' plugin dashboard
2. Select `Comments-Not-Replied-To.zip` from your computer
3. Upload
4. Activate the plugin in the WordPress Plugin Dashboard

= Using FTP =

1. Extract `Comments-Not-Replied-To.zip` to your computer
2. Upload the `Comments-Not-Replied-To` directory to your wp-content/plugins directory
3. Navigate to the WordPress Plugin Dashboard
4. Activate the plugin from this page

== Screenshots ==

1. The updated 'Comments Dashboard' showing the new column with the 'Comment Reply' column and their status.
2. Filter the comments to show only the ones with missing reply
3. Plugin page settings

== Changelog ==

= 1.5.9 =
* Updated freemius sdk to version 2.7.2

= 1.5.8 =
* Updated freemius sdk to version 2.5.10

= 1.5.7 =
* REFACTOR: The plugin has been refactored to use PHP Namespacing
* TWEAKED: updated freemius SDK to version 2.5.8

= 1.5.6 =
* Updated Freemius SDK to version 2.5.3, this fix a warning if PHP 8.1 is used

= 1.5.5 =
* Minor changes

= 1.5.4 =
* Updated Freemius SDK to version 2.5.2, fixed broken links

= 1.5.3 =
* Updated Freemius SDK

= 1.5.2 =
* FIXED: Exclude pingbacks and trackbacks from count near the "Missing Reply" link

= 1.5.1 =
* FIXED: new comments doesn't appear in "missing reply" column

= 1.5.0 =
A lot of long waited features in this release:
* Pingbacks and trackbacks are now fully ignored
* New setting page, where is possible to select if a comment should be marked as read if an admin, an editor or an author answer to a comment
* Comment are now marked as no replied even if not approved yet
* When a comment is removed from trash, also plugin's comment meta is deleted
* Added a pro version of the plugin, where is possible to mark the single comment as read

= 1.4.0 =
* FIX: support for https://translate.wordpress.org
* TWEAKED: refactor plugin to support boilerplate standard and code cleanup

= 1.3.1 =
* FIX: "Missing reply" section was showing comments with replies

= 1.3.0 =
Plugin was adopted by new developer.
* TWEAKED: use dashicons instead of images
* TWEAKED: minor changes

= Additional Info =
See credits.txt file
