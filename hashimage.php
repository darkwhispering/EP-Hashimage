<?php
/*
Plugin Name: EP Hashimage
Plugin URI: http://darkwhispering.com/wordpress-plugins
Description: Display image from a hashtag from twitter or instagram in your template, post/page or widget area using template tag, shortcode or the widget.
Author: Mattias Hedman & Peder Fjällström
Version: 4.0.0
Author URI: http://darkwhispering.com http://earthpeople.se
*/

class Hashimage {

    function __construct($args = array())
    {
        // Set default values
        add_option('ep_hashimage_async','true');
        add_option('ep_hashimage_img_display','lightbox');
        add_option('ep_hashimage_refresh','false');
        add_option('ep_hashimage_thumb_height',200);
        add_option('ep_hashimage_thumb_width',200);
        add_option('ep_hashimage_w_thumb_height',80);
        add_option('ep_hashimage_w_thumb_width',80);
        add_option('ep_hashimage_lightbox_height',400);
        add_option('ep_hashimage_lightbox_width',600);
        add_option('ep_hashiamge_instagram_client_id','');

        $default_networks = array(
            'instagram' => 'instagr.am',
            'twitpic'   => 'twitpic',
            'twitter'   => 'pic.twitter.com',
            'yfrog'     => 'yfrog',
            'flickr'    => 'flic.kr',
            'plixi'     => 'plixi'
        );
        add_option('ep_hashimage_networks',$default_networks);

        // Setup settings array
        $default_settings = array(
            'hashtag'       => 'unicorn',
            'limit'         => '5',
            'network'       => '',
            'type'          => 'plugin',
            'output'        => 'html',
            'networks'      => get_option('ep_hashimage_networks'),
            'async'         => get_option('ep_hashimage_async'),
            'img_display'   => get_option('ep_hashimage_img_display'),
            'refresh'       => get_option('ep_hashimage_refresh'),
            'img_sizes'     => array(
                'thumb_h'           => get_option('ep_hashimage_thumb_height'),
                'thumb_w'           => get_option('ep_hashimage_thumb_width'),
                'widget_thumb_h'    => get_option('ep_hashimage_w_thumb_height'),
                'widget_thumb_w'    => get_option('ep_hashimage_w_thumb_width'),
                'lightbox_h'        => get_option('ep_hashimage_lightbox_height'),
                'lightbox_w'        => get_option('ep_hashimage_lightbox_width')
            ),
            'instagram_client_id'   => get_option('ep_hashimage_instagram_client_id',''),
        );

        $this->settings = wp_parse_args($args, $default_settings);

        $this->twitterUrl = '';
        $this->instagramUrl = '';
            
        // Twitter Search
        $this->twitterUrl = 'http://search.twitter.com/search.json?q=&phrase=&ors=';
        foreach($this->settings['networks'] as $network) {
            $this->twitterUrl .= $network.'+';
        }
        $this->twitterUrl .= 'lang=all&include_entities=true&rpp=500&tag='.str_replace('#','',$this->settings['hashtag']);

        // Instagram Search
        $this->instagramUrl = 'https://api.instagram.com/v1/tags/'.str_replace('#','',$this->settings['hashtag']).'/media/recent?client_id=eee5f1d268ae4465b5931ce74a2f6ae5';

        // Do the magic
        $this->_init();
    }

