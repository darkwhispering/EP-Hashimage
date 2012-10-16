<?php
/*
Plugin Name: EP Hashimage
Plugin URI: http://hashimage.com/
Description: Display image from a hashtag in your template, post/page or widget area using template tag, shortcode or the widget.
Author: Peder Fjällström & Mattias Hedman, Earth People AB
Version: 3.1.1
Author URI: http://earthpeople.se
*/


class Hashimage{
	function __construct($args){

		// Set values from the args sent
		$this->hashtag = $args['hashtag'];
		$this->limit 	= $args['limit'];
		$this->type 	= $args['type'];

		// Set som default values
		add_option('ep_hashimage_async','true');
		add_option('ep_hashimage_img_display','lightbox');
		add_option('ep_hashimage_refresh','false');
		add_option('ep_hashimage_thumb_height',200);
		add_option('ep_hashimage_thumb_width',200);
		add_option('ep_hashimage_w_thumb_height',80);
		add_option('ep_hashimage_w_thumb_width',80);
		add_option('ep_hashimage_lightbox_height',400);
		add_option('ep_hashimage_lightbox_width',600);

		$default_networks = array(
			'instagram' 	=> 'instagr.am',
			'twitpic' 	=> 'twitpic',
			'twitter' 	=> 'pic.twitter.com',
			'yfrog' 		=> 'yfrog',
			'flickr' 		=> 'flic.kr',
			'plixi' 		=> 'plixi'
		);

		add_option('ep_hashimage_networks',$default_networks);
		
		$this->links 	= array();
		$this->images 	= array();

		// Thumbnails and lightbox images settings
		$this->thumb_height 	= get_option('ep_hashimage_thumb_height');
		$this->thumb_width 		= get_option('ep_hashimage_thumb_width');
		$this->w_thumb_height 	= get_option('ep_hashimage_w_thumb_height');
		$this->w_thumb_width 	= get_option('ep_hashimage_w_thumb_width');
		$this->lightbox_height 	= get_option('ep_hashimage_lightbox_height');
		$this->lightbox_width 	= get_option('ep_hashimage_lightbox_width');
		$this->async 			= get_option('ep_hashimage_async');
		$this->img_display		= get_option('ep_hashimage_img_display');
		$this->refresh			= get_option('ep_hashimage_refresh');
		$this->networks		= get_option('ep_hashimage_networks');

		// API url
		$this->apiurl 	= 'http://search.twitter.com/search.json?q=&phrase=&ors=';
		foreach($this->networks as $network) {
			$this->apiurl .= $network.'+';
		}
		$this->apiurl .= 'lang=all&include_entities=true&rpp=500&tag=';
		
		// Do the magic
		$this->_init();
	}

	/**
	* The heart of the plugin, here we do the heavy loading
	**/
	private function _init(){
		// Check if we should load this asynct or not
		if($this->async === 'true' && $_GET['asyncload'] === 'true' || $this->async === 'false') {
			$resultsjson = json_decode($this->_fetchurl($this->apiurl.$this->hashtag, 600+rand(1,120)));
		} else {
			$resultsjson = '';
		}

		// Process the result from the call
		if(isset($resultsjson) && isset($resultsjson->results)){
			if($resultsjson->results){
				foreach($resultsjson->results as $results){
					if(isset($results->entities) && isset($results->entities->urls)){
						foreach($results->entities->urls as $url){
							$this->links[md5($url->expanded_url)]['img'] = $url->expanded_url;
							$this->links[md5($url->expanded_url)]['source'] = $url->url;
						}
					}
					if(isset($results->entities) && isset($results->entities->media)){
						foreach($results->entities->media as $image){
							$this->images[md5($image->media_url)]['img'] = $image->media_url;
							$this->images[md5($image->media_url)]['source'] = $image->url;
						}
					}
				}
			}
		}

		// Get the returned images
		$this->_extractimages();

		$this->images = array_map('unserialize', array_unique(array_map('serialize', $this->images)));
		$this->images = array_slice($this->images, 0, $this->limit);

		// Build the HTML
		$this->html = $this->_formathtml($this->images);
		
	}

