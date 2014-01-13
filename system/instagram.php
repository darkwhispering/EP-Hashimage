<?php 
/*
*
* Get images from Instagram API
* by a specifig tag
*
* Instagram API v1 used and app auth method
*
*/

class Instagram extends Hashimage_Core {

    function __construct($settings)
    {
        parent::__construct();

        // Plugin settings
        $this->settings = $settings;

        // Instagram API with hashtag and client_id
        $this->url = 'https://api.instagram.com/v1/tags/'.urlencode($this->validate_hash($this->settings['hashtag'], false)).'/media/recent?count='.$this->settings['limit'].'&client_id='.$this->settings['client_id'];
    }

    function init()
    {

        // Check if the cache has expired or not, if, update the content
        if (!$images = get_site_transient('instagram_feed_'.$this->settings['hashtag'])) {

            $feed = json_decode($this->fetch_url($this->url));

            // Process the results from instagram
            if (!empty($feed->data)) {

                foreach ($feed->data as $result) {

                    if (!empty($result->link) && !empty($result->images->standard_resolution->url)) {

                        $images[md5($result->images->standard_resolution->url)]['img']      = $result->images->standard_resolution->url;
                        $images[md5($result->images->standard_resolution->url)]['source']   = $result->link;
                        $images[md5($result->images->standard_resolution->url)]['date']     = $this->format_date($result->created_time);

                    }

                }

            }

            set_site_transient('instagram_feed_'.$this->settings['hashtag'], $images, $this->settings['cache_time']);

        }       

        return $images;

    }

}