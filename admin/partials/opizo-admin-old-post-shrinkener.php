<?php
$opizo_old_links = get_option("opizo_old_links",array());

if(!empty($_REQUEST["start"]) && is_array($opizo_old_links) && count($opizo_old_links))
{
    $opizo_old_links = array_values($opizo_old_links);
    $current_post_id = $opizo_old_links[0];
    $current_post_opizo_shrinked = get_post_meta($current_post_id,"_opizo_shrinked",true);

    if($current_post_opizo_shrinked != "1")
    {
        $_POST["opizo-shrink-link-in-post"] = true;
        $this->shrink_urls_in_content($current_post_id);
        $check_completed = get_post_meta($current_post_id,"_opizo_shrinked",true);
    }
    else
        $check_completed = "1";

    if($check_completed == "1")
    {
        $opizo_old_links = get_option("opizo_old_links");
        if (($key = array_search($current_post_id, $opizo_old_links)) !== false) {
            unset($opizo_old_links[$key]);
            $opizo_old_links = array_values($opizo_old_links);
            update_option("opizo_old_links",$opizo_old_links);
        }
    }

}

$total = count($opizo_old_links);
$item_per_page = 50;
$paged = isset($_GET['paged']) ? $_GET['paged'] : 1;
$paged = $paged < 1 ? 1 : $paged;
$total_page = ceil($total / $item_per_page);

$_chunk_page = @array_chunk($opizo_old_links,$item_per_page);
$_chunked_page = $_chunk_page[$paged - 1];

$pagination = array(
    'format'             => '?paged=%#%',
    'total'              => $total_page,
    'current'            => $paged,
    'show_all'           => false,
    'end_size'           => 2,
    'mid_size'           => 2,
    'prev_next'          => true,
    'prev_text'          => __('Previous',"opizo"),
    'next_text'          => __('Next',"opizo"),
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
    <div class="opizo-setting-admin-message"><?php echo __("For shrinking URLs in old posts, go to Post lists, select desired posts then select \"Shrink links by Opizo\" from Bulk Actions, then return this page and press \"Start Shrinking\" button.", 'opizo') ?>
        <button id="toggle_tutorial" class="button"><?php echo __("Image Guide","opizo"); ?></button>
        <div id="image_tutorial" style="display: none"><img src="<?php
            if(strtolower(get_locale()) == 'fa_ir')
                echo plugin_dir_url(__FILE__) . '../images/old_post_help_fa.png';
            else
                echo plugin_dir_url(__FILE__) . '../images/old_post_help.png';
            ?>"></div>
    </div>
    <br>
    <div class="opizo-shrinked-urls container">

        <div style="text-align: center">
            <?php
            if(count($opizo_old_links))
            {
                if(!empty($_REQUEST["start"]))
                {
                    ?>
                    <img src="<?php echo plugin_dir_url(__FILE__) . '../images/loading.gif' ?>">
                    <br />
                    <a class="start-button" style="text-decoration: none;" href="<?php echo get_admin_url(null, "admin.php?page=opizo_old_post_shrinkener")?>"><?php echo __("Stop","opizo")?></a>
                    <h3 style="color: #F00;"><?php echo __("Don't close this page, wait for complete","opizo") ?></h3>
                    <?php
                }
                else
                {
                    ?>
                    <a class="start-button" style="text-decoration: none;" href="<?php echo get_admin_url(null, "admin.php?page=opizo_old_post_shrinkener&start=1")?>"><?php echo __("Start Shrinking","opizo")?></a>
                    <?php
                }
            }
            else
            {
                ?>
                <a class="start-button disabled" style="text-decoration: none;"><?php echo __("Start Shrinking","opizo")?></a>
                <?php
            }
            ?>
        </div>
        <br />
        <div class="opizo-pagination-container">
            <?php echo paginate_links( $pagination );?>
        </div>
        <table id="opizo-old-post-list" class="opizo-shrinked-urls table" cellpadding="0" cellspacing="0">
            <tr>
                <th style="min-width: 1%; white-space: nowrap; padding: 0 15px;"></th>
                <th style="width: 98%;"><?php echo __('Post title','opizo')?></th>
                <!--<th style="width: 1%; white-space: nowrap;"><?php /*echo __('Queue Number','opizo')*/?></th>-->
            </tr>
            <?php
            if(count($_chunk_page))
            {
                if(count($_chunked_page))
                {
                    foreach($_chunked_page as $key => $_post_id)
                    {
                        ?>
                        <tr>
                            <td><a class="opizo-delete-old-post" data-opizo-old-post-id="<?php echo $_post_id ?>" href="javascript:return;"><img src="<?php echo plugin_dir_url(__FILE__) . '../images/delete.png';?>" /></a></td>
                            <td><p><a href="<?php echo get_permalink($_post_id)?>"><?php echo wp_trim_words(get_the_title($_post_id),35)?></a></p></td>
                            <!--<td><p><?php /*echo (($item_per_page * ($paged-1)) + $key) + 1;*/?></p></td>-->
                        </tr>
                        <?php
                    }
                }
            }
            else
            {
                ?>
                <tr>
                    <td colspan="3" align="center"><h3><?php echo __("There is no post to shrink","opizo") ?></h3></td>
                </tr>
                <?php
            }

            ?>
        </table>
    </div>
</div>

<script>
    var total = <?php echo count($opizo_old_links)?>;
    var refresh_rate = 5000;
    function getUrlVars()
    {
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for(var i = 0; i < hashes.length; i++)
        {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    }
    jQuery(document).ready(function(){
        var started = getUrlVars();
        started = started["start"];

        if(started == 1 && total > 0)
        {
            if(jQuery("#opizo-old-post-list tr").length === 1)
            {
                setTimeout(function(){
                    window.location.replace("<?php echo get_admin_url(null, "admin.php?page=opizo_old_post_shrinkener&start=1"); ?>");
                    },refresh_rate);
            }
            else
            {
                setTimeout(function(){
                    location.reload();
                },refresh_rate);
            }
        }
    });
</script>