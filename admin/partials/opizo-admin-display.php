<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://opizo.com
 * @since      1.0.0
 *
 * @package    Opizo
 * @subpackage Opizo/admin/partials
 */
?>

<?php if( isset($_GET['settings-updated']) ) { ?>
    <div id="message" class="updated">
        <p><strong><?php _e('Settings saved.') ?></strong></p>
    </div>
<?php } ?>

<div class="wrap">
    <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
    <div class="logo-container">
        <img src="<?php echo plugin_dir_url(__FILE__) . '../images/logo.png' ?>">
    </div>


    <div class="opizo-setting-admin-message">
        * <?php echo __("Shrinking urls not change post content, only replace urls via shrinked link on post view", 'opizo') ?>
        <br />
        * <?php echo __("By deleting link from wordpress, shrinked URL still exist in your panel at Opizo.com", 'opizo') ?>
        <br />
        * <?php echo __("For shrinking URLs in old posts, go to Post lists, select desired posts then select \"Shrink links by Opizo\" from Bulk Actions, then go to \"Shrink Old Posts\" page and press \"Start Shrinking\" button.", 'opizo') ?>
        <button id="toggle_tutorial" class="button"><?php echo __("Image Guide","opizo"); ?></button>
        <div id="image_tutorial" style="display: none"><img src="<?php
            if(strtolower(get_locale()) == 'fa_ir')
                echo plugin_dir_url(__FILE__) . '../images/old_post_help_fa.png';
            else
                echo plugin_dir_url(__FILE__) . '../images/old_post_help.png';
            ?>"></div>
    </div>

    <form method="post" action="options.php">
        <?php
        $options = get_option($this->plugin_name,$this->default_options);
        $post_types = get_post_types(array("public" => true));
        $saved_post_types = $options['post-types'];
        $active = $options['active'];

        settings_fields($this->plugin_name);
        do_settings_sections($this->plugin_name);
        ?>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label for="<?php echo $this->plugin_name; ?>-active"><?php echo __("Active Opizo Shortener", 'opizo'); ?></label>
                </th>
                <td>
                    <input name="<?php echo $this->plugin_name; ?>[active]" type="checkbox"
                           id="<?php echo $this->plugin_name; ?>-active"
                           value="1" <?php echo checked($active) ?>>
                    <div>
                        <?php
                        echo sprintf(wp_kses(__("You can simply active or deactive plugin.<br />This option is not affect on <a href='%s'>Shrink Link</a> page.", 'opizo'), array('a' => array('href' => array(),'target' => array()), 'br' => array())), get_admin_url(null, "admin.php?page=opizo_shrink"));
                        ?>
                    </div>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="<?php echo $this->plugin_name; ?>-api-key"><?php echo __("API Key", 'opizo'); ?></label>
                </th>
                <td>
                    <input name="<?php echo $this->plugin_name; ?>[api-key]" type="text"
                           id="<?php echo $this->plugin_name; ?>-api-key"
                           value="<?php echo $this->get_option($options, 'api-key', '') ?>" class="regular-text">
                    <div>
                        <?php
                        echo sprintf(wp_kses(__("This code used to connect your wordpress site to <a href='%s' target='_blank'>Opizo link shortener</a> service.<br />For generate and get your own api code, login to your <a target='_blank' href='%s'>Opizo dashboard</a> and from <a target='_blank' href='%s'>API</a> section, make new one.", 'opizo'), array('a' => array('href' => array(),
                            'target' => array()), 'br' => array())), esc_url("http://opizo.com/"), esc_url("http://opizo.com/usercp/"), esc_url("http://opizo.com/usercp/api/create"));
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row"><label
                            for="<?php echo $this->plugin_name; ?>-domains"><?php echo __("Domains", 'opizo'); ?></label>
                </th>
                <td>
                    <input name="<?php echo $this->plugin_name; ?>[domains]" type="text"
                           id="<?php echo $this->plugin_name; ?>-domains"
                           value="<?php echo $this->get_option($options, 'domains', '') ?>" class="regular-text">
                    <div>
                        <div>
                            <?php
                            echo wp_kses(__("If you want shrink links from only certain domains, enter domains in this filed(comma separated)<br/>(for example: uploadboy.com,cdn.server.net)", 'opizo'), array('br' => array()));
                            ?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row"><label
                            for="<?php echo $this->plugin_name; ?>-post-types"><?php echo __("Post types", 'opizo'); ?></label>
                </th>
                <td>
                    <ul>
                        <?php
                        foreach ($post_types as $post_type)
                        {
                            if ($post_type == 'attachment')
                                continue;
                            $checked = !is_null($saved_post_types) && in_array($post_type, $saved_post_types);
                            ?>
                            <li><label><input name="<?php echo $this->plugin_name; ?>[post-types][]" type="checkbox"
                                              id="<?php echo $this->plugin_name; ?>-post-types"
                                              value="<?php echo $post_type ?>"
                                              <?php echo checked($checked) ?>><?php echo $post_type ?>
                                </label></li>
                            <?php
                        }
                        ?>
                    </ul>
                    <?php echo __("You can use link shortener service in post types that you want.",'opizo');?>
                </td>
            </tr>
            </tbody>
        </table>
        <?php submit_button(__('Save all changes'), 'primary', 'submit', true); ?>

    </form>

</div>
