<?php


Class Hashimage_Builder extends Hashimage_Core {

    function __construct()
    {
        parent::__construct();

        // $this->settings = $this->get_settings($args);

    }

    /**
    * Build html code for async request container
    *
    * @param array
    * @return string
    **/
    function build_async($args = array()) {

        $async_args = array(
            'hashtag'       => $args['hashtag'],
            'limit'         => $args['limit'],
            'type'          => $args['type'],
            'refresh'       => $args['cache_time'],
            'pluginpath'    => plugins_url('async.php', __FILE__),
            'async'         => true
        );

        $html = "<div class='hashimage-container' data-async='". json_encode($async_args) ."'>";
            $html .= '<div class="hashimage-loader"><img src="'. plugins_url('loading.gif', __FILE__) .'" alt="loading..."/></div>';
        $html .= "</div>";

        return $html;

    }


    /**
    * Build html for lightbox display option
    * 
    * @param array
    * @return string
    **/
    function build_lightbox($feed = array())
    {

        $html = '<ul class="hashimage-list">';

        if ($feed) {

            if ($this->settings['type'] === 'widget') {
                $html .= $this->lightbox_widget($feed);
            }

            if ($this->settings['type'] === 'plugin') {
                $html .= $this->lightbox_html($feed);
            }

        } else {

            $html .= '<li><strong>No images found. Try with another hashtag?!</strong></li>';

        }

        $html .= '</ul>';

        return $html;

    }

    /**
    * Build html for source image display option
    * 
    * @param array
    * @return string
    **/
    function build_source($feed = array())
    {

        $html = '<ul class="hashimage-list">';

        if ($feed) {

            if ($this->settings['type'] === 'widget') {
                $html .= $this->source_widget($feed);
            }

            if ($this->settings['type'] === 'plugin') {
                $html .= $this->source_html($feed);
            }

        } else {

            $html .= '<li><strong>No images found. Try with another hashtag?!</strong></li>';

        }

        $html .= '</ul>';

        return $html;

    }

    /**
    * Build html for widget with lioghtbox display option
    * 
    * @param array
    * @return string
    **/
    private function lightbox_widget($feed = array())
    {

        $i = 1;
        $html = '';

        foreach ($feed as $image) {

            $html .= '<li class="hashimage-list-item hashimage-item-'.$i.'">';
                $html .= '<a href="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->settings['img_sizes']['lightbox_w'].'&amp;h='.$this->settings['img_sizes']['lightbox_h'].'&amp;zc=2" rel="lightbox-'.$this->settings['hashtag'].'">';
                    $html .= '<img src="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->settings['img_sizes']['widget_thumb_w'].'&amp;h='.$this->settings['img_sizes']['widget_thumb_h'].'" alt="Image loaded with Hashimage" />';
                $html .= '</a>'."\n";
            $html .= '</li>';

            $i++;

        }

        return $html;

    }

    /**
    * Build html for shortcode or template tag with lightbox display option
    * 
    * @param array
    * @return string
    **/
    private function lightbox_html($feed = array())
    {

        $i = 1;
        $html = '';

        foreach ($feed as $image) {

            $html .= '<li class="hashimage-list-item hashimage-item-'.$i.'">';
                $html .= '<a href="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->settings['img_sizes']['lightbox_w'].'&amp;h='.$this->settings['img_sizes']['lightbox_h'].'&amp;zc=2" rel="lightbox-'.$this->settings['hashtag'].'">';
                    $html .= '<img src="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->settings['img_sizes']['thumb_w'].'&amp;h='.$this->settings['img_sizes']['thumb_h'].'" alt="Image loaded with Hashimage" />';
                $html .= '</a>'."\n";
            $html .= '</li>';

            $i++;
        }

        return $html;

    }

    /**
    * Build html for widget with source image display option
    * 
    * @param array
    * @return string
    **/
    private function source_widget($feed = array())
    {

        $i = 1;
        $html = '';

        foreach ($feed as $image) {

            $html .= '<li class="hashimage-list-item hashimage-item-'.$i.'">';
                $html .= '<a href="'.$image['source'].'" target="_blank">';
                    $html .= '<img src="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->settings['img_sizes']['widget_thumb_w'].'&amp;h='.$this->settings['img_sizes']['widget_thumb_h'].'" alt="Image loaded with Hashimage" />';
                $html .= '</a>'."\n";
            $html .= '</li>';

            $i++;

        }

        return $html;

    }

    /**
    * Build html for shortcode or template tag with source image display option
    * 
    * @param array
    * @return string
    **/
    private function source_html($feed = array())
    {

        $i = 1;
        $html = '';

        foreach ($feed as $image) {

            $html .= '<li class="hashimage-list-item hashimage-item-'.$i.'">';
                $html .= '<a href="'.$image['source'].'" target="_blank">';
                    $html .= '<img src="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->settings['img_sizes']['thumb_w'].'&amp;h='.$this->settings['img_sizes']['thumb_h'].'" alt="Image loaded with Hashimage" />';
                $html .= '</a>'."\n";
            $html .= '</li>';

            $i++;

        }

        return $html;

    }
    
}