    /**
    * The heart of the plugin, here we do the heavy loading
    **/
    private function _init()
    {
        // Check if we should load this asynct or not
        if (!isset($_GET['asyncload']) || $this->settings['async'] === 'false') {
            if (empty($this->settings['network'])) {
                $twitterjson = json_decode($this->_fetchurl($this->twitterUrl, 600+rand(1,120)));
                $instagramjson = json_decode($this->_fetchurl($this->instagramUrl, 600+rand(1,120)));
            } else if ($this->settings['network'] === 'twitter') {
                $twitterjson = json_decode($this->_fetchurl($this->twitterUrl, 600+rand(1,120)));
            } else if ($this->settings['network'] === 'instagram') {
                $instagramjson = json_decode($this->_fetchurl($this->instagramUrl, 600+rand(1,120)));
            }
        } else if ($this->settings['async'] === 'true' && $_GET['asyncload'] === 'true') {
            $twitterjson = '';
            $instagramjson = '';
        }

        // Process the result from twitter
        if (isset($twitterjson) && $twitterjson->results) {
            foreach ($twitterjson->results as $results) {
                if (isset($results->entities) && isset($results->entities->urls)) {
                    foreach ($results->entities->urls as $url) {
                        if (!empty($url->expanded_url) && !empty($url->url)) {
                            $links[md5($url->expanded_url)]['img'] = $url->expanded_url;
                            $links[md5($url->expanded_url)]['source'] = $url->url;
                        }
                    }
                }
                if (isset($results->entities) && isset($results->entities->media)) {
                    foreach ($results->entities->media as $image) {
                        if (!empty($image->media_url) && !empty($image->url)) {
                            $images[md5($image->media_url)]['img'] = $image->media_url;
                            $images[md5($image->media_url)]['source'] = $image->url;
                        }
                    }
                }
            }

            // Get the images from the links on twitter
            $images = array_merge($this->_extractimages($links),$images);
        }

        // Process the results from instagram
        if (isset($instagramjson) && isset($instagramjson->data)) {
            foreach ($instagramjson->data as $result) {
                if (!empty($result->link) && !empty($result->images->standard_resolution->url)) {
                    $images[md5($result->id)]['img'] = $result->images->standard_resolution->url;
                    $images[md5($result->id)]['link'] = $result->link;
                }
            }
        }

        // print_r($this->links);
        
        $images = array_map('unserialize', array_unique(array_map('serialize', $images)));

        if (empty($network)) {
            shuffle($images);
        }

        $images = array_slice($images, 0, $this->settings['limit']);

        // Build the output
        if ($this->settings['output'] === 'html') {
            $this->output = $this->_formathtml($images);
        } elseif ($this->settings['output'] === 'array') {
            $this->output = $images;
        }
    }

