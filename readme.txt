=== Ad Blocking Detector ===
Contributors: jtmorris
Donate link: http://adblockingdetector.jtmorris.net/
Tags: adblock, adblocker, ad blocker, adblock plus, detector, advertisement, ads, ad blocking
Requires at least: 3.9
Tested up to: 3.9.1
Stable tag: 2.1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Tired of missed opportunities because of pesky ad blocker browser extensions, add-ons, and plugins? Fight
back with Ad Blocking Detector today!

== Description ==
Tired of missed opportunities and empty spaces because of pesky ad blocking browser extensions, add-ons, and plugins?
Would you like to determine which of your site visitors use ad blockers and do something about it? Then this plugin
is for you! Show an ad to those without ad blockers, and something else entirely to those with them. Don't settle for 
lost profit!

- Fully compatible with WordPress multisite networks.
- Detects all major ad blocking browser extensions, including Ad Block Plus.
- Works with all major web browsers.
- Integrates with some other popular ad management plugins, such as AdRotate.

= How Does It Work? =
This plugin allows you to specify two alternative sections of content.  One for normal visitors, 
and the other for visitors using ad blockers, such as the popular Adblock Plus.  This content is
then tied to a WordPress [shortcode](http://codex.wordpress.org/Shortcode).  

Simply insert this shortcode anywhere shortcodes are supported, such as posts, pages, and the
sidebar (with a [simple tweak](http://www.wpbeginner.com/wp-tutorials/how-to-use-shortcodes-in-your-wordpress-sidebar-widgets/)), 
and the plugin will display the appropriate content based on whether it detects an ad blocker or not.

See a working demonstration at the [plugin's website](http://adblockingdetector.jtmorris.net/demo/).

= What Can I Display To Ad Block Wielding Visitors? =
Anything that ad blockers won't block. There is no way to turn off the visitor's ad blocker; however,
you can use the knowledge that they have an ad blocker to your advantage.  Display a signup form for
your email newsletter.  Pop in a description and link to your new eBook.  Even display a plea for
the user to disable their ad blockers if necessary.

There are other ways of engaging your site's visitors, when you can't display an ad, take advantage
of those other methods.


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
Copy and paste your desired shortcode into a Text widget added to your sidebar.


Note: By default, WordPress does not parse shortcodes in widgets.  You must enable that functionality manually.
You can find instructions from the folks at WPBeginner.com 
[here](http://www.wpbeginner.com/wp-tutorials/how-to-use-shortcodes-in-your-wordpress-sidebar-widgets/).

The short version is you must add the following code to your theme's *functions.php* file:
`add_filter('widget_text', 'do_shortcode');`


= How does this plugin treat visitors with JavaScript disabled? =
At this time, this plugin does not treat disabled JavaScript as an ad blocker.  
If the visitor has JavaScript disabled, the plugin displays the content used
for normal visitors with no ad blockers.

A future update will allow you to choose what disabled JavaScript means.


= Does this plugin prevent visitors with an ad blocker from visiting my site or in any other way obscure the content? =
No.  In an indirect and inefficient way, you *can* obscure large portions of content from visitors using this plugin,
but that is not what it was designed to do.  It simply places alternative content in the space the ad would have displayed.


= Why doesn't this plugin detect the _______________ ad blocker? =
Not all ad blockers operate the same way.  This plugin was tested with the most common ad blockers, but it is
possible we missed one.  If you find an ad blocker we aren't detecting, [contact us](http://adblockingdetector.jtmorris.net/contact/) and let us know the 
ad blocker, web browser, and operating system you are using.  We will then investigate the problem.


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