<?php

Class Hashimage_Core {

    function __construct()
    {
        self::$this;
    }

    /**
    * Default settings
    * @access public
    * @param array
    * @return array
    **/
    function get_settings($args = array())
    {

        $default_img_sizes = array(
            'lightbox_w'        => 600,
            'lightbox_h'        => 400,
            'thumb_w'           => 100,
            'thumb_h'           => 100,
            'widget_thumb_w'    => 60,
            'widget_thumb_h'    => 60
        );

        // Setup settings array
        $default_settings = array(
            'async'             => true,
            'hashtag'           => 'cats',
            'limit'             => 10,
            'type'              => 'plugin',
            'output'            => 'html',
            'cache_time'        => (get_option('ep_hashimage_cache_time')) ? get_option('ep_hashimage_cache_time') : 1200,
            'network'           => (get_option('ep_hashimage_network')) ? get_option('ep_hashimage_network') : array('twitter', 'instagram'),
            'networks'          => (get_option('ep_hashimage_networks')) ? get_option('ep_hashimage_networks') : array('twitpic', 'yfrog', 'instagram'),
            'img_display'       => (get_option('ep_hashimage_img_display')) ? get_option('ep_hashimage_img_display') : 'source',
            'img_sizes'         => (get_option('ep_hashimage_img_sizes')) ? get_option('ep_hashimage_img_sizes') : $default_img_sizes,
            'twitter_key'       => (get_option('ep_hashimage_twitter_key')) ? get_option('ep_hashimage_twitter_key') : '',
            'twitter_secret'    => (get_option('ep_hashimage_twitter_secret')) ? get_option('ep_hashimage_twitter_secret') : '',
            'client_id'         => (get_option('ep_hashimage_client_id')) ? get_option('ep_hashimage_client_id') : ''
        );

        // Merge img_sizes settings
        if (!empty($args['img_sizes'])) {
            $default_settings['img_sizes'] = wp_parse_args($args['img_sizes'], $default_settings['img_sizes']);
            unset($args['img_sizes']);
        }

        // Merge default settings with the new arguments from the user
        $settings = wp_parse_args($args, $default_settings);

        // Return settings
        return $settings;
    }

    /**
    * Format date
    * 
    * @param string
    * @param bool
    * @return string
    **/
    function format_date($date, $string = false)
    {

        if ($string) {
            $date = strtotime($date);
        }

        $formated = 'date_'.date('YmdHis', $date);

        return $formated;
    }

    /**
    * Make sure hashtag include a hash or exluded
    * 
    * @param string
    * @param bool
    * @return string
    **/
    function validate_hash($hashtag, $add = true)
    {

        if ($add) {
            $hashtag = '#'.str_replace('#','',$hashtag);
        } else  {
            $hashtag = str_replace('#','',$hashtag);
        }

        return $hashtag;
    }

    /**
    * Fetch url
    * 
    * @param string
    * @return json
    **/
    function fetch_url($url)
    {

        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER  => 1,
                CURLOPT_URL             => $url,
                CURLOPT_USERAGENT       => 'EP Hashimage Wordpress Plugin'
            )
        );
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;

    }

    /**
    * Merge feeds
    * 
    * @param array
    * @param array
    * @return array
    **/
    function merge_feeds($f1 = array(), $f2 = array())
    {

        $feed1 = array();
        $feed2 = array();

        if ($f1) {

            // Create new key based on date
            foreach ($f1 as $item) {

                $new_key = $item['date'];
                unset($item['date']);

                $feed1[$new_key] = $item;
            }

        }

        if ($f2) {

            // Create new key based on date
            foreach ($f2 as $item) {
                $new_key = $item['date'];
                unset($item['date']);

                $feed2[$new_key] = $item;
            }

        }

        // Merge the two feeds
        $merge = array_merge($feed1, $feed2);

        // Sort them by the new date key, but oldest key will get first in array here...
        ksort($merge);

        // ... so lets do an array reverse so we get newest images is first in
        $images = array_reverse($merge);

        return $images;

    }

    /**
    * Clean the feed of duplicates
    * 
    * @param array
    * @return array
    **/
    function clean_feed($feed = array())
    {

        // Clean form duplicates
        $clean = array_map('unserialize', array_unique(array_map('serialize', $feed)));

        // Slide the array so we only return the amount of images we want to display
        $sliced = array_slice($clean, 0 , $this->settings['limit']);

        return $sliced;

    }

}