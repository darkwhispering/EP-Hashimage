<?php

class Hashimage_Settings extends Hashimage_Core {

    function panel_content()
    {
        if (!current_user_can('manage_options'))  {
            wp_die( __('You do not have sufficient permissions to access this page.') );
        }
        ?>
        <div class="wrap ep-hashimage">
            <div class="icon32" id="icon-options-general"><br></div>
            <h2>EP Hashimage settings</h2>

            <strong>The settings panel is not available in the Alpha version</strong>

            <p>To change any settings, edit the core.php file in the plugins folder. This is also the file you need to add your twitter app key and hash and also instagram client id.</p>

            <p>More help here on how to create an <a href="" target="_bkank">Twitter app</a> or <a href="" target="_blank">Instagram app</a></p>

        </div>
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