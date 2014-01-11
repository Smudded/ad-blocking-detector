Ad Blocking Detector
====================
A WordPress plugin that allows users to input two distinct code sections.  One section
is displayed if no common ad blocking software or browser plugins is placed.  The other
is displayed if ad blocking is detected.

Picture this in your mind.  You're running a successful WordPress website which uses a tasteful
Google AdSense ad in the sidebar to generate a bit of revenue.  Got that pictured?

Good, now let me ruin that beautiful picture.  As of 2013, 
[22.7% of web surfers block ads](http://www.forbes.com/sites/kashmirhill/2013/08/21/use-of-ad-blocking-is-on-the-rise/)!  
If your site caters to more tech-minded individuals, that percentage is dramatically larger.

This plugin attempts to insert some item on your site (presumably an ad), but if it detects common ad blockers, 
such as [Adblock Plus](https://adblockplus.org), it displays alternative content instead.


Install
========
This plugin is installed like any other WordPress plugin.  You can it through [WordPress.org](http://wordpress.org/plugins/ad-blocking-detector/), or 
[install it manually](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).
To install manually, download all the files, and copy to a directory named adblock-detector in your WordPress install's **wp-content/plugins** directory.
Then go to the Plugins screen in your WordPress admin section, find the new plugin in the list and activate it.



Usage
======
This plugin adds a new menu item to your WordPress admin section entitled **Adblock**.  Detailed usage instructions are provided on the plugin's
page.  The short version is explained below.

**Ad Blocking Detector** uses WordPress shortcodes to achieve its goal.  You create a shortcode through the admin page.  When the shortcode is resolved
by WordPress, the plugin displays the appropriate content based on whether an ad blocker is detected.  Several sample shortcodes are added by default 
to get you started.



Tips, Cautions, and Recommendations
====================================
Using a tool like this is circumventing the wishes of your site's visitors. They use an ad blocker for a reason. Displaying a different gaudy advertisement
or berating the visitor will do more harm than good.  If done in significant quantities, it will also make tools like this a target
for the ad blocker developers and maintainers.

Because of this, I recommend you display something besides an alternative ad or a plea to disable their ad blocker.  This is a perfect way to get visitors to
sign up for your newsletter, follow you on social media, donate, or any number of other ideas.  Displaying something different will be much more
effective and well-received.

Keeping your alternative content simple and tasteful will keep your visitors happy and ensure tools like this continue to function as expected.



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