	/**
	* Extract the images from the data returned
	**/
	private function _extractimages(){
		if($this->links){
			foreach($this->links as $link){
				if(stristr($link['img'],'yfrog.com')){
					$this->images[md5($link['img'])]['img'] = $this->_extractyfrog($link['img']);
					$this->images[md5($link['img'])]['source'] = $link['source'];
				}else if(stristr($link['img'],'plixi.com')){
					$this->images[md5($link['img'])]['img'] = $this->_extractplixi($link['img']);
					$this->images[md5($link['img'])]['source'] = $link['source'];
				}else if(stristr($link['img'],'instagr.am')){
					$this->images[md5($link['img'])]['img'] = $this->_extractinstagram($link['img']);
					$this->images[md5($link['img'])]['source'] = $link['source'];
				}else if(stristr($link['img'],'twitpic.com')){
					$this->images[md5($link['img'])]['img'] = $this->_extracttwitpic($link['img']);
					$this->images[md5($link['img'])]['source'] = $link['source'];
				}else if(stristr($link['img'],'flic.kr')){
					$this->images[md5($link['img'])]['img'] = $this->_extractflickr($link['img']);
					$this->images[md5($link['img'])]['source'] = $link['source'];
				}
			}
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
			preg_match_all('!<img src="(.*)" alt="photo" !', $string, $matches);
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
			preg_match_all('!<img class="photo" src="(.*)" />!', $string, $matches);
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
	private function _formathtml($images = array()){
		$html = '';

		$jsargs = array(
			'hashtag' 	=> $this->hashtag,
			'limit' 		=> $this->limit,
			'async' 		=> $this->async,
			'pluginpath' 	=> plugins_url('hashimage.php',__FILE__),
			'type' 		=> $this->type,
			'img_display'	=> $this->img_display,
			'refresh'		=> $this->refresh
		);

		if($_GET['asyncload'] != 'true') {
			$html .= "<ul class='hashimage-container' data-options='".json_encode($jsargs)."'>";
		}
		
		if($this->async === 'true' && $_GET['asyncload'] != 'true') {
			$html .= '<p><img src="'.plugins_url('loading.gif',__FILE__).'" alt="Loading"> Loading hashimages...</p>';
		} else {
			foreach($images as $image){
				$html .= '<li>';
				if(!empty($image['img'])){
					if($this->img_display === 'lightbox') {
						if($this->type == 'widget' || $_GET['type'] == 'widget') {
							$html .= '<a href="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->lightbox_width.'&amp;h='.$this->lightbox_height.'&amp;zc=2" rel="lightbox"><img src="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->w_thumb_width.'&amp;h='.$this->w_thumb_height.'" alt="Image loaded with Hashimage" /></a>'."\n";
						} else {
							$html .= '<a href="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->lightbox_width.'&amp;h='.$this->lightbox_height.'&amp;zc=2" rel="lightbox"><img src="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->thumb_width.'&amp;h='.$this->thumb_height.'" alt="Image loaded with Hashimage" /></a>'."\n";
						}
					} elseif($this->img_display === 'source') {
						if($this->type == 'widget' || $_GET['type'] == 'widget') {
							$html .= '<a href="'.$image['source'].'" target="_blank"><img src="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->w_thumb_width.'&amp;h='.$this->w_thumb_height.'" alt="Image loaded with Hashimage" /></a>'."\n";
						} else {
							$html .= '<a href="'.$image['source'].'" target="_blank"><img src="'.plugins_url('timthumb.php',__FILE__).'?src='.$image['img'].'&amp;w='.$this->thumb_width.'&amp;h='.$this->thumb_height.'" alt="Image loaded with Hashimage" /></a>'."\n";
						}
					}
				}
				$html .= '</li>';
			}
		}
		$html .= '</ul>';

		return $html;
	}
	
	/**
	* Fetch the url
	**/
	private function _fetchurl($url = null, $ttl = 86400){
		if($url){
			$option_name = 'hashimage_cache_'.md5($url);

			if (false === ($data = get_transient($option_name))) {
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
					set_transient($option_name, $data, $ttl);
				}
			}

			return $data['chunk'];
		}
	}
}

/**
* IF THIS IS AN ASYNC REQUEST
**/
if(!defined('ABSPATH')){
	require("../../../wp-load.php");
	$args = array (
 		'hashtag' 	=> strip_tags($_GET['hashtag']),
 		'limit' 		=> (int)$_GET['limit'],
 		'async' 		=> 'true',
		'type' 		=> 'plugin',
		'img_display' 	=> 'lightbox',
		'refresh'		=> 'false'
	);	
	$hashimage = new Hashimage($args);
	echo $hashimage->html; 
	exit(0);
}

function hashimage($args = ''){
	$defaults = array (
 		'hashtag' 	=> 'unicorn',
 		'limit' 		=> '5',
 		'async' 		=> 'true',
		'type' 		=> 'plugin',
		'img_display'	=> 'lightbox',
		'refresh'		=> 'false'
	);	
	$args = wp_parse_args($args, $defaults);
	$hashimage = new Hashimage($args);
	return $hashimage->html;
}

function hashimage_array($args = ''){
	$defaults = array (
 		'hashtag' 	=> 'unicorn',
 		'async' 		=> 'true',
 		'limit' 		=> '5',
 		'img_display'	=> 'lightbox',
 		'refresh'		=> 'false'
	);	
	$args = wp_parse_args($args, $defaults);
	$hashimage = new Hashimage($args);
	return $hashimage->images;
}

function hashimage_shortcode($args){
	$hashimage = new Hashimage($args);
	return $hashimage->html;
}
add_shortcode('hashimage', 'hashimage_shortcode');

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


// =================
// = Plugin Widget =
// =================
// Load the widget it self
add_action('widgets_init','load_ep_hashimage_widget');

// Get widget class
function load_ep_hashimage_widget() {
	register_widget('ep_hashimage_widget');
}

// Widget class
class ep_hashimage_widget extends WP_Widget{

