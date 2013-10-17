<?php

Class Hashimage_Core {

    function __construct()
    {
        self::$this;
    }

    /**
    * Default settings
    *
    * @param array
    * @return array
    **/
    function get_settings($args = array())
    {
        // Setup settings array
        $default_settings = array(
            'hashtag'           => 'cats',
            'limit'             => 10,
            'cache_time'        => 120,
            'type'              => 'plugin',
            'output'            => 'html',
            'network'           => array('twitter' => 1,'instagram' => 1),
            'networks'          => array('instagram.com', 'twitpic.com', 'yfrog.com'),
            'img_display'       => 'source',
            'img_sizes'         => array(
                                    'lightbox_w'        => 600,
                                    'lightbox_h'        => 400,
                                    'thumb_w'           => 100,
                                    'thumb_h'           => 100,
                                    'widget_thumb_w'    => 60,
                                    'widget_thumb_h'    => 60
                                ),
            'twitter_key'       => '',
            'twitter_secret'    => '',
            'client_id'         => ''
        );

        // Merge the network setting
        if (!empty($args['network'])) {
            $default_settings['network'] = wp_parse_args($args['network'], $default_settings['network']);
            unset($args['network']);
        }

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
    * @return string
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

    function clean_feed($feed = array())
    {

        // Clean form duplicates
        $clean = array_map('unserialize', array_unique(array_map('serialize', $feed)));

        // Slide the array so we only return the amount of images we want to display
        $sliced = array_slice($clean, 0 , $this->settings['limit']);

        return $sliced;

    }

}