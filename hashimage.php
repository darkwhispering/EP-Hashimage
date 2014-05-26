<?php
/*
Plugin Name: EP Hashimage
Plugin URI: http://darkwhispering.com/wordpress-plugins
Description: Display image by hashtag from twitter or instagram in your template, post/page or widget area using template tag, shortcode or the widget.
Author: Mattias Hedman
Version: 5.0.1
Author URI: http://darkwhispering.com
*/

// Set plugin version
define('HASHIMAGE_VERSION', '5.0.1');

// Load needed files
require_once('system/core.php');
require_once('system/twitter.php');
require_once('system/instagram.php');
require_once('system/builder.php');
require_once('system/settings-page.php');
require_once('system/shortcode.php');
require_once('system/widget.php');

// Lets do magic
Class Hashimage extends Hashimage_Builder {

    function __construct()
    {
        parent::__construct();       
    }

    /**
    * Main function, getting feeds and build content to return
    * 
    * @param array
    * @return string
    **/
    public function init($args = array()) {

        // Update default settings with the users settings
        $this->settings = $this->get_settings($args);

        // Request is async, create continer with all needed options
        if ($this->settings['async'] == true) {

            // Create async container
            return $this->build_async($this->settings);

        }

        // Request is not async, go ahead and load content
        else {

            if ($this->settings['network']['twitter'] && $this->settings['network']['instagram']) {
                
                // Both networks
                $twitter = new Twitter($this->settings);
                $instagram = new Instagram($this->settings);

                // Merge the two feeds together
                $merged_feed = $this->merge_feeds($twitter->init(), $instagram->init());

                // Clean the feed from duplicates and limit it to the amount we want to display
                $final_feed = $this->clean_feed($merged_feed);

                if ($this->settings['img_display'] === 'lightbox') {
                    return $this->build_lightbox($final_feed);
                }

                if ($this->settings['img_display'] === 'source') {
                    return $this->build_source($final_feed);
                }

            } elseif (!$this->settings['network']['twitter'] && $this->settings['network']['instagram']) {
                
                // Only Instagram
                $instagram = new Instagram($this->settings);
                
                // Create complete feed
                $merged_feed = $this->merge_feeds($instagram->init());

                // Clean the feed from duplicates and limit it to the amount we want to display
                $final_feed = $this->clean_feed($merged_feed);

                if ($this->settings['img_display'] === 'lightbox') {
                    return $this->build_lightbox($final_feed);
                }

                if ($this->settings['img_display'] === 'source') {
                    return $this->build_source($final_feed);
                }

            } elseif ($this->settings['network']['twitter'] && !$this->settings['network']['instagram']) {
                
                // Only Twitter
                $twitter = new Twitter($this->settings);
                
                // Create compelte feed
                $merged_feed = $this->merge_feeds($twitter->init());

                // Clean the feed from duplicates and limit it to the amount we want to display
                $final_feed = $this->clean_feed($merged_feed);

                if ($this->settings['img_display'] === 'lightbox') {
                    return $this->build_lightbox($final_feed);
                }

                if ($this->settings['img_display'] === 'source') {
                    return $this->build_source($final_feed);
                }

            }

        }

    }

}


/**
* Public open function, used for tempalte part, shortcode and widget
**/
function hashimage($args = array()) {
    $hashimage = new Hashimage();

    return $hashimage->init($args);
}


/**
* Load frontend JS and CSS
**/
function hashimage_js() {
    wp_enqueue_script("jquery");
    wp_register_script('hashimage_js', plugins_url('js/slimbox2.js', __FILE__));
    wp_register_script('hashimage_js_async', plugins_url('js/async.js', __FILE__));
    wp_enqueue_script('hashimage_js');
    wp_enqueue_script('hashimage_js_async');
}
add_action('wp_print_scripts','hashimage_js');

function hashimage_css() {
    wp_register_style('hashimage_slimbox_css', plugins_url('css/slimbox2.css', __FILE__));
    wp_register_style('hashimage_css', plugins_url('css/style.css', __FILE__));
    wp_enqueue_style('hashimage_slimbox_css');
    wp_enqueue_style('hashimage_css');
}
add_action('wp_print_styles','hashimage_css');