	function ep_hashimage_widget() {
		//Settings
		$widget_ops = array('classname'=>'ephashimagewidget','description'=>__('Add hashimages to your widget area.','ephashimagewidget'));
		
		//Controll settings
		$control_ops = array('id_base' => 'ephashimagewidget');
		
		//Create widget
		$this->WP_Widget('ephashimagewidget',__('EP Hashimage'),$widget_ops,$control_ops);
		
	}
	
	// Widget frontend code
	function widget($args,$instance) {
		extract($args);
		
		//User selected settings
		$title 		= $instance['title'];
		$hashtag 		= $instance['hashtag'];
		$limit 		= ($instance['limit']) ? $instance['limit'] : 5;
		$jsargs 		= array(
						'hashtag'	=> $hashtag,
						'limit' 	=> $limit
					);

		echo $before_widget;
		?>
			
			<?php echo $before_title . $title . $after_title; ?>
			
			<div class="images">
				<?php
					if(!empty($hashtag)) {
						if(function_exists('hashimage')){
							echo hashimage('hashtag='.$hashtag.'&limit='.$limit.'&type=widget');
						}
					}
				?>
			</div>
		
		<?php
		echo $after_widget;
	}
	
	// Widget update. It's here the magic is happening when saving
	function update($new_instance,$instance) {
		$instance['title'] 		= strip_tags($new_instance['title']);
		$instance['hashtag'] 	= strip_tags($new_instance['hashtag']);
		$instance['limit'] 		= strip_tags($new_instance['limit']);
		return $instance;
	}

	// Widget backend, the options for the widget in WP admin
	function form($instance) {
		// This is where you set the default values, if you want any.
		$default = array(
			'title' 	=> 'Hashimages',
			'hashtag' => 'unicorn',
			'limit' 	=> 5
		);
		$instance = wp_parse_args((array)$instance,$default);
		
		// Your settings form. No start, end or submit tags is needed here, wordpress ad this itself later in admin
	?>
		<!-- TITLE -->
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title:'); ?></label>
			<br />
			<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>
		
		<!-- HASHTAG -->
		<p>
			<label for="<?php echo $this->get_field_id('hashtag'); ?>"><?php echo __('Hashtag:'); ?></label>
			<br />
			<input type="text" id="<?php echo $this->get_field_id('hashtag'); ?>" name="<?php echo $this->get_field_name('hashtag'); ?>" value="<?php echo $instance['hashtag']; ?>" class="widefat" />
		</p>
		
		<!-- LIMIT -->
		<p>
			<label for="<?php echo $this->get_field_id('limit'); ?>"><?php echo __('Limit:'); ?></label>
			<br />
			<input type="text" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" value="<?php echo $instance['limit']; ?>" class="widefat" />
		</p>
	
	<?php
	}
}


// ========================
// = Plugin settings page =
// ========================

class epHashimageSettings {
	function hashimage_panel() {
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}

		add_option('ep_hashimage_async','true');
		add_option('ep_hashimage_img_display','lightbox');
		add_option('ep_hashimage_refresh','false');
		add_option('ep_hashimage_thumb_height',200);
		add_option('ep_hashimage_thumb_width',200);
		add_option('ep_hashimage_w_thumb_height',80);
		add_option('ep_hashimage_w_thumb_width',80);
		add_option('ep_hashimage_lightbox_height',400);
		add_option('ep_hashimage_lightbox_width',600);

		$default_networks = array(
			'instagram' => 'instagr.am',
			'twitpic' => 'twitpic',
			'twitter' => 'pic.twitter.com',
			'yfrog' => 'yfrog',
			'flickr' => 'flickr',
			'plixi' => 'plixi'
		);