    /**
    * Fetch the url
    **/
    private function _fetchurl($url = null, $ttl = 86400){
        if ($url)
        {
            $option_name = 'hashimage_cache_'.md5($url);

            // if (false === ($data = get_transient($option_name))) {
                $ch = curl_init();
                $options = array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT => 10
                );
                curl_setopt_array($ch, $options);
                $data['chunk'] = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if($http_code === 200){
                    // set_transient($option_name, $data, $ttl);
                }
            // }

            return $data['chunk'];
        }
    }

    /**
    * Extract the images from the data returned
    **/
    private function _extractimages($links){
        if($links){
            foreach($links as $link){
                if (stristr($link['img'],'yfrog.com')) {
                    $images[md5($link['img'])]['img'] = $this->_extractyfrog($link['img']);
                    $images[md5($link['img'])]['source'] = $link['source'];
                } else if (stristr($link['img'],'plixi.com')) {
                    $images[md5($link['img'])]['img'] = $this->_extractplixi($link['img']);
                    $images[md5($link['img'])]['source'] = $link['source'];
                } else if (stristr($link['img'],'instagr.am')) {
                    $images[md5($link['img'])]['img'] = $this->_extractinstagram($link['img']);
                    $images[md5($link['img'])]['source'] = $link['source'];
                } else if (stristr($link['img'],'twitpic.com')) {
                    $images[md5($link['img'])]['img'] = $this->_extracttwitpic($link['img']);
                    $images[md5($link['img'])]['source'] = $link['source'];
                } else if (stristr($link['img'],'flic.kr')) {
                    $images[md5($link['img'])]['img'] = $this->_extractflickr($link['img']);
                    $images[md5($link['img'])]['source'] = $link['source'];
                }
            }

            return $images;
        }
    }

    /**
    * Extract yfrog images
    **/
    private function _extractyfrog($link){
        return trim($link,'”."').':iphone';
    }

    /**
    * Extract twitpic images
    **/
    private function _extracttwitpic($link){
        $linkparts = explode('/',$link);
        return 'http://twitpic.com/show/large/'.$linkparts[3];
    }

    /**
    * Extract flickr images
    **/
    private function _extractflickr($link){
        $string = $this->_fetchurl($link);
        if(isset($string)){
            preg_match_all('!<img src="(.*?)" alt="photo" !', $string, $matches);
            if(isset($matches[1][0])){
                return $matches[1][0];
            }
        }
    }

    /**
    * Extract instagram images
    **/
    private function _extractinstagram($link){
        $link = trim($link);

        $search = 'instagr.am';
        $replace = 'instagram.com';

        $link = str_replace($search, $replace, $link);

        $string = $this->_fetchurl($link);
        if(isset($string)){
            preg_match_all('! class="photo" src="(.*?)" !', $string, $matches);
            if(isset($matches[1][0]) && !empty($matches[1][0])){
                return $matches[1][0];
            }
        }
    }

    /**
    * Extract plixi images
    **/
    private function _extractplixi($link){
        $string = $this->_fetchurl($link);
        if(isset($string)){
            preg_match_all('! src="(.*)" id="photo"!', $string, $matches);
            if($matches[1][0]){
                return $matches[1][0];
            }
        }
    }

    /**
    * Build the HTML code
    **/
    private function _formathtml($images = array())
    {
        print_r($this->settings);
        $html = '';

        $jsargs = array(
            'hashtag'       => $this->settings['hashtag'],
            'limit'         => $this->settings['limit'],
            'async'         => $this->settings['async'],
            'pluginpath'    => plugins_url('hashimage.php',__FILE__),
            'type'          => $this->settings['type'],
            'img_display'   => $this->settings['img_display'],
            'refresh'       => $this->settings['refresh']
        );

        if ($_GET['asyncload'] != 'true') {
            $html .= "<ul class='hashimage-container' data-options='".json_encode($jsargs)."'>";
        }
        
        if ($this->async === 'true' && $_GET['asyncload'] != 'true') {
            $html .= '<p><img src="'.plugins_url('loading.gif',__FILE__).'" alt="Loading"> Loading hashimages...</p>';
        } else {
            foreach ($images as $image) {
                if (!empty($image['img'])) {
                    $html .= '<li>';
                    if ($this->settings['img_display'] === 'lightbox') {
                        if ($this->settings['type'] == 'widget' || $_GET['type'] == 'widget') {
                            $html .= '<a href="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->settings['img_sizes']['lightbox_w'].'&amp;h='.$this->settings['img_sizes']['lightbox_h'].'&amp;zc=2" rel="lightbox"><img src="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->settings['img_sizes']['widget_thumb_w'].'&amp;h='.$this->settings['img_sizes']['widget_thumb_h'].'" alt="Image loaded with Hashimage" /></a>'."\n";
                        } else {
                            $html .= '<a href="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->settings['img_sizes']['lightbox_w'].'&amp;h='.$this->settings['img_sizes']['lightbox_h'].'&amp;zc=2" rel="lightbox"><img src="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->settings['img_sizes']['thumb_w'].'&amp;h='.$this->settings['img_sizes']['thumb_h'].'" alt="Image loaded with Hashimage" /></a>'."\n";
                        }
                    } elseif($this->settings['img_display'] === 'source') {
                        if ($this->settings['type'] == 'widget' || $_GET['type'] == 'widget') {
                            $html .= '<a href="'.$image['source'].'" target="_blank"><img src="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->settings['img_sizes']['widget_thumb_w'].'&amp;h='.$this->settings['img_sizes']['widget_thumb_h'].'" alt="Image loaded with Hashimage" /></a>'."\n";
                        } else {
                            $html .= '<a href="'.$image['source'].'" target="_blank"><img src="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->settings['img_sizes']['thumb_w'].'&amp;h='.$this->settings['img_sizes']['thumb_h'].'" alt="Image loaded with Hashimage" /></a>'."\n";
                        }
                    }
                    $html .= '</li>';
                }                
            }
        }
        $html .= '</ul>';

        return $html;
    }
}

function hashimage($args = array()){
    $hashimage = new Hashimage($args);
    return $hashimage->output;
}