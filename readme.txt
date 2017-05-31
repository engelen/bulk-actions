=== Advanced Bulk Actions ===
Contributors: engelen
Tags: bulk actions,bulk,actions,admin,advanced bulk actions,post type,bulk post type,change post type,change,post,type
Requires at least: 4.7
Tested up to: 4.7
Stable tag: 1.1.2

Supercharge the WordPress admin panel with additional bulk actions to manage your content

== Description ==

[Advanced Bulk Actions](https://wordpress.org/plugins/bulk-actions/) is a WordPress plugin that adds new bulk actions to your admin panel on the posts, pages and users overviews. It works with custom post types.

> This plugin **works only with WordPress versions 4.7+** and is in active development. The objective to is to implement a wide range of useful bulk actions.
> Feedback is highly appreciated: if you have any suggestions regarding bulk actions, please [create a new topic in the support forums](https://wordpress.org/support/plugin/bulk-actions#new-post). Thank you!

With this plugin, you can easily switch the post types, change the featured image, or change the post visibility or status, of multiple posts at once.

= Features: bulk actions =
*	Change post type
*	Change featured image
*	Change post visibility
*	Change post status

== Installation ==

1. Upload `bulk-actions` to the `/wp-content/plugins/` directory
2. Activate Bulk Actions through the 'Plugins' menu in WordPress
3. You're done! No further configuration is needed. The additional bulk actions now appear on the relevant admin pages

== Screenshots ==

1. Advanced Bulk Actions for Posts overview
2. Change featured image for multiple posts at once

== Changelog ==

= 1.1.2 =
* Remove debugging information
* Only display the feedback notice to people who can `manage_options`
* Fix syntax error in PHP <= 5.3

= 1.1.1 =
* Localization fix for featured image JavaScript
* Added feedback notification for feature suggestions etc.

= 1.1 =
* Added bulk action for changing post status
* Restore state of previously opened custom bulk action when switching back from another bulk action
* Add visual indicator to bulk action secondary dropdown or input when it appears
* Add default "None" option to 2nd-tier bulk action settings

= 1.0 =
* First full release. Includes actions for post type, featured image and post visibility

= 0.1beta =
* Beta version. Internal version numbers in @since are 1.0