		add_option('ep_hashimage_networks',$default_networks);
	?>
		<div class="wrap ep-hashimage">
			<div class="icon32" id="icon-options-general"><br></div>
			<h2>EP Hashimage settings</h2>
			
			<?php if(!empty($_POST['submit'])) : ?>
				<?php $new_values = $this->hashimage_save_settings($_POST); ?>
				<div class="updated">
					Your settings has been saved!
				</div>
			<?php endif; ?>
			
			<?php
			if(empty($new_values)) {
				$this->thumb_height 	= get_option('ep_hashimage_thumb_height');
				$this->thumb_width 		= get_option('ep_hashimage_thumb_width');
				$this->w_thumb_height 	= get_option('ep_hashimage_w_thumb_height');
				$this->w_thumb_width 	= get_option('ep_hashimage_w_thumb_width');
				$this->lightbox_height 	= get_option('ep_hashimage_lightbox_height');
				$this->lightbox_width 	= get_option('ep_hashimage_lightbox_width');
				$this->async 			= get_option('ep_hashimage_async');
				$this->img_display		= get_option('ep_hashimage_img_display');
				$this->refresh			= get_option('ep_hashimage_refresh');
				$this->networks		= get_option('ep_hashimage_networks');
			} else {
				$this->thumb_height 	= $new_values['thumb_height'];
				$this->thumb_width 		= $new_values['thumb_width'];
				$this->w_thumb_height 	= $new_values['w_thumb_height'];
				$this->w_thumb_width 	= $new_values['w_thumb_width'];
				$this->lightbox_height 	= $new_values['lightbox_height'];
				$this->lightbox_width 	= $new_values['lightbox_width'];
				$this->async 			= $new_values['async'];
				$this->img_display		= $new_values['img_display'];
				$this->refresh			= $new_values['refresh'];
				$this->networks		= $new_values['networks'];
			}
			?>
			
			<form method="post">
				<div class="form-row">
					Dimension for thumbnails (w,h): <input type="number" name="thumb_width" size="3" value="<?php echo $this->thumb_width; ?>" /> x <input type="number" name="thumb_height" size="3" value="<?php echo $this->thumb_height; ?>" />px <em>(Default values is 200x200)</em>
				</div>
				<div class="form-row">
					Dimension for widget thumbnails (w,h): <input type="number" name="w_thumb_width" size="3" value="<?php echo $this->w_thumb_width; ?>" /> x <input type="number" name="w_thumb_height" size="3" value="<?php echo $this->w_thumb_height; ?>" />px <em>(Default values is 80x80)</em>
				</div>
				<div class="form-row">
					Dimension for lightbox (w,h): <input type="number" name="lightbox_width" size="3" value="<?php echo $this->lightbox_width; ?>" /> x <input type="number" name="lightbox_height" size="3" value="<?php echo $this->lightbox_height; ?>" />px <em>(Default values is 600x400)</em>
				</div>
				<div class="form-row">
					Async (quicker page loads):
					<input type="radio" name="async" value="true" <?php if($this->async === 'true') echo 'checked="checked"'; ?> /> true
					&nbsp;&nbsp;&nbsp;
					<input type="radio" name="async" value="false" <?php if($this->async === 'false') echo 'checked="checked"'; ?> /> false
				</div>
				<div class="form-row">
					Auto refresh (every 15 min):
					<input type="radio" name="refresh" value="true" <?php if($this->refresh === 'true') echo 'checked="checked"'; ?> /> true
					&nbsp;&nbsp;&nbsp;
					<input type="radio" name="refresh" value="false" <?php if($this->refresh === 'false') echo 'checked="checked"'; ?> /> false
				</div>
				<div class="form-row">
					How to open images on click:
					<input type="radio" name="img_display" value="lightbox" <?php if($this->img_display === 'lightbox') echo 'checked="checked"'; ?> /> Lightbox
					&nbsp;&nbsp;&nbsp;
					<input type="radio" name="img_display" value="source" <?php if($this->img_display === 'source') echo 'checked="checked"'; ?> /> Original source
				</div>
				<div class="form-row">
					Choose networks:<br/>
					<input type="checkbox" name="networks[instagram]" value="instagr.am" <?php echo !empty($this->networks['instagram']) ? 'checked="checked"' : ''; ?>> Instagram
					&nbsp;&nbsp;&nbsp;
					<input type="checkbox" name="networks[twitpic]" value="twitpic" <?php echo !empty($this->networks['twitpic']) ? 'checked="checked"' : ''; ?>> Twitpic
					&nbsp;&nbsp;&nbsp;
					<input type="checkbox" name="networks[twitter]" value="pic.twitter.com" <?php echo !empty($this->networks['twitter']) ? 'checked="checked"' : ''; ?>> pic.twitter.com
					&nbsp;&nbsp;&nbsp;
					<input type="checkbox" name="networks[yfrog]" value="yfrog" <?php echo !empty($this->networks['yfrog']) ? 'checked="checked"' : ''; ?>> yFrog
					&nbsp;&nbsp;&nbsp;
					<input type="checkbox" name="networks[flickr]" value="flic.kr" <?php echo !empty($this->networks['flickr']) ? 'checked="checked"' : ''; ?>> Flickr
					&nbsp;&nbsp;&nbsp;
					<input type="checkbox" name="networks[plixi]" value="plixi" <?php echo !empty($this->networks['plixi']) ? 'checked="checked"' : ''; ?>> Plixi
				</div>
				<div class="form-row">
					<input type="submit" name="submit" value="Save" />
				</div>
			</form>
			
			<h2>How to use</h2>
			<p>There is three ways to use EP Hashimage on your Wordpress website. Either you use the available template tag, the shortcode or the included widget. More information about each option below.</p>
			
			<h4>Template Tag</h4>
				Just add <pre>echo hashimage($hashtag,$limit);</pre> anywhere in your template.
				<br/>
				Code example: <pre>&lt;&quest;php echo hashimage('hashtag=unicorn&amp;limit=5'); &quest;></pre>
			
			<h4>Shortcode</h4>
				Anywhere in a post or page text, add <pre>[hashimage $hastag $limit]</pre>
				<br/>
				Code example: <pre>[hashimage hashtag="unicorn" limit="5"]</pre>

			<h4>Widget</h4>
				Go to your widget page and look for <strong>EP Hashimage</strong> and move it to your widget area. Options are title, hashtag and limit.
		</div>
	<?php
	}
	
	private function hashimage_save_settings($data) {

		
		update_option('ep_hashimage_thumb_height',($data['thumb_height']) ? $data['thumb_height'] : 200);
		update_option('ep_hashimage_thumb_width',($data['thumb_width']) ? $data['thumb_width'] : 200);
		update_option('ep_hashimage_w_thumb_height',($data['w_thumb_height']) ? $data['w_thumb_height'] : 80);
		update_option('ep_hashimage_w_thumb_width',($data['w_thumb_width']) ? $data['w_thumb_width'] : 80);
		update_option('ep_hashimage_lightbox_height',($data['lightbox_height']) ? $data['lightbox_height'] : 400);
		update_option('ep_hashimage_lightbox_width',($data['lightbox_width']) ? $data['lightbox_width'] : 600);
		update_option('ep_hashimage_async',$data['async']);
		update_option('ep_hashimage_img_display',$data['img_display']);
		update_option('ep_hashimage_refresh',$data['refresh']);
		update_option('ep_hashimage_networks',$data['networks']);
		
		return array(
			'thumb_height' 	=> ($data['thumb_height']) ? $data['thumb_height']: 200,
			'thumb_width' 		=> ($data['thumb_width']) ? $data['thumb_width'] : 200,
			'w_thumb_height' 	=> ($data['w_thumb_height']) ? $data['w_thumb_height'] : 80,
			'w_thumb_width' 	=> ($data['w_thumb_width']) ? $data['w_thumb_width'] : 80,
			'lightbox_height' 	=> ($data['lightbox_height']) ? $data['lightbox_height'] : 400,
			'lightbox_width' 	=> ($data['lightbox_width']) ? $data['lightbox_width'] : 600,
			'async' 			=> $data['async'],
			'img_display'		=> $data['img_display'],
			'refresh'			=> $data['refresh'],
			'networks'		=> $data['networks']
		);
	}
}

function ep_hashimage_settings() {
	$settings_panel = new epHashimageSettings;
	return $settings_panel->hashimage_panel();
}

function hashimage_menu() {
	add_submenu_page('options-general.php', 'EP Hashimage Settings', 'EP Hashimage', 'manage_options', 'ep-hashimage', 'ep_hashimage_settings');
}
add_action('admin_menu','hashimage_menu');

function hashimage_admin_css() {
	wp_register_style('hashimage_css', plugins_url('css/admin.css', __FILE__));
	wp_enqueue_style('hashimage_css');
}
add_action('admin_init','hashimage_admin_css');
