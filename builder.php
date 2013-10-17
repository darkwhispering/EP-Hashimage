<?php


Class Hashimage_Builder extends Hashimage_Core {

    function __construct()
    {
        parent::__construct();

        $this->settings = $this->get_settings($args);

    }

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

    private function lightbox_widget($feed = array())
    {

        $html = '';

        foreach ($feed as $image) {

            $html = '<a href="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->settings['img_sizes']['lightbox_w'].'&amp;h='.$this->settings['img_sizes']['lightbox_h'].'&amp;zc=2" rel="lightbox-'.$this->settings['hashtag'].'">';
            $html .= '<img src="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->settings['img_sizes']['widget_thumb_w'].'&amp;h='.$this->settings['img_sizes']['widget_thumb_h'].'" alt="Image loaded with Hashimage" />';
            $html .= '</a>'."\n";

        }

        return $html;

    }

    private function lightbox_html($feed = array())
    {

        $html = '';

        foreach ($feed as $image) {

            $html .= '<a href="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->settings['img_sizes']['lightbox_w'].'&amp;h='.$this->settings['img_sizes']['lightbox_h'].'&amp;zc=2" rel="lightbox-'.$this->settings['hashtag'].'">';
            $html .= '<img src="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->settings['img_sizes']['thumb_w'].'&amp;h='.$this->settings['img_sizes']['thumb_h'].'" alt="Image loaded with Hashimage" />';
            $html .= '</a>'."\n";

        }

        return $html;

    }

    private function source_widget($feed = array())
    {

        $html = '';

        foreach ($feed as $image) {

            $html .= '<a href="'.$image['source'].'" target="_blank">';
            $html .= '<img src="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->settings['img_sizes']['widget_thumb_w'].'&amp;h='.$this->settings['img_sizes']['widget_thumb_h'].'" alt="Image loaded with Hashimage" />';
            $html .= '</a>'."\n";

        }

        return $html;

    }

    private function source_html($feed = array())
    {

        $html = '';

        foreach ($feed as $image) {

            $html .= '<a href="'.$image['source'].'" target="_blank">';
            $html .= '<img src="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->settings['img_sizes']['thumb_w'].'&amp;h='.$this->settings['img_sizes']['thumb_h'].'" alt="Image loaded with Hashimage" />';
            $html .= '</a>'."\n";

        }

        return $html;

    }
    
}