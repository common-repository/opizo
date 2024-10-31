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


<div class="wrap">
    <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
    <div class="logo-container">
        <img src="<?php echo plugin_dir_url(__FILE__) . '../images/logo.png' ?>">
    </div>

    <form action="" method="post" id="opizo-shrink-form">
        <input type="hidden" name="action" value="opizo_shrink">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label for="links-to-urls"><?php echo __("Shrink",'opizo'); ?></label>
                </th>
                <td>
                    <textarea style="width: 100%" class="opizo-urls" name="links-to-shrink" id="links-to-shrink"></textarea>
                </td>
            </tr>

            </tbody>
        </table>
        <div class="text-center">
            <button type="submit" class="shrink-button"><?php echo __('Shrink','opizo') ?></button>
        </div>
    </form>
    <div id="shrink-result" style="display: none;">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label for="links-to-urls"><?php echo __("Shrinked Links:",'opizo'); ?></label>
                </th>
                <td>
                    <textarea style="width: 100%" class="opizo-urls"></textarea>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="text-center">
            <button id="new-opizo-shrink-button" class="shrink-button"> <?php echo __('Shrink new links','opizo') ?></button>
            <span class="dashicons dashicons-yes" style="font-size: 50px; color: #688c3a;;"></span>
        </div>
    </div>
</div>
