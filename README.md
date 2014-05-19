Ad Blocking Detector - WordPress Plugin.
====================
Tired of missed opportunities and empty spaces because of pesky ad blocking browser extensions, add-ons, and plugins?
Would you like to determine which of your site visitors use ad blockers and do something about it? Then this plugin
is for you! Show an ad to those without ad blockers, and something else entirely to those with them. Don't settle for 
lost profit!  Use the Ad Blocking Detector WordPress plugin.

- Fully compatible with WordPress multisite networks.
- Detects all major ad blocking browser extensions, including Ad Block Plus.
- Works with all major web browsers.
- Integrates with some other popular ad management plugins, such as AdRotate.

How Does It Work?
--------------------
This plugin allows you to specify two alternative sections of content.  One for normal visitors, 
and the other for visitors using ad blockers, such as the popular Adblock Plus.  This content is
then tied to a WordPress [shortcode](http://codex.wordpress.org/Shortcode).  

Simply insert this shortcode anywhere shortcodes are supported, such as posts, pages, and the
sidebar (with a simple tweak), and the plugin will display the appropriate content based on
whether it detects an ad blocker or not.

See a working demonstration at the [plugin's website](http://adblockingdetector.jtmorris.net/demo/).

What Can I Display To Ad Block Wielding Visitors?
------------------------------------------------------
Anything that ad blockers won't block. There is no way to turn off the visitor's ad blocker; however,
you can use the knowledge that they have an ad blocker to your advantage.  Display a signup form for
your email newsletter.  Pop in a description and link to your new eBook.  Even display a plea for
the user to disable their ad blockers if necessary.

There are other ways of engaging your site's visitors, when you can't display an ad, take advantage
of those other methods.


Installation
=================
Using WordPress.org's Plugin Repository (recommended)
---------------------------------------------------------
1. Search for "Ad Blocking Detector" in your WordPress "Add New" plugin section of your dashboard.
1. Install and activate the "[Ad Blocking Detector](http://wordpress.org/plugins/ad-blocking-detector/)" plugin by John Morris.
1. Visit the newly added "Ad Blocking" menu in your admin section to get started.


Manually
-------------------
1. Download the latest version of adblock-detector and upload the `adblock-detector.zip` file 
in the Add New plugin section of your dashboard.
1. Activate the plugin through the "Plugins" menu in your WordPress admin section.
1. Visit the newly added "Ad Blocking" menu in your admin section to get started.


From Source
------------------
1. Visit the plugin's [GitHub repository](https://github.com/jtmorris/adblock-detector).
1. Select the branch you want to download (leaving the default master branch is highly recommended).
1. Click the download ZIP button on the lower right side of the page.
1. Upload the contents of the adblock-detector-**branch name** directory to a directory named 'adblock-detector'
in your WordPress site's './wp-content/plugins/' directory.
1. Visit your WordPress site's Plugins menu in the admin section, and activate the newly listed
Ad Blocking Detector plugin.



PLEASE REPORT PROBLEMS
=======================
Bugs and conflicts aside, ad blockers and the block lists they use are updated frequently.  Eventually, the methods used to detect ad blockers 
may stop working.  If you discover this happening, please open up an issue.  Knowing what web browser, ad blocker, and content you are displaying 
will speed up fixing the plugin.



Future Additions
==================
I hope to polish the features this plugin currently uses and add a few new features as time progresses.  Some of the anticipated
new features are listed below.

* Statistics on how many visitors use ad blockers.
* Add option to display the ads are blocked content if Javascript is disabled
  * This plugin detects ad blockers using Javascript, and most ads are served using Javascript.  Therefore, if a visitor has Javascript disabled,
    they will probably not see any ads, and the alternative content will not be displayed.



Plugin Limitations
===================
* This plugin relies on Javascript.  If a visitor comes to your site with Javascript disabled, this plugin will display the content used when no
  ad blockers are detected.  The number of people browsing the Internet with Javascript disabled is quite small; however, as it stands now, this is
  an easy way to remove most ads and prevent this plugin from knowing about it.

* By default, Wordpress does not allow shortcodes in sidebar widgets.  This functionality must be enabled manually if you wish to use this in a widget.
  Instructions for doing so can be found [here](http://www.wpbeginner.com/wp-tutorials/how-to-use-shortcodes-in-your-wordpress-sidebar-widgets/).