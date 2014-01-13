<?php

class Hashimage_Settings extends Hashimage_Core {

    function panel_content()
    {
        if (!current_user_can('manage_options'))  {
            wp_die( __('You do not have sufficient permissions to access this page.') );
        }

        $settings = $this->get_settings();
        ?>


        <div class="wrap ep-hashimage">
            <div class="icon32" id="icon-options-general"><br></div>
            <h2>EP Hashimage settings</h2>

            <?php
                /*if ($_POST['clear_cache']) {
                    global $wpdb;
                    if($wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name LIKE %s", '%hashimage_cache%'))) {
                        echo '<div class="updated"><p><strong>Cache cleared</strong></p></div>';
                    }
                }*/
            ?>
                       
            <form method="post" action="options.php">

                <?php wp_nonce_field('update-options'); ?>

                <table class="form-table">

                    <tbody>

                        <tr valign="top">
                            <th scope="row">
                                Dimension for thumbnails
                            </th>
                            <td>
                                <input type="number" name="ep_hashimage_img_sizes[thumb_w]" class="small-text" value="<?php echo $settings['img_sizes']['thumb_w']; ?>" /> x <input type="number" name="ep_hashimage_img_sizes[thumb_h]" class="small-text" value="<?php echo $settings['img_sizes']['thumb_h']; ?>" />px 
                                <p class="description">(W x H - Default values is 200x200)</p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                Dimension for widget thumbnails
                            </th>
                            <td>
                                 <input type="number" name="ep_hashimage_img_sizes[widget_thumb_w]" class="small-text" value="<?php echo $settings['img_sizes']['widget_thumb_w']; ?>" /> x <input type="number" name="ep_hashimage_img_sizes[widget_thumb_h]" class="small-text" value="<?php echo $settings['img_sizes']['widget_thumb_h']; ?>" />px
                                 <p class="description">(W x H - Default values is 80x80)</p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                Dimension for lightbox
                            </th>
                            <td>
                                <input type="number" name="ep_hashimage_img_sizes[lightbox_w]" class="small-text" value="<?php echo $settings['img_sizes']['lightbox_w']; ?>" /> x <input type="number" name="ep_hashimage_img_sizes[lightbox_h]" class="small-text" value="<?php echo $settings['img_sizes']['lightbox_h']; ?>" />px
                                <p class="description">(W x H - Default values is 600x400)</p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                Cache Time
                            </th>
                            <td>
                                <input type="text" name="ep_hashimage_cache_time" value="<?php echo $settings['cache_time']; ?>" class="regular-text">
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                How to open images on click
                            </th>
                            <td>
                                <input type="radio" name="ep_hashimage_img_display" value="lightbox" <?php if($settings['img_display'] === 'lightbox') echo 'checked="checked"'; ?> /> Lightbox <span class="description">(Opens image in an lightbox overlay)</span>
                                <br/>
                                <input type="radio" name="ep_hashimage_img_display" value="source" <?php if($settings['img_display'] === 'source') echo 'checked="checked"'; ?> /> Original source <span class="description">(Opens the source page in a new tab)</span>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                Choose network
                            </th>
                            <td>
                                <?php
                                    if ($settings['network']['twitter']) {
                                        $check_twitter = 'checked="checked"';
                                    }
                                    if ($settings['network']['instagram']) {
                                        $check_instagram = 'checked="checked"';
                                    }
                                ?>
                                <input type="checkbox" name="ep_hashimage_network[twitter]" value="1" <?php echo $check_twitter; ?>>
                                Twitter
                                <br/>
                                <input type="checkbox" name="ep_hashimage_network[instagram]" value="1" <?php echo $check_instagram; ?>>
                                Instagram
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                Choose networks
                            </th>
                            <td>
                                <input type="checkbox" name="ep_hashimage_networks[instagram]" value="instagram.com" <?php echo !empty($settings['networks']['instagram']) ? 'checked="checked"' : ''; ?>> Instagram
                                <br/>
                                <input type="checkbox" name="ep_hashimage_networks[twitpic]" value="twitpic.com" <?php echo !empty($settings['networks']['twitpic']) ? 'checked="checked"' : ''; ?>> Twitpic
                                <br/>
                                <input type="checkbox" name="ep_hashimage_networks[yfrog]" value="yfrog.com" <?php echo !empty($settings['networks']['yfrog']) ? 'checked="checked"' : ''; ?>> yFrog
                                <br/>
                                <p class="description">(Used only when searching on twitter)</p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                Twitter Consumer Key
                            </th>
                            <td>
                                <input type="text" name="ep_hashimage_twitter_key" value="<?php echo $settings['twitter_key']; ?>" class="regular-text">
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                Twitter Consumer Secret
                            </th>
                            <td>
                                <input type="text" name="ep_hashimage_twitter_secret" value="<?php echo $settings['twitter_secret']; ?>" class="regular-text">
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row">
                                Instagram Client Id
                            </th>
                            <td>
                                <input type="text" name="ep_hashimage_client_id" value="<?php echo $settings['client_id']; ?>" class="regular-text">
                            </td>
                        </tr>

                        <tr valign="top">
                            <td colspan="2">
                                <input type="hidden" name="action" value="update" />
                                <input type="hidden" name="page_options" value="ep_hashimage_img_display,ep_hashimage_img_sizes,ep_hashimage_cache_time,ep_hashimage_twitter_key,ep_hashimage_twitter_secret,ep_hashimage_client_id,ep_hashimage_network,ep_hashimage_networks" />

                                <input type="submit" name="submit" value="Save" class="button button-primary" />
                            </td>
                        </tr>
                    </tbody>
                </table>
                
            </form>

            <?php /*
            <br/><h2 class="title">Tools</h2><br/>

            <form method="post">
                <input type="submit" class="button-secondary" value="Clear cache" name="clear_cache">
            </form>
            <p class="description">Only use this if you don't have any other caching pugin installed. If you have a caching plugin, you need to clear the cache in the that plugins settings</p>
                
            <!-- <p>For help, documentations and examples on how to use EP Hashimage. <a href="">Visit the documentations</a></p> -->

            <br/><h2 class="title">How to use</h2>
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
        */ ?>





        <?php /*
        <div class="wrap ep-hashimage">
            <div class="icon32" id="icon-options-general"><br></div>
            <h2>EP Hashimage settings</h2>

            <strong>The settings panel is not available in the Alpha version</strong>

            <p>To change any settings, edit the core.php file in the plugins folder. This is also the file you need to add your twitter app key and hash and also instagram client id.</p>

            <p>More help here on how to create an <a href="" target="_bkank">Twitter app</a> or <a href="" target="_blank">Instagram app</a></p>

        </div>
        */ ?>


        <?php
    }

}

function hashimage_settings_panel() {
    $settings_panel = new Hashimage_Settings;
    return $settings_panel->panel_content();
}

function hashimage_settings_menu_option() {
    add_submenu_page('options-general.php', 'EP Hashimage Settings', 'EP Hashimage', 'manage_options', 'ep-hashimage', 'hashimage_settings_panel');
}
add_action('admin_menu','hashimage_settings_menu_option');