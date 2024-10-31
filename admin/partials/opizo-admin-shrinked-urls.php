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
global $wpdb;
$total = $wpdb->get_var("SELECT COUNT(*) FROM ".$this->shrinked_urls_table_name);
$item_per_page = 50;
$paged = isset($_GET['paged']) ? $_GET['paged'] : 1;
$paged = $paged < 1 ? 1 : $paged;
$total_page = ceil($total / $item_per_page);
$offset = $item_per_page * ($paged - 1);

$shrinked_urls = $wpdb->get_results("SELECT * FROM ".$this->shrinked_urls_table_name." LIMIT $offset,$item_per_page;");

$pagination = array(
    'format'             => '?paged=%#%',
    'total'              => $total_page,
    'current'            => $paged,
    'show_all'           => false,
    'end_size'           => 2,
    'mid_size'           => 2,
    'prev_next'          => true,
    'prev_text'          => __('Previous'),
    'next_text'          => __('Next'),
    'type'               => 'plain',
    'add_args'           => false,
    'add_fragment'       => '',
    'before_page_number' => '',
    'after_page_number'  => ''
);
?>


<div class="wrap">
    <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
    <div class="logo-container">
        <img src="<?php echo plugin_dir_url(__FILE__) . '../images/logo.png' ?>">
    </div>
    <div class="opizo-shrinked-urls container">
        <div class="opizo-pagination-container">
            <?php echo paginate_links( $pagination );?>
        </div>
        <table class="opizo-shrinked-urls table" cellpadding="0" cellspacing="0">
            <tr>
                <th style="min-width: 1%; white-space: nowrap; padding: 0 15px;"></th>
                <th><?php echo __('Post Title','opizo')?></th>
                <th><?php echo __('Shrinked url','opizo')?></th>
                <th style="width: 100%;"><?php echo __('Original url','opizo')?></th>
            </tr>
            <?php
            foreach($shrinked_urls as $shrinked_url)
            {
                ?>
                <tr>
                    <td><a class="opizo-delete-link" data-opizo-link-id="<?php echo $shrinked_url->id ?>" href="javascript:return;"><img src="<?php echo plugin_dir_url(__FILE__) . '../images/delete.png';?>" /></a></td>
                    <td><p class="opizo-shrinked-urls post_title"><a href="<?php echo $shrinked_url->post_id > 0 ? get_permalink($shrinked_url->post_id) : '#'?>"><?php echo $shrinked_url->post_id > 0 ? wp_trim_words(get_the_title($shrinked_url->post_id),35) : ''?></a></p></td>
                    <td><pre class="opizo-shrinked-urls shrinked"><a target="_blank" href="<?php echo $this->getActiveDomain() . $shrinked_url->shrinked ?>"><?php echo $this->getActiveDomain() . $shrinked_url->shrinked ?></a></pre></td>
                    <td><pre class="opizo-shrinked-urls url"><a target="_blank" href="<?php echo $shrinked_url->url ?>"><?php echo $shrinked_url->url ?></a></pre></td>
                </tr>
                <?php
            }
            ?>
        </table>
    </div>

</div>
