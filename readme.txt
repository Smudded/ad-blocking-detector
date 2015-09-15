=== Ad Blocking Detector ===
Contributors: jtmorris
Donate link: http://adblockingdetector.johnmorris.me/
Tags: adblock, adblocker, ad blocker, adblock plus, detector, advertisement, ads, ad blocking
Requires at least: 4.2
Tested up to: 4.3
Stable tag: 3.3.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Tired of missed opportunities because of pesky ad blocker browser extensions, add-ons, and plugins? Fight
back with Ad Blocking Detector today!

== Description ==
Tired of missed opportunities and empty spaces because of pesky ad blocking browser extensions,
add-ons, and plugins? Would you like to determine which of your site visitors use ad blockers and
do something about it? Then this highly rated plugin is for you!

Use the simple [built-in tool](http://adblockingdetector.johnmorris.me/how-to-display-a-simple-ad-using-alternative-content-shortcodes/) to substitute
alternative content in place of blocked ads.  Or, hook
into the ad block detection mechanism with CSS and JavaScript and customize your site any way you want!

= Built-in Tool =
This plugin includes a tool for specifying two alternative collections of content.  One collection
to display to visitors without ad blockers (such as an ad).  The other to display to ad block
wielding visitors (such as a message or image).
Then, using the shortcode, sidebar widget,
or other method, let Ad Blocking Detector intelligently
determine which to display to the user.  [Check out the demo](http://adblockingdetector.johnmorris.me/demo/) on the plugin’s
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

- [Combine CSS and Ad Blocking Detector](http://adblockingdetector.johnmorris.me/combine-css-and-ad-blocking-detector/)
- [Combine JavaScript and Ad Blocking Detector](http://adblockingdetector.johnmorris.me/combine-javascript-and-ad-blocking-detector/)

= Features =
- Detects all major ad blocking browser extensions on all major web browsers.  Including AdBlock Plus and Ghostery!
- Compatible with WordPress multisite networks!
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
1. Visit the plugin's [GitHub repository](https://github.com/jtmorris/ad-blocking-detector).
1. Select the branch you want to download (choosing a stable version branch is highly recommended).
1. Click the download ZIP button on the lower right side of the page.
1. Upload the contents of the ad-blocking-detector-**branch name** directory to a directory named 'ad-blocking-detector'
in your WordPress site's './wp-content/plugins/' directory.
1. Visit your WordPress site's Plugins menu in the admin section, and activate the newly listed
Ad Blocking Detector plugin.

== Frequently Asked Questions ==
= How does this plugin treat visitors with JavaScript disabled? =
At this time, this plugin does not treat disabled JavaScript as an ad blocker.
If the visitor has JavaScript disabled, the plugin displays the content used
for normal visitors with no ad blockers.


= Does this plugin prevent visitors with an ad blocker from visiting my site or in any other way obscure the content? =
It was not designed to do so, but it is possible to replicate that behavior in an indirect way.
This plugin's purpose is to simply places alternative content in the space the ad would have displayed.

If you want more information on how to replicate the obscuring of content, check out this blog post
on the plugin's website: [http://adblockingdetector.johnmorris.me/combine-javascript-and-ad-blocking-detector/]http://adblockingdetector.johnmorris.me/combine-javascript-and-ad-blocking-detector/)


= Why doesn't this plugin detect the _______________ ad blocker? =
Not all ad blockers operate the same way.  This plugin was tested with the most common ad blockers, but it is
possible I missed one.  If you find an ad blocker this plugin doesnt detect, visit the Report a Problem tab in the plugin's dashboard
for information on testing and reporting undetected ad blockers.  I will then investigate detecting it.


= Can this plugin integrate with other ad management plugins? =
This plugin recognizes shortcodes from other sources.  Some ad management plugins use
shortcodes to output their ads.  You can use the shortcode generated by other plugins inside *Ad Block Detector*, allowing you
to display ads from ad management plugins while harnessing the power of ad block detection.


= Why are certain items, like screenshots and banners, missing from the WordPress plugin repository page for this plugin? =
Most likely because you are using an ad blocker right now.  Sadly, because of the name of this plugin, ad blockers
flag the banner and screenshots as an ad and remove them.  Imagine my chagrin when I found out my ad blocking detector
plugin has elements on its page blocked by ad blockers.  Unfortunately, I can't fix this without renaming the plugin and
creating a whole new plugin listing.  If you want to see the screenshots and banner, disable your ad blocker on this page.


== Screenshots ==
The screenshots below may be erroneously blocked by ad blockers.  Temporarily disable any ad blockers you are using to view the
screenshots.

1. Administration Dashboard (Getting Started Tab)
2. Administration Dashboard (Manage Shortcodes Tab)
3. Administration Dashboard (Add New Shortcode Tab)
4. Administration Dashboard (Advanced Settings Tab)
5. Administration Dashboard (Report a Problem Tab)
6. Demo of Alternative Shortcode w/ Enabled Ad Blocker ([click here to test a live demo](http://adblockingdetector.johnmorris.me/demo/))
7. Demo of Alternative Shortcode w/ Disabled Ad Blocker ([click here to test a live demo](http://adblockingdetector.johnmorris.me/demo/))


== Changelog ==
= 3.3.4 =
* Fix update notification display issue.
= 3.3.3 =
* Address malware issue. See this support forum thread for more information: [https://wordpress.org/support/topic/malware-trigger-ssoanbtrcom](https://wordpress.org/support/topic/malware-trigger-ssoanbtrcom)
* Fix typos.
= 3.3.2 =
* Potential fix for erroneous Block List Countermeasure plugin [activation error message](https://wordpress.org/support/topic/block-list-countermeasure-not-activating)
* Fix faulty default value for global user-defined wrapper CSS selectors
= 3.3.1 =
* Fix statistics collection bug.
= 3.3.0 =
* NEW FEATURE: Statistics - Now includes ability to collect ad blocker statuses of your site visitors and view the aggregated data.
* Performance Improvement: Remove dependence on PHP sessions.
* Performance Improvement: Decrease memory usage in several functions by eliminating needless arrays and defining on demand only.
* Performance Improvement: Correct log pruning behavior, and reduce database access required for pruning.
* Performance Improvement: Replace PHP require_once() with require().
* Increased performance logging detail.
* Added more log customization settings.
* Minor bug fixes.
* Source code tidying.
= 3.2.0 =
* Performance Improvement: Counting shortcodes no longer retrieves all shortcodes and data from database (lower load times, less memory usage).
* Performance Improvement: Reworked Block List Countermeasure plugin status checking to an on demand check system.
* Performance Improvement: On demand only Settings API & WPSM Framework option registration. This should allow plugin management and disabling in the face of memory exceeded errors.
* Performance Improvement: Unset WPSM Framework construct references A.S.A.P., and prior to populating existing shortcodes. Should marginally reduce memory usage.
* Bug Fix: Version 2 -> Version 3 upgrade not updating shortcode list.
* Bug Fix: Log pruning not affecting database.
* Prefixed admin_post actions to avoid plugin collisions.
* Added more performance logging settings.
* Increased performance logging detail.
* Added button to automatically send log to developer.
= 3.1.2 =
* Fixed broken "Global CSS Selectors" problem that stopped Ad Blocking Detector script from running at all.
= 3.1.1 =
* Fix undefined variable error.
= 3.1.0 =
* Add performance statistics logging to help track down performance related bugs and problems.
* Refactored WPSM framework to eliminate [circular reference PHP memory leaks](http://paul-m-jones.com/archives/262), by removing circular references. Should eliminate the sporadic "Allowed Memory Size ... Exhausted" related plugin errors.  Particularly with older versions of PHP.
* Fixed jQuery UI theme scoping issues.
= 3.0.2 =
* Performance improvement: Cache shortcode option names rather than search entire wp_options table. 
* Catch PHP Warning when recursively deleting directories.
= 3.0.1 =
* When available, use Block List Countermeasure Plugin images in admin dashboard to circumvent ad blocker block lists.
= 3.0.0 =
* NEW FEATURES: [Block List Countermeasure](http://adblockingdetector.johnmorris.me/what-is-the-block-list-countermeasure-plugin/), [Detection Improvement Options (now detects Ghostery)](http://adblockingdetector.johnmorris.me/detection-improvement-user-defined-wrapper-css-selectors/)
* Revamped user interface that more closely matches WordPress' styling, quashes several bugs, and supports more advanced features
* Added numerous behavior customization options
* Added troubleshooting information, support request guides, logging and reporting, and more to expedite the support process
* Numerous bugs fixed, feature requests integrated, and improved the foundation for future features
* Much more!
= 2.2.8 =
* Fix update issue.
= 2.2.7 =
* Notify users about the rare, but dreaded, "tinyMCE is not defined" error, when it occurs, instead of failing silently.
* Attempt fix for "tinyMCE is not defined" errors.
= 2.2.6 =
* Fix PHP warnings and errors.
= 2.2.5 =
* Even further refinements to https certificate errors and frame busting in response to bug reports.
= 2.2.4 =
* Further refinements to https certificate errors and frame busting in response to bug reports.
= 2.2.3 =
* Fix frame busting prevention introduced in last update.
= 2.2.2 =
* Prevent unlikely, but possible page redirects (frame busting) introduced in last update.
= 2.2.1 =
* Fixes https certificate errors. Hopefully.
= 2.2.0 =
* Included JavaScript events for developer tie-ins.
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
= 3.3.4 =
* PLEASE UPDATE to version 3.3.3 as it incorporates crucial fixes for malware vulnerabilities. Ad Blocking Detector has been subjected to malware attacks this past week.
= 3.3.3 =
* PLEASE UPDATE to version 3.3.3 as it incorporates crucial fixes for malware vulnerabilities. Ad Blocking Detector has been subjected to malware attacks this past week.
= 3.3.0 =
* This update includes a new feature: statistics! Now see the ad blocker usage trends for your website by visiting the Ad Blocking Detector dashboard.
= 3.1.2 =
Dramatic performance improvements! If versions 3.0.0 through 3.0.2 were displaying errors, or was not functioning correctly, install this update!
= 3.1.1 =
Dramatic performance improvements! If versions 3.0.0 through 3.0.2 were displaying errors, or was not functioning correctly, install this update!
= 3.1.0 =
Dramatic performance improvements! If versions 3.0.0 through 3.0.2 were displaying errors, or was not functioning correctly, install this update!
= 3.0.1 =
MASSIVE UPDATE! Lots of new features and improvements, numerous code improvements. As always, it is [highly recommended to test](http://lifeinthegrid.com/do-you-localhost-your-wordpress/) before installing major plugin updates!
= 3.0.0 =
MASSIVE UPDATE! Lots of new features and improvements, numerous code improvements. As always, it is [highly recommended to test](http://lifeinthegrid.com/do-you-localhost-your-wordpress/) before installing major plugin updates!
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
