=== EP Hashimage ===
Contributors: Earth People, fjallstrom, darkwhispering
Tags: tags, hashtag, hashimage, twitter, twitpic, instagram, flickr, yfrog, plixi, plugin, social, images, image, widget, thumbnails, async
Requires at least: 3.3.0
Tested up to: 3.4.2
Stable tag: 3.1.1

Display image by hashtag from twitter or instagram in your template, post/page or widget area using template tag, shortcode or the widget.

== Description ==

This plugin search after hashtagged images on twitter and/or Instagram.

The Twitter search can fetch images from the following networks in the twitter search result.

* twitpic
* instagram
* yfrog
* plixi
* flickr
* pic.twitter.com.

URL’s are being curled and cached for 10-12 minutes using the Wordpress Transients API.

The plugin, when enabled, exposes a template tag which you can add to your theme:

`<?php
if(function_exists('hashimage')){
 echo hashimage('hashtag=unicorn&limit=5');
}
?>`

or you use the shortcode in your post and pages

`[hashimage hashtag="unicorn" limit="5"]`

or add the widget in any of your widget areas.

You will get an optionpage in the settings meny section with settings for thumbnails sizes in widget and shortcode. There is also an option for async loading, with that on `true` your page will load faster, show image in lightbox or original source, what network to search in and if the plugin should autoload every 15 min.

Some notes/known bugs:

* The Twitter API only returns the latest results when searching on hashtag, so the result set is limited due to this.
* The Instagram API required a client_id that you need to get from http://instagram.com/developer/
* The Instagram API limits the search result to around max 20 images right now.
* A documentation page is on the why with better info on how to use the plugin.
* This beta will break old settings! Make sure you go over your settings again.

== Installation ==

1. Upload the zipped file to yoursite/wp-content/plugins
2. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

1. Admin settings page
2. Images displayed in a post using shortcode
3. Clicked image in lightbox
4. Widget options
5. Widget displayed on site

== Frequently Asked Questions ==

= The hashimage dont show anything! =

If the plugin don't show anything for you this is probably due to that the search result from twitter it empty. Test with another hashtag or do a search in twitter.com with the hashtag you want to use. If there is no results on twitter.com you found why the plugin is blank on your site.

= A search on twitter give results but I'm not getting anything on my site. =

If you are using version 2.3.0, please upgrade to the latest version. the 2.3.0 version has a bug in the async option that can cause this behavior.

If you are running the latest version, try to deactivate all other plugins to see if that help. Also check that the uploads folder have read/write permissions.

= I'm getting a lot of broken links. =

The plugin stores the cache folder in wordpress uplaods folder. If you get broken images the plugin is unable to create the cache files. Make sure that the uploads folder is read/writable.

*The versions below 2.3.3 of the plugin used the webservers default temp folder as cache folder. For the most the will work perfect, but in some cases it might not and you end up with broken images as result. A quick fix for this is to go into the plugin folder and change the cache folder path in timthumb-config.php. This might be updated as an option in the settings page in the future.*

== Changelog ==

= 4.0.0b01 =
* Most of the plugin has been rewritten to make it more flexible and easier to use.
* Added support to search for images directly on instagram (instagram client_id key needed).
* In beta, expect that thers might be some bug and errors if you try it.
* This beta will break old settings! Make sure you go over your settings again.

= 3.1.1 =
* Fixed missing expire time for the caching

= 3.1.0 =
* Re-written the caching, now using the Wordpress Transients API which is speeding up the plugin a lot.
* Fixed a bug with the lightbox not working properly.
* Added right time value to the autoload.

= 3.0.0 =
* Added the option to choose what networks to search in. See the option page.

= 2.4.0 =
* Added the option to view the image in lightbox or at the original source

= 2.3.5 =
* Added animated loading image to the loadning text when using async option.
* The plugin no longer requires PHP 5.3, now has the same requirements as Wordpress

= 2.3.4 =
* Fixed broken instagram images due to url change at instagram. Thanks to **ndenitto** for the info.

= 2.3.3 =
* move cache dir location from php tmp uplaod folder to wordpress uploads folder as default.

= 2.3.2 =
* Changed to checkboxes for async option in settings page.
* Fixed bug with async option not saving true value.
* Now renders proper html code (ul list).
* Added global css file with minimal css code to fix default ul li style.
* Updated screenshot of admin settings page

= 2.3.1 =
* Fixed bug with the new true/false async option. Now forcing true/false even if 1/0 is set as value.

= 2.3.0 =
* Added pic.twitter.com as supported service.
* Added async option to do all the heavy lifting after page load.
* Changed the timthumb cropping in the lightbox. No more cropped and cut images, it now displays the entire image scaled down.

= 2.2.1 =
* Updated to latest timthumb v2.8.10

= 2.2.0 =
* Added a widget.
* Added widget thumbnails size in settings page.

= 2.1.3 =
* Moved all code to one file to fix problems with network activate in Wordpress networks
* CLeaned up the code here and there
* Now requires PHP 5.3 as minimum

= 2.1.2 =
* Added missing default values for thumbnails and lightbox image sizes.
* Fixed bug when update variable where empty.

= 2.1.1 =
* Added timthumb resize
* Added slimbox lightbox
* Added settings page for thumbnails and lightbox image sizes.
* Released on Wordpress Plugins Directory

= 2.0.1 =
* added support for wp shorttags.

= 2.0.0 =
* finally got around to fix the problem imposed by twitter's built-in url shortener + made caching work.

= 1.0.0 =
* initial release