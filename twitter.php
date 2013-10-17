<?php 
/*
*
* Search twitter posts with a specifict hashtag
* and collect all images from different sites
* and networks to be displaied on the site.
*
* Twitter API v1.1 used and app auth method
*
*/

class Twitter extends Hashimage_Core {

    function __construct($settings)
    {

        parent::__construct();

        // Plugin settings
        $this->settings = $settings;

        // Get Twitter auth token
        $this->token = $this->_twitter_token($this->settings['twitter_key'], $this->settings['twitter_secret']);

        // Twitter Search URL
        $this->url = 'https://api.twitter.com/1.1/search/tweets.json?lang=all&include_entities=true&result_type=recent&count='.$this->settings['limit'].'&q=';

        // Add hashtag to search url
        $this->url .= urlencode($this->validate_hash($this->settings['hashtag']).' ');
        
        // Check what networks within twitter we should use and add to the search url
        $this->url .= urlencode(implode(' OR ',$this->settings['networks']));

        // Make sure the tweet have a link, will filter out all non needed tweets that don't link to any image
        $this->url .= urlencode(' filter:links');

    }

    /**
    * All heavy lifting is done here, collecting images, sort them and remove dublicates
    **/
    function init()
    {

        // Check if the cache has expired or not, if, update the content
        if (!$images = get_site_transient('twitter_feed_'.$this->settings['hashtag'])) {

            $feed = $this->_fetch_twitter($this->url, $this->token);
            $images = $this->_filter_images($feed);

            set_site_transient('twitter_feed_'.$this->settings['hashtag'], $images, $this->settings['cache_time']);

        }        

        return $images;

    }


    /**
    * Filter out all images/image urls from the feed
    *
    * @param array
    * @return array
    **/
    private function _filter_images($feed) 
    {
        $images     = array();
        $links      = array();
        $combined   = array();

        if (!empty($feed->statuses)) {

            foreach ($feed->statuses as $tweet) {

                // If it is twitter media/image
                if (isset($tweet->entities) && isset($tweet->entities->media)) {

                    foreach ($tweet->entities->media as $image) {

                        if (!empty($image->media_url) && !empty($image->url)) {

                            $images[md5($image->media_url)]['img']      = $image->media_url;
                            $images[md5($image->media_url)]['source']   = $image->url;
                            $images[md5($image->media_url)]['date']     = $this->format_date($tweet->created_at, true);

                        }

                    }

                }

                // If it is links to other networks
                if (isset($tweet->entities) && isset($tweet->entities->urls)) {

                    foreach ($tweet->entities->urls as $url) {

                        if (!empty($url->expanded_url) && !empty($url->url)) {

                            $links[md5($url->expanded_url)]['img']      = $url->expanded_url;
                            $links[md5($url->expanded_url)]['source']   = $url->url;
                            $links[md5($url->expanded_url)]['date']     = $this->format_date($tweet->created_at, true);

                        }

                    }

                }

            }

            $combined = array_merge($this->_extractimages($links), $images);

            if ($combined) {
                // Remove any doubles
                $combined = array_map('unserialize', array_unique(array_map('serialize', $combined)));

                // Remove images without img url or source url
                foreach ($combined as $key => $image) {
                    if(empty($image['img']) || empty($image['source'])) {
                        unset($combined[$key]);
                    }
                }
            }

            return $combined;

        } else {
            return array();
        }
    }
    

    /**
    * Fetch twitter feed
    *
    * @param string
    * @param string
    * @return array
    **/
    private function _fetch_twitter($url, $twitter_token) 
    {

        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_HTTPHEADER      => array('Authorization: Bearer '.$twitter_token),
                CURLOPT_RETURNTRANSFER  => 1,
                CURLOPT_URL             => $url,
                CURLOPT_USERAGENT       => 'EP Hashimage Wordpress Plugin'
            )
        );
        $result = json_decode(curl_exec($curl));
        curl_close($curl);

        return $result;

    }

    /**
    * Extract the images from the data returned
    *
    * @param string
    * @return array
    **/
    private function _extractimages($links){
        if($links){
            foreach($links as $link){

                // yfrog.com
                if (stristr($link['img'],'yfrog.com'))
                {
                    $images[md5($link['img'])]['img']       = $this->_extractyfrog($link['img']);
                    $images[md5($link['img'])]['source']    = $link['source'];
                    $images[md5($link['img'])]['date']      = $link['date'];
                }
                // instagr.am
                else if (stristr($link['img'],'instagram.com'))
                {
                    $images[md5($link['img'])]['img']       = $this->_extractinstagram($link['img']);
                    $images[md5($link['img'])]['source']    = $link['source'];
                    $images[md5($link['img'])]['date']      = $link['date'];
                }
                // twitpic.com
                else if (stristr($link['img'],'twitpic.com'))
                {
                    $images[md5($link['img'])]['img']       = $this->_extracttwitpic($link['img']);
                    $images[md5($link['img'])]['source']    = $link['source'];
                    $images[md5($link['img'])]['date']      = $link['date'];
                }
                // flic.kr
                // else if (stristr($link['img'],'flic.kr'))
                // {
                //     $images[md5($link['img'])]['img']       = $this->_extractflickr($link['img']);
                //     $images[md5($link['img'])]['source']    = $link['source'];
                // }
            }

            return $images;
        }
    }

    /**
    * Extract yfrog images
    *
    * @param string
    * @return string
    **/
    private function _extractyfrog($link){
        return trim($link,'”."').':iphone';
    }

    /**
    * Extract twitpic images
    *
    * @param string
    * @return string
    **/
    private function _extracttwitpic($link){
        $linkparts = explode('/',$link);
        return 'http://twitpic.com/show/large/'.$linkparts[3];
    }

    /**
    * Extract flickr images
    *
    * @param string
    * @return string
    **/
    private function _extractflickr($link){
        $string = $this->fetch_url($link);
        if(isset($string)){
            preg_match_all('! property="og:image" content="(.*?)" !', $string, $matches);
            if(isset($matches[1][0])){
                return $matches[1][0];
            }
        }
    }

    /**
    * Extract instagram images
    *
    * @param string
    * @return string
    **/
    private function _extractinstagram($link){
        $string = $this->fetch_url($link);
        if(isset($string)){
            preg_match_all('! property="og:image" content="(.*?)" !', $string, $matches);
            if(isset($matches[1][0]) && !empty($matches[1][0])){
                return $matches[1][0];
            }
        }
    }

    /**
    * Get auth tokens for the Twitter API
    *
    * @param string
    * @param string
    * @return string
    **/
    private function _twitter_token($key, $secret)
    {
        $app_key = urlencode($key);
        $app_secret = urlencode($secret);

        $new_key = base64_encode($app_key.':'.$app_secret);

        $apptoken = curl_init();
        curl_setopt_array($apptoken, array(
                CURLOPT_HTTPHEADER      => array('Authorization: Basic '.$new_key),
                CURLOPT_RETURNTRANSFER  => 1,
                CURLOPT_URL             => 'https://api.twitter.com/oauth2/token',
                CURLOPT_USERAGENT       => 'EP Hashimage Wordpress Plugin',
                CURLOPT_POST            => 1,
                CURLOPT_POSTFIELDS      => array('grant_type' => 'client_credentials')
            )
        );
        $token = json_decode(curl_exec($apptoken));
        curl_close($apptoken);

        return $token->access_token;

    }

}