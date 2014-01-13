<?php

// Load WP core, needed when doing request via ajax
if(!defined('ABSPATH')) require_once("../../../../wp-load.php");

// Load needed files
require_once('core.php');
require_once('twitter.php');
require_once('instagram.php');
require_once('builder.php');

// Lets do magic
Class Async extends Hashimage_Builder {

    function __construct()
    {
        parent::__construct();       
    }

    public function init($args = array()) {

        // Update default settings with the users settings
        $this->settings = $this->get_settings($args);

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

$hashimage = new Async();
echo $hashimage->init($_GET);
