<?php
/*
Plugin Name: EP Hashimage
Plugin URI: http://darkwhispering.com/wordpress-plugins
Description: Display image by hashtag from twitter or instagram in your template, post/page or widget area using template tag, shortcode or the widget.
Author: Mattias Hedman
Version: 5.0.0.a01
Author URI: http://darkwhispering.com
*/

define('HASHIMAGE_VERSION', '5.0.0.a01');


require_once('core.php');
require_once('twitter.php');
require_once('instagram.php');
require_once('builder.php');
require_once('settings-page.php');

Class Hashimage extends Hashimage_Builder {

    function __construct($args = array())
    {
        parent::__construct();

        $this->settings = $this->get_settings($args);

        // Do the magic
        $this->_init();
    }

    private function _init() {

        if ($this->settings['network']['twitter'] && $this->settings['network']['instagram']) {
            
            // Both networks
            $twitter = new Twitter($this->settings);
            $instagram = new Instagram($this->settings);

            // Merge the two feeds togheter
            $merged_feed = $this->merge_feeds($twitter->init(), $instagram->init());

            // Clean the feed from duplicates and limit it to the amount we want to display
            $final_feed = $this->clean_feed($merged_feed);

            if ($this->settings['img_display'] === 'lightbox') {
                echo $this->build_lightbox($final_feed);
            }

            if ($this->settings['img_display'] === 'source') {
                echo $this->build_source($final_feed);
            }

        } elseif (!$this->settings['network']['twitter'] && $this->settings['network']['instagram']) {
            
            // Only Instagram
            $instagram = new Instagram($this->settings);
            
            // Create compelte feed
            $merged_feed = $this->merge_feeds($instagram->init());

            // Clean the feed from duplicates and limit it to the amount we want to display
            $final_feed = $this->clean_feed($merged_feed);

            if ($this->settings['img_display'] === 'lightbox') {
                echo $this->build_lightbox($final_feed);
            }

            if ($this->settings['img_display'] === 'source') {
                echo $this->build_source($final_feed);
            }

        } elseif ($this->settings['network']['twitter'] && !$this->settings['network']['instagram']) {
            
            // Only Twitter
            $twitter = new Twitter($this->settings);
            
            // Create compelte feed
            $merged_feed = $this->merge_feeds($twitter->init());

            // Clean the feed from duplicates and limit it to the amount we want to display
            $final_feed = $this->clean_feed($merged_feed);

            if ($this->settings['img_display'] === 'lightbox') {
                echo $this->build_lightbox($final_feed);
            }

            if ($this->settings['img_display'] === 'source') {
                echo $this->build_source($final_feed);
            }

        }

    }

}

function hashimage($args = array()) {
    new Hashimage($args);
}


//Frontpage JS and CSS
function hashimage_js() {
    wp_enqueue_script("jquery");
    wp_register_script('hashimage_js', plugins_url('js/slimbox2.js', __FILE__));
    // wp_register_script('hashimage_js_async', plugins_url('js/async.js', __FILE__));
    wp_enqueue_script('hashimage_js');
    // wp_enqueue_script('hashimage_js_async');
}
add_action('wp_print_scripts','hashimage_js');

function hashimage_css() {
    wp_register_style('hashimage_slimbox_css', plugins_url('css/slimbox2.css', __FILE__));
    wp_register_style('hashimage_css', plugins_url('css/style.css', __FILE__));
    wp_enqueue_style('hashimage_slimbox_css');
    wp_enqueue_style('hashimage_css');
}
add_action('wp_print_styles','hashimage_css');