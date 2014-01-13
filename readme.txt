=== EP Hashimage ===
Contributors: darkwhispering, Earth People, fjallstrom
Tags: tags, hashtag, hashimage, twitter, twitpic, instagram, flickr, yfrog, plugin, social, images, image, widget, thumbnails, async, lightbox, slimbox, timthumb
Requires at least: 3.3.0
Tested up to: 3.8.0
Stable tag: 5.0.0

Display image by hashtag from twitter or instagram in your template, post/page or widget area using template tag, shortcode or the widget.

== Description ==

**ABOUT**

This plugin search after hashtagged images on twitter and/or Instagram.

The Twitter search can fetch images from the following networks in the twitter search result.

* twitpic
* instagram
* yfrog

URL's are being curled and cached using the Wordpress Transients API. All content is loaded asynchronously, to have minimal impact on your sites loading time. It also refresh the feed asynchronously after the set cache time in the settings page. Default 20 minuter.

The plugin, when enabled, exposes a template, shortcode, widget and a settings page.

**New documentation page for version 5 is available [here](http://darkwhispering.com/documentation/hashimage)**

**Some notes**

* The Twitter API required a consumer key and secret that you need to get from https://dev.twitter.com [Follow this guide](http://darkwhispering.com/how-to/get-twitter-consumer-key-and-secret ) if you don't know how to get one.
* The Instagram API are using odd limits. If you set a limit of eg 20, you will never get 20 images back, it find 30 and then remove any that is private and give back all the images after that. A result can therefor be different on each request. I will be looking into a workaround for this in the future.

== Installation ==

1. Upload the zipped files content to yoursite/wp-content/plugins
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Make sure your uploads folder is writeable.
4. [Read the documentation](http://darkwhispering.com/documentation/hashimage) to get started, you need to get keys and secrets from Twitter and Instagram.

== Upgrade Notice ==
Upgrading to version 5 from any version below **WILL** break your installed EP Hashimage plugins settings. After install, be sure to go over your settings again including widgets.

If the widget won't work, remove it from the widgeta area and read add it.

== Screenshots ==

1. Admin settings page
2. Images displayed in a post using shortcode
3. Clicked image in lightbox
4. Widget options
5. Widget displayed on site

== Frequently Asked Questions ==

= I'm getting a lot of broken links. =

The plugin stores the cache folder in wordpress uploads folder. If you get broken images the plugin is unable to create the cache files. Make sure that the uploads folder is read & writable.

= It only displayes about 50 images when I set my limit to 100+ =

Sadly, there are some limitations in both Twitter and Instagram.

Instagram only return about 20-30 images on a search. You can find more information about it [here](http://stackoverflow.com/questions/12322028/what-is-the-maximum-number-of-requests-for-instagram)

Also, the Instagram API are using odd limits. If you set a limit of eg 20, you will never get 20 images back, it finds 20 and then remove any that is private and give back all the images after that. A result can therefor be different on each request. I will be looking into a workaround for this in the future.

Twitter only return results from the lates 6-8 days, and most of the time this result in 50 - 70 items depending on how popular hashtag you use. You can read more about this in the twitter search api documentations [here](https://dev.twitter.com/docs/using-search)

== Changelog ==

= 5.0.0 =
* Plugin completely re-written
* Updated to support the changes made in the Twitter API, July 2013
* Updated Slimbox (lightbox) to version 2.05
* Updated Timthumb to version 2.8.13
* Added Twitter consumer key settings option
* Added Twitter consumer secret settings option
* Added cache time settings option
* Fixed issues with caching of the images
* Removed plixi as networks to search for on Twitter
* Removed pic.twitter.com as networks to search for on Twitter
* Removed Flickr as networks to search for on Twitter
* Removed async option from settings page. Plugin will always load asynchronously.
* Removed auto refresh option. It is now always auto refreshing and using the cache time a refresh time.
* Tested on WP 3.8

= 4.0.1 =
* Tested on Wordpress 3.5
* Better message after clearing cache on the settings page
* Fixed broken style on save button in wordpress 3.5

= 4.0.0 =
* You can now navigate between images in the lightbox
* Autoload function not work more smoothly and displayes a loader when running
* Remove cache tool added to settings page
* Delete timthumb cache when plugin is deactivated
* Delete cache in database when plugin is deactivated
* + all fixed and updates done in the beta versions

= 4.0.0b02 =
* Fixed problem with refresh/autoload.
* Renamed settings.php to settings-page.php for WP network support (having a settings.php breaks WP Network admin for some reason)
* Better path handling to cache folder. Now works on wp sites that is hosting the installation in its own folder.

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