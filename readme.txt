=== Ad Blocking Detector ===
Contributors: jtmorris
Donate link: http://adblockingdetector.jtmorris.net/
Tags: adblock, adblocker, ad blocker, adblock plus, detector, advertisement, ads, ad blocking
Requires at least: 3.9
Tested up to: 4.0
Stable tag: 2.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Tired of missed opportunities because of pesky ad blocker browser extensions, add-ons, and plugins? Fight
back with Ad Blocking Detector today!

== Description ==
Tired of missed opportunities and empty spaces because of pesky ad blocking browser extensions,
add-ons, and plugins? Would you like to determine which of your site visitors use ad blockers and
do something about it? Then this highly rated plugin is for you!

Use the simple [built-in tool](http://adblockingdetector.jtmorris.net/using-ad-blocking-detector/) to substitute
alternative content in place of blocked ads.  Or, hook
into the ad block detection mechanism with CSS and JavaScript and customize your site any way you want!

= Built-in Tool =
This plugin includes a tool for specifying two alternative collections of content.  One collection
to display to visitors without ad blockers (such as an ad).  The other to display to ad block
wielding visitors (such as a message or image).
Then, using the shortcode, [sidebar widget](http://adblockingdetector.jtmorris.net/new-feature-sidebar-widgets/),
or other method, let Ad Blocking Detector intelligently
determine which to display to the user.  [Check out the demo](http://adblockingdetector.jtmorris.net/demo/) on the plugin’s
website, or [look at the
screenshots](https://wordpress.org/plugins/ad-blocking-detector/screenshots/) for an example.

Use this power to, in place of a blocked ad, display a signup form for your email newsletter, a link
and description to your eBook, a plea to your visitors, and much more.   Don’t let ad blockers waste
prime real estate on your site!

= Hook In With JavaScript and CSS =
If the built-in tool isn’t what you’re looking for, ignore it and use the ad block detection for your
own purposes.  With this plugin, you can easily modify the appearance of your site with CSS or execute
JavaScript code based on the ad block detection results!  The sky is the limit!
For ideas and information on how to use this power, look at the following articles on the plugin’s website.

- [Combine CSS and Ad Blocking Detector](http://adblockingdetector.jtmorris.net/combine-css-ad-blocking-detector/)
- [Unleash Your Inner Geek With JavaScript and Ad Blocking Detector](http://adblockingdetector.jtmorris.net/unleash-your-inner-geek-with-javascript/)

= Features =
- Detects all major ad blocking browser extensions on all major web browsers.  Including AdBlock Plus!
- Full [compatibility with WordPress multisite](http://adblockingdetector.jtmorris.net/using-ad-blocking-detector-multisite/) networks!
- Regular feature enhancements and additions!
- Excellent support from the plugin developer.




== Installation ==
= Using WordPress.org's Plugin Repository (recommended) =
1. Search for "Ad Blocking Detector" in your WordPress "Add New" plugin section of your dashboard.
1. Install and activate the "[Ad Blocking Detector](http://wordpress.org/plugins/ad-blocking-detector/)" plugin by John Morris.
1. Visit the newly added "Ad Blocking" menu in your admin section to get started.


= Manually =
1. Download the latest version of adblock-detector and upload the `adblock-detector.zip` file
in the Add New plugin section of your dashboard.
1. Activate the plugin through the "Plugins" menu in your WordPress admin section.
1. Visit the newly added "Ad Blocking" menu in your admin section to get started.


= From Source =
1. Visit the plugin's [GitHub repository](https://github.com/jtmorris/adblock-detector).
1. Select the branch you want to download (leaving the default master branch is highly recommended).
1. Click the download ZIP button on the lower right side of the page.
1. Upload the contents of the adblock-detector-**branch name** directory to a directory named 'adblock-detector'
in your WordPress site's './wp-content/plugins/' directory.
1. Visit your WordPress site's Plugins menu in the admin section, and activate the newly listed
Ad Blocking Detector plugin.

== Frequently Asked Questions ==
= How can I use a shortcode in sidebar widget? =
You have two options.  The first and simplest is to use the [built-in sidebar widget](http://adblockingdetector.jtmorris.net/new-feature-sidebar-widgets/).  Simply
edit your widgets, and add the Ad Blocking Detector widget.

However, if you would prefer greater flexibility, you can copy and paste your desired shortcode into a
Text widget added to your sidebar.


Note: By default, WordPress does not parse shortcodes in text widgets.  You must enable that functionality manually.
You can find instructions from the folks at WPBeginner.com
[here](http://www.wpbeginner.com/wp-tutorials/how-to-use-shortcodes-in-your-wordpress-sidebar-widgets/).

The short version is you must add the following code to your theme's *functions.php* file:
`add_filter('widget_text', 'do_shortcode');`


= How does this plugin treat visitors with JavaScript disabled? =
At this time, this plugin does not treat disabled JavaScript as an ad blocker.
If the visitor has JavaScript disabled, the plugin displays the content used
for normal visitors with no ad blockers.


= Does this plugin prevent visitors with an ad blocker from visiting my site or in any other way obscure the content? =
It was not designed to do so, but it is possible to replicate that behavior in an indirect way.
This plugin's purpose is to simply places alternative content in the space the ad would have displayed.

If you want more information on how to replicate the obscuring of content, check out this blog post
on the plugin's website: [http://adblockingdetector.jtmorris.net/unleash-your-inner-geek-with-javascript/](http://adblockingdetector.jtmorris.net/unleash-your-inner-geek-with-javascript/)


= Why doesn't this plugin detect the _______________ ad blocker? =
Not all ad blockers operate the same way.  This plugin was tested with the most common ad blockers, but it is
possible I missed one.  If you find an ad blocker this plugin doesnt detect, [contact me](http://adblockingdetector.jtmorris.net/contact/) and let me know the
ad blocker, web browser, and operating system you are using.  I will then investigate detecting it.


= Can this plugin integrate with other ad management plugins? =
This plugin recognizes shortcodes from other sources.  Some ad management plugins, like the popular *[Ad Rotate](http://wordpress.org/plugins/adrotate/)* plugin, use
shortcodes to output their ads.  You can use the shortcode generated by other plugins inside *Ad Block Detector*, allowing you
to display ads from ad management plugins while harnessing the power of ad block detection.  You can find more information at the [plugin
website](http://adblockingdetector.jtmorris.net/display-rotating-ads/).


= Why are certain items, like screenshots and banners, missing from the WordPress plugin repository page for this plugin? =
Most likely because you are using an ad blocker right now.  Sadly, because of the name of this plugin, ad blockers
flag the banner and screenshots as an ad and remove them.  Imagine my chagrin when I found out my ad blocking detector
plugin has elements on its page blocked by ad blockers.  Unfortunately, I can't fix this without renaming the plugin and
creating a whole new plugin listing.  If you want to see the screenshots and banner, disable your ad blocker on this page.


== Screenshots ==
1. Administration Dashboard for Ad Blocking Detector
2. New Shortcode Form in Administration Dashboard
3. Example Content in New Shortcode Form in Administration Dashbaord
4. Demo of This Plugin **With** an Active Ad Blocker
5. Demo of This Plugin **Without** an Active Ad Blocker


== Changelog ==
= 2.2.0 =
* Included JavaScript events for developer tie-ins. [Click here for more info!](http://adblockingdetector.jtmorris.net/)
* Fix typos and a bug introduced by last update.
* Update plugin listing and tutorial/tip links.
= 2.1.10 =
* Added CSS and JavaSript selectors and flags for easier end-user targeting.
= 2.1.9 =
* Add sidebar widget capabilities.
= 2.1.8 =
* Fix update issue.
= 2.1.7 =
* Minify JavaScript files to improve load time.
= 2.1.6 =
* Add additional HTML IDs, classes, and wrappers around output for easy CSS targeting.
* Added warning/confirmation before deleting shortcodes to prevent accidental deletions.
* Added plugin thumbnails.
* Verified, and so indicated, WordPress 4.0 support.
= 2.1.5 =
* Fix browser warnings when using HTTPS.
= 2.1.4 =
* Code improvements.
* New "tip" in sidebar.
* Implemented admin section notification system.
= 2.1.3 =
* Code cleanup.
* Add "Other Plugins By Developer" box to ABD Dashboard
= 2.1.2 =
* Fix errors with asynchronous loading of JavaScript.
= 2.1.1 =
* UI improvements.
* MAJOR BUG FIX: No longer gives error to new users when adding shortcodes.
= 2.1.0 =
* Added ability to disable WordPress editor auto-formatting.
* Added settings link for Ad Blocking Detector on plugin management page.
= 2.0.13 =
* Fix incompatibility with JS & CSS Optimizer plugin.
* Code improvements.
= 2.0.12 =
* Preparations for future feature additions.
* Bug fixes.
= 2.0.11 =
* Performance improvements.
* Removed some dependence on JavaScript.
* Bug fixes.
= 2.0.10 =
* Bug fixes.
= 2.0.9 =
* Bug fixes.
= 2.0.8 =
* Fix Internet Explorer page refresh problems.
* Fix a rare ad block detection out-of-sync error.
= 2.0.7 =
* Bug fixes.
= 2.0.6 =
* Bug fixes.
= 2.0.5 =
* Bug fixes.
= 2.0.1 =
* Minor bug fixes.
* Better documentation.
= 2.0.0 =
* MULTISITE SUPPORT!
* Massive code base cleanup and refactoring.
* Performance improvements.
* Improved documentation.
* Bug fixes.
* UI improvements.
= 1.2.2 =
* Fixed PHP warnings when PHPSESSID cookie contains illegal characters.
= 1.2.1 =
* Add support for WordPress 3.9.
= 1.2.0 =
* Enabled shortcode processing in ad code. Now you can take advantage of other [plugin's capabilities](http://adblockingdetector.jtmorris.net/display-rotating-ads/) inside Ad Blocking Detector code.
* Bug fixes.
= 1.1.3 =
* Bug fixes.
= 1.1.1 =
* Add screenshots and a banner image to the plugin listing.
= 1.1.0 =
* Add two new ad block detection methods
* Fix broken detection in Firefox's Adblock Plus
* Fix repeated ad block toggling problem
* Several bug fixes and performance improvements
= 1.0.2 =
* Fix default shortcodes
= 1.0 =
* Prep for initial publishing on WordPress.org.
* Fix a few links.
= 0.2 =
* Add content and formatting required to submit this plugin to WordPress.org's repository.
= 0.1 =
* Initial version. No changes to report.


== Upgrade Notice ==
= 2.2.0 =
Increases Ad Blocking Detector's capabilities allowing you to use ad block detection any way you want. Install the update to receive more information.
= 2.1.9 =
NEW FEATURE: WIDGETS. This plugin now offers a widget for displaying your shortcodes. You'll no longer need to use text widgets and theme edits (unless you want to).
= 2.1.1 =
MAJOR BUG FIX: Addresses problem where new plugin users were unable to add new shortcodes after last update.
= 2.0.8 =
IMPORTANT! Addresses unexpected behavior when refreshing the page in Internet Explorer.
= 2.0.5 =
Fixes issue with latest update where some shortcodes become unavailable.
= 2.0.0 =
Major update! Lot's of bug fixes, performance improvements, documentation improvements, interface cleanup, and MULTISITE support.
= 1.2.2 =
SECURITY UPDATE: This update plugs a security hole that, in cicumstances, gave [Full Path Disclosure](https://www.owasp.org/index.php/Full_Path_Disclosure).
= 1.2.0 =
Added a powerful new feature: shortcode support. Now you can [integrate content from other plugins](http://adblockingdetector.jtmorris.net/display-rotating-ads/) with Ad Blocking Detector.
= 1.1.3 =
Bug fixes allowing for easier error debugging.
= 1.1.0 =
Fixed detection of Firefox's Adblock Plus and added numerous bug fixes and performance improvements
= 0.1 =
Because it's the awesome first version!
