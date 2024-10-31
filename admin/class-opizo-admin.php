<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://opizo.com
 * @since      1.0.0
 *
 * @package    Opizo
 * @subpackage Opizo/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Opizo
 * @subpackage Opizo/admin
 * @author     Opizo <opizo.com@gmail.com>
 */
class Opizo_Admin
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    private $black_list_option_name;
    private $active_plugin_option_name;
    private $shrinked_urls_table_name;

    private $default_options = array("active" => "1",
        "api-key" => "",
        "domains" => "",
        "post-types" => Array("post"));
    private $default_options_active_domain = array("active_domain" => "http://opizo.me",
        "last-update" => "0000-00-00 00:00:00",);

    private $active_domain_fetch = array("http://opizo.com/active_domain.txt",
        "https://plugins.svn.wordpress.org/opizo/trunk/active_domain.txt");

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return $this->default_options;
    }

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $plugin_name The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        global $wpdb;
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->black_list_option_name = $plugin_name . "_black_list";
        $this->active_plugin_option_name = $plugin_name . "_active_domain";
        $this->shrinked_urls_table_name = $wpdb->prefix . OPIZO_PLUGIN_DB_TABLE_NAME;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Opizo_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Opizo_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        if (is_rtl())
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/opizo-admin-rtl.css', array(), $this->version, 'all');
        else
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/opizo-admin.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/opizo-admin.js', array('jquery'), $this->version, false);

        /*Mojtaba*/
        wp_enqueue_script($this->plugin_name . 'flot', plugin_dir_url(__FILE__) . 'js/flot/jquery.flot.js', array('jquery'), $this->version);
        wp_enqueue_script($this->plugin_name . 'flot.stack', plugin_dir_url(__FILE__) . 'js/flot/jquery.flot.stack.js', array('jquery'), $this->version);
        wp_enqueue_script($this->plugin_name . 'flot.resize', plugin_dir_url(__FILE__) . 'js/flot/jquery.flot.resize.js', array('jquery'), $this->version);
        wp_enqueue_script($this->plugin_name . 'flot.navigate', plugin_dir_url(__FILE__) . 'js/flot/jquery.flot.navigate.js', array('jquery'), $this->version);
        wp_enqueue_script($this->plugin_name . 'flot.tooltip', plugin_dir_url(__FILE__) . 'js/flot/jquery.flot.tooltip.js', array('jquery'), $this->version);

        wp_localize_script($this->plugin_name, 'opizo_translations', array(
                'delete_link' => __("Delete Link?\nThis link only delete from wordpress and still exist in your panel in Opizo.com", "opizo"),
                'delete_old_post' => __("Cancel shrinking?", "opizo")
        ));
    }


    /*Mojtaba*/

    public function check_update_db()
    {
        global $wpdb;
        if (get_option('opizo_db_version') != OPIZO_PLUGIN_DB_VERSION)
        {
            require_once plugin_dir_path(__FILE__) . '../includes/class-opizo-activator.php';
            Opizo_Activator::installDBTable();
        }

        $check_empty_url_crc = $wpdb->get_var("SELECT COUNT(*) FROM `$this->shrinked_urls_table_name` WHERE `url_crc` = 0");

        if ($check_empty_url_crc > 0)
        {
            $empty_url_crc = $wpdb->get_results("SELECT id,url FROM `$this->shrinked_urls_table_name` WHERE `url_crc` = 0 LIMIT 100");
            foreach ($empty_url_crc as $item)
            {
                $url_id = $item->id;
                $url_crc = $this->url_crc($item->url);
                $wpdb->query("UPDATE `$this->shrinked_urls_table_name` SET `url_crc` = $url_crc WHERE id = $url_id");
            }
        }
    }

    public function add_admin_page()
    {
        $page_title = __('Opizo Settings', 'opizo');
        $menu_title = __('Opizo', 'opizo');
        add_menu_page($page_title, $menu_title, 'manage_options', $this->plugin_name, array($this,
            'load_admin_page_main'), plugin_dir_url(__FILE__) . 'images/icon.png');
        add_submenu_page($this->plugin_name, __('Shrink', 'opizo'), __('Shrink Link', 'opizo'), 'manage_options', $this->plugin_name . '_shrink', array($this,
            'load_admin_page_shrink'));
        add_submenu_page($this->plugin_name, __('Shrink Old Posts', 'opizo'), __('Shrink Old Posts', 'opizo'), 'manage_options', $this->plugin_name . '_old_post_shrinkener', array($this,
            'load_admin_page_old_post_shrinkener'));
        add_submenu_page($this->plugin_name, __('Shrinked URLs', 'opizo'), __('Shrinked URLs', 'opizo'), 'manage_options', $this->plugin_name . '_shrinked_urls', array($this,
            'load_admin_page_shrinked_urls'));
    }

    public function load_admin_page_main()
    {
        require_once plugin_dir_path(__FILE__) . 'partials/opizo-admin-display.php';
    }

    public function load_admin_page_shrink()
    {
        require_once plugin_dir_path(__FILE__) . 'partials/opizo-admin-shrink.php';
    }

    public function load_admin_page_shrinked_urls()
    {
        require_once plugin_dir_path(__FILE__) . 'partials/opizo-admin-shrinked-urls.php';
    }

    public function load_admin_page_old_post_shrinkener()
    {
        require_once plugin_dir_path(__FILE__) . 'partials/opizo-admin-old-post-shrinkener.php';
    }

    public function register_setting()
    {
        register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
    }

    public function validate($input)
    {
        $valid = array();
        $valid['active'] = (isset($input['active']) && !empty($input['active'])) ? 1 : 0;
        $valid['api-key'] = (isset($input['api-key']) && !empty($input['api-key'])) ? $input['api-key'] : null;
        $valid['domains'] = (isset($input['domains']) && !empty($input['domains'])) ? $input['domains'] : null;
        $valid['post-types'] = (isset($input['post-types']) && !empty($input['post-types'])) ? $input['post-types'] : null;

        return $valid;
    }

    public function get_option($options, $key, $default = null)
    {
        $return = $default;
        foreach ($options as $_key => $_option)
        {
            if ($_key == $key)
            {
                $return = $_option;
                break;
            }
        }
        return $return;
    }

    public function shrink_urls_in_content($post_id)
    {
        $options = get_option($this->plugin_name, $this->default_options);
        if (strlen($options["api-key"]) != 32)
            return;

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        if (defined('DOING_AJAX') && DOING_AJAX)
            return;

        if (false !== wp_is_post_revision($post_id))
            return;

        if (!array_key_exists('opizo-shrink-link-in-post', $_POST))
        {
            return;
        }

        $post_content = get_post_field('post_content', $post_id);

        $text = $this->shrink($post_content, $post_id);

        /*
        $post = array('ID' => $post_id, 'post_content' => $text);
        remove_action('save_post', array($this, 'shrink_urls_in_content'));
        wp_update_post($post);
        add_action('save_post', array($this, 'shrink_urls_in_content'));
        */
    }

    public function replace_shrinked_in_content($content)
    {
        return $this->replace_shrinked($content);
    }

    private function shrink($content, $post_id, $from_content = true)
    {
        global $wpdb;
        $_any_error_occurred = false;
        $_shrink_permission = null;
        $_api_active_domain = "";
        $text = preg_replace("/\{URLTOSHRINK[0-9]+\}/", "", $content);

        if ($from_content)
        {
            preg_match_all('/<a(.*)href(?:\s*)=(?:\s*)[\\"|\'](((https?:\/\/)([a-zA-Z0-9.\/?=@\-:;&#+%\p{Arabic}_\-\(\)\[\]!]{5,})))[\\"|\'](.*)>(.*)<\/a>/miU', $text, $matches, PREG_SET_ORDER, 0);
            $match_index = 2;
        }
        else
        {
            preg_match_all('/((https?:\/\/)([a-zA-Z0-9.\/?=@\-:;&#+%\p{Arabic}_\-\(\)\[\]!]{5,}))/ui', $text, $matches, PREG_SET_ORDER, 0);
            $match_index = 1;
        }

        $urls = array();
        $counter = 0;
        foreach ($matches as $match)
        {
            $url = $match[$match_index];

            if ($from_content)
            {
                if ($this->checkExistUrlInDB($url))
                    continue;
            }

            if (!$this->checkDomains($url))
                continue;

            $urls[$counter] = $url;
            $text = str_replace("$url", "{URLTOSHRINK" . $counter . "}", $text);
            $counter++;
        }


        $urls = array_filter($urls);
        if (empty($urls))
        {
            if($from_content)
            {
                add_post_meta($post_id, '_opizo_shrinked', '1', true);
            }
            return $content;
        }

        $new_links = array();

        $chunk_urls = array_chunk($urls, 50);

        foreach ($chunk_urls as $_urls)
        {
            $_opizo_links = OpizoApi::getInstance()->Shrink($_urls);
            if ($_opizo_links["status"] == "success")
            {
                $_api_active_domain = $_opizo_links["data"]["active_domain"];
                $new_links = array_merge($new_links, $_opizo_links["data"]["urls"]);
            }
            else
            {
                $_any_error_occurred = true;
            }
            /*
            else
                return $content;
            */
        }

        if (count($new_links) > 0)
        {
            $chunk_new_links = array_chunk($new_links, 20);
            $counter = 0;
            if ($from_content)
            {
                foreach ($chunk_new_links as $chunk_new_link)
                {
                    $values = array();
                    $query = "INSERT INTO `" . $this->shrinked_urls_table_name . "` (`post_id`, `url`, `shrinked`, `url_crc`, `shrinked_crc`, `shrink_date`) VALUES ";

                    foreach ($chunk_new_link as $_new_link)
                    {
                        if (substr($_new_link, 0, strlen($_api_active_domain)) == $_api_active_domain)
                        {
                            $_new_link = str_replace($_api_active_domain, '', $_new_link);
                            $shrinked_crc = $this->url_crc($_new_link);
                            $url_crc = $this->url_crc($urls[$counter]);
                            if (is_null($wpdb->get_var($wpdb->prepare("SELECT id FROM `" . $this->shrinked_urls_table_name . "` WHERE `post_id` = %d AND `shrinked_crc` = %s AND shrinked = %s", $post_id, $shrinked_crc, $_new_link))))
                            {
                                $values[] = $wpdb->prepare("(%d,%s,%s,%s,%s,NOW())", $post_id, $urls[$counter], $_new_link, $url_crc, $shrinked_crc);
                            }
                        }
                        $counter++;
                    }

                    $query .= implode(",\n", $values);
                    $wpdb->query($query);
                }

                if (!$_any_error_occurred)
                    add_post_meta($post_id, '_opizo_shrinked', '1', true);

                return $content;
            }
            else
            {
                $counter = 0;
                foreach ($new_links as $new_link)
                {
                    $text = str_replace("{URLTOSHRINK$counter}", $new_link, $text);
                    $counter++;
                }
                return $text;
            }
        }
        else
        {
            if (!$_any_error_occurred)
                add_post_meta($post_id, '_opizo_shrinked', '1', true);

            return $content;
        }
    }

    public function replace_shrinked($content)
    {
        global $wpdb;
        $shrinked_urls = array();
        $post_id = get_the_ID();

        if ($post_id <= 0)
            return $content;

        $options = get_option($this->plugin_name, $this->default_options);
        $active = $options['active'];
        if ($active != 1)
            return $content;

        if ($post_id)
            $shrinked_urls = $wpdb->get_results("SELECT * FROM " . $this->shrinked_urls_table_name . " WHERE `post_id` = $post_id");

        usort($shrinked_urls, array($this, 'sortByLength'));

        foreach ($shrinked_urls as $shrinked_url)
        {
            $content = str_replace($shrinked_url->url, $this->getActiveDomain() . $shrinked_url->shrinked, $content);
        }
        return $content;
    }


    public function add_meta_box()
    {
        global $wpdb, $post_id;
        $shrinked_urls = 0;
        if ($post_id)
            $shrinked_urls = $wpdb->get_var("SELECT COUNT(*) FROM " . $this->shrinked_urls_table_name . " WHERE `post_id` = $post_id");

        $options = get_option($this->plugin_name, $this->default_options);

        $active = $options['active'];
        $screens = $options['post-types'];

        foreach ($screens as $screen)
        {
            if ($active == 1)
                add_meta_box('opizo_shrink', __('Shrink URLs', 'opizo'), array($this,
                    'meta_box_shrink'), $screen, 'side', 'high');

            if ($shrinked_urls > 0)
                add_meta_box('opizo_shrinked_urls', __('Shrinked URLs of this post', 'opizo'), array($this,
                    'meta_box_shrinked'), $screen, 'advanced', 'high');
        }
    }

    public function meta_box_shrink()
    {
        $options = get_option($this->plugin_name, $this->default_options);
        $api_key = $options["api-key"];
        ?>
        <img style="width: 100%; max-width: 150px;" src="<?php echo plugin_dir_url(__FILE__) . 'images/logo.png' ?>">
        <hr/>
        <div>
            <label style="font-weight: bold">
                <input type="checkbox"
                       name="opizo-shrink-link-in-post"> <?php echo __("Shrink urls in content?", 'opizo') ?>
            </label>
            <div style="color: #47aae0;"><?php echo __("Shrinking urls not change post content, only replace urls via shrinked link on post view", 'opizo') ?></div>
            <?php
            if (strlen($api_key) != 32)
            {
                ?>
                <div style="color:#f00;">
                    <?php
                    echo sprintf(wp_kses(__("Please enter valid API code in <a href='%s'>Setting Page</a> ", 'opizo'), array('a' => array('href' => array()))), get_admin_url(null, "admin.php?page=opizo"));
                    ?>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
    }

    public function meta_box_shrinked()
    {
        global $wpdb;
        $shrinked_urls = array();
        $post_id = get_the_ID();

        if ($post_id)
            $shrinked_urls = $wpdb->get_results("SELECT * FROM " . $this->shrinked_urls_table_name . " WHERE `post_id` = $post_id");

        if (count($shrinked_urls) > 0)
        {
            ?>
            <div style="position:relative; width: 100%; overflow: scroll; height: 250px;">
                <p><?php echo __("Shrinked URLs of this post:", "opizo") ?>
                    <strong><?php echo count($shrinked_urls) ?></strong></p>
                <table class="opizo-shrinked-urls table" cellpadding="0" cellspacing="0">
                    <?php
                    foreach ($shrinked_urls as $shrinked_url)
                    {
                        ?>
                        <tr>
                            <td style="min-width: 1%; white-space: nowrap; padding: 0 5px;"><a class="opizo-delete-link"
                                                                                               data-opizo-link-id="<?php echo $shrinked_url->id ?>"
                                                                                               href="javascript:return;"><img
                                            src="<?php echo plugin_dir_url(__FILE__) . 'images/delete.png'; ?>"/></a>
                            </td>
                            <td>
                                <pre class="opizo-shrinked-urls shrinked"><?php echo $this->getActiveDomain() . $shrinked_url->shrinked ?></pre>
                            </td>
                            <td>
                                <pre class="opizo-shrinked-urls url"><a
                                            href="<?php echo $shrinked_url->url ?>"><?php echo $shrinked_url->url ?></a></pre>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
            </div>
            <?php
        }
    }

    private function checkDomains($url)
    {
        $parsed_url = parse_url(strtolower($url));

        $options = get_option($this->plugin_name, $this->default_options);
        $black_list = get_option($this->black_list_option_name);

        $black_list_urls = isset($black_list['urls']) ? $black_list['urls'] : array();
        $black_list_last_update = isset($black_list['last-update']) ? $black_list['last-update'] : 0;

        if (@strtotime($black_list_last_update) <= (strtotime("-24 hour")))
        {
            $black_list_check = OpizoApi::getInstance()->GetBlackListURLs();
            if ($black_list_check['status'] == 'success')
            {
                $black_list_urls = $black_list_check['data']['urls'];
                update_option($this->black_list_option_name, array('urls' => $black_list_urls,
                    'last-update' => date('Y-m-d H:i:s')));
            }
        }

        $url_domain = str_replace("www.", "", $parsed_url['host']);
        if (count($black_list_urls) > 0)
        {
            if (in_array($url_domain, $black_list_urls))
                return false;
        }

        $domains = array_filter(explode(',', $options['domains']));
        if (empty($domains))
            return true;

        $return = in_array($url_domain, $domains);
        return $return;
    }

    private function getActiveDomain()
    {
        $options = get_option($this->active_plugin_option_name, $this->default_options_active_domain);
        $active_domain = $options['active_domain'];
        $active_domain_last_check = $options['last-update'];

        if (@strtotime($active_domain_last_check) <= (strtotime("-24 hour")))
        {
            foreach ($this->active_domain_fetch as $active_domain_fetch)
            {
                $active_domain_text = @file_get_contents($active_domain_fetch);
                if (trim($active_domain_text) != '')
                {
                    $active_domain_parse = parse_url($active_domain_text);
                    if ($active_domain_parse['host'])
                    {
                        $active_domain = $active_domain_text;
                        break;
                    }
                }
            }

            $validate_active_domain = parse_url($active_domain);
            if (!isset($validate_active_domain['host']))
            {
                $active_domain = 'http://opzio.me/';
            }

            if (substr($active_domain, -1, 1) != '/')
                $active_domain = $active_domain . '/';

            update_option($this->active_plugin_option_name, array('active_domain' => $active_domain,
                'last-update' => date('Y-m-d H:i:s')));
        }

        return $active_domain;
    }

    private function checkExistUrlInDB($url)
    {
        global $wpdb;
        $post_id = get_the_ID();
        $shrinked_crc = $this->url_crc($url);

        return $wpdb->get_var($wpdb->prepare("SELECT id FROM `" . $this->shrinked_urls_table_name . "` WHERE `post_id` = %d AND `url_crc` = %s AND url = %s", $post_id, $shrinked_crc, $url)) ? true : false;
    }

    public function ajax_shrink()
    {
        $content = stripslashes_deep($_POST["links-to-shrink"]);
        $text = $this->shrink($content, 0, false);

        echo $text;
        wp_die();
    }

    public function ajax_delete_link()
    {
        global $wpdb;
        $id = $_POST["id"];
        $wpdb->delete($this->shrinked_urls_table_name, array('id' => $id), array('%d'));
        echo "ok";
        wp_die();
    }

    public function ajax_delete_old_post()
    {
        $id = $_POST["id"];
        $opizo_old_links = get_option("opizo_old_links");
        if (($key = array_search($id, $opizo_old_links)) !== false) {
            unset($opizo_old_links[$key]);
            $opizo_old_links = array_values($opizo_old_links);
            update_option("opizo_old_links",$opizo_old_links);
        }
        echo "ok";
        wp_die();
    }

    public function ajax_statics()
    {
        $statics = OpizoApi::getInstance()->GetStaticsChart();
        $unread_message = OpizoApi::getInstance()->GetMessageInbox(true);
        if ($unread_message["code"] == 200)
        {
            if (count($unread_message["data"]) > 0)
                $statics["unread_messages"] = $unread_message["data"];
        }
        echo json_encode($statics);
        wp_die();
    }

    private function validateUrl($url)
    {
        $regex = "/^(?:(?:https?|ftp):\/\/)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:\/[^\s]*)?$/iuS";
        return preg_match($regex, $url) ? $url : null;
    }

    public function dashboard_widgets()
    {
        wp_add_dashboard_widget('opizo_dashboard_widget', __('Opizo Status', 'opizo'), array($this,
            'dashboard_widget_render'));
    }

    public function dashboard_widget_render()
    {
        ?>
        <div id="opizo_unread_messages"></div>
        <div id="opizo_graph_chart" style="width: 100%; min-width: 200px; min-height: 150px"></div>
        <script>

            var chartColours = ['#96CA59', '#3F97EB', '#FF9C1D', '#6f7a8a', '#f7cb38', '#5a8022', '#2c7282'];

            (function ($) {
                'use strict';
                $(document).ready(function () {

                    //graph options
                    var options = {
                        grid: {
                            show: true,
                            aboveData: true,
                            color: "#3f3f3f",
                            labelMargin: 10,
                            axisMargin: 0,
                            borderWidth: 0,
                            borderColor: null,
                            minBorderMargin: 5,
                            clickable: true,
                            hoverable: true,
                            autoHighlight: true,
                            mouseActiveRadius: 100
                        },
                        series: {
                            lines: {
                                show: true,
                                fill: true,
                                lineWidth: 2,
                                steps: false
                            },
                            points: {
                                show: true,
                                radius: 4.5,
                                symbol: "circle",
                                lineWidth: 3.0
                            }
                        },
                        legend: {
                            position: "ne"
                        },
                        colors: chartColours,
                        shadowSize: 0,
                        tooltip: true, //activate tooltip
                        tooltipOpts: {
                            content: "<?php echo __("Earning: %y", 'opizo') ?>",
                            shifts: {
                                x: -30,
                                y: -50
                            },
                            defaultTheme: false
                        },
                        yaxis: {
                            min: 0
                        },
                        xaxis: {
                            ticks: function () {
                                var labels = [];
                                var out = [];
                                for (var i = 0; i < labels.length; i++)
                                    out.push([i, labels[i]]);
                                return out;
                            }
                        },
                        navigationControl: {
                            homeRange: {xmin: -10, xmax: 10, ymin: -10, ymax: 10},
                            panAmount: 100,
                            zoomAmount: 1.5,
                            position: {left: "20px", top: "20px"}
                        }
                    };


                    function onDataReceived(series) {
                        options.xaxis.ticks = function () {
                            var labels = series.graph_labels;
                            var out = [];
                            for (var i = 0; i < labels.length; i++)
                                out.push([i, labels[i]]);
                            return out;
                        };

                        $.plot("#opizo_graph_chart",
                            [{
                                label: "<?php echo __("Earning from links", 'opizo') ?>",
                                data: series.visit_logs
                            }, {
                                label: "<?php echo __("Earning from referral", 'opizo') ?>",
                                data: series.ref_visit_logs
                            }]
                            , options);
                    }

                    $.post(ajaxurl, {'action': 'opizo_statics'}, function (data) {
                        data = jQuery.parseJSON(data);

                        if (data.unread_messages) {
                            var message_list = $('<ul/>').addClass("opizo-messages");

                            $.each(data.unread_messages, function (i, message) {

                                /*
                                <ul class="community-events-results activity-block last" aria-hidden="false">
			<li class="event event-meetup wp-clearfix">
				<div class="event-info">
					<div class="dashicons event-icon" aria-hidden="true"></div>
					<div class="event-info-inner">
						<a class="event-title" href="https://wordpress.org/news/2018/04/celebrate-the-wordpress-15th-anniversary-on-may-27/">WP15</a>
						<span class="event-city">Everywhere</span>
					</div>
				</div>

				<div class="event-date-time">
					<span class="event-date">Sunday, May 27, 2018</span>

						<span class="event-time">12:00 pm</span>

				</div>
			</li>
	</ul>
                                 */
                                message_list.append(
                                    $('<li/>')
                                        .addClass("message message-meetup wp-clearfix")
                                        .append(
                                            $("<div/>")
                                                .addClass("message-info")
                                                .append(
                                                    $("<div/>")
                                                        .addClass("dashicons message-icon")
                                                )
                                                .append(
                                                    $("<div/>")
                                                        .addClass("message-info-inner")
                                                        .append(
                                                            $("<a/>")
                                                                .addClass("message-title")
                                                                .attr("href", "http://opizo.com/usercp/message/view/" + message.id)
                                                                .attr("target", "_blank")
                                                                .text(message.title)
                                                        )
                                                )
                                        )
                                );
                            });
                            $("#opizo_unread_messages").html(message_list);
                        }

                        if (data.code === 200) {
                            data = data.data;
                            onDataReceived(data);
                        }
                        else if (data.code === 403) {
                            $("#opizo_graph_chart").html("<?php
                                $url = 'http://opizo.com/';
                                $link = sprintf(wp_kses(__("Statics not allowed, login to your <a href='%s' target='_blank'>Opizo account</a> and change permission of API key.", 'opizo'), array('a' => array('href' => array(),
                                    'target' => array()))), esc_url($url));
                                echo $link;?>");
                        }
                    })
                    ;
                });
            })(jQuery);


        </script>
        <?php
    }

    private function url_crc($url)
    {
        return sprintf('%u', crc32($url));
    }

    private function sortByLength($a, $b)
    {
        if ($a->url == $b->url)
            return 0;
        return (strlen($a->url) > strlen($b->url) ? -1 : 1);
    }

    public function register_opizo_bulk_actions($bulk_actions)
    {
        $bulk_actions['shrink_by_opizo'] = __('Shrink links by Opizo', 'opizo');
        return $bulk_actions;
    }

    public function opizo_bulk_action_handler($redirect_to, $action, $post_ids)
    {
        if ($action !== 'shrink_by_opizo')
        {
            return $redirect_to;
        }

        $old_posts = get_option("opizo_old_links");

        /*
        foreach ($post_ids as $key => $post_id)
        {
            if(get_post_meta($post_id,"_opizo_shrinked",true) == "1")
                unset($post_ids[$key]);
        }

        $post_ids = array_values($post_ids);
        */

        if (is_array($old_posts))
            $old_posts = array_merge($old_posts, $post_ids);
        else
            $old_posts = $post_ids;

        $old_posts = array_unique($old_posts);


        update_option('opizo_old_links', $old_posts);

        //$redirect_to = 'admin.php?page=opizo';
        $redirect_to = add_query_arg('opizo_add_to_shrink_list', count($post_ids), $redirect_to);
        return $redirect_to;
    }

    public function opizo_bulk_action_admin_notice()
    {
        if (!empty($_REQUEST['opizo_add_to_shrink_list']))
        {
            $post_count = intval($_REQUEST['opizo_add_to_shrink_list']);
            printf('<div id="message" class="updated notice is-dismissible"><p><img style="margin: 0 5px -5px 5px" src="'.plugin_dir_url(__FILE__) . "images/icon.png".'">' . sprintf(
                    wp_kses(
                        __("%s post(s) add to \"<a href='%s'>Shrink Old Post</a>\" list, after select desired posts, go to \"<a href='%s'>Shrink Old Post</a>\" page and press \"Start Shrinking\" Or <a href='%s'>Start Shrinking Now</a>", 'opizo'),
                        array('a' => array('href' => array()))),
                    $post_count,
                    get_admin_url(null, "admin.php?page=opizo_old_post_shrinkener"),
                    get_admin_url(null, "admin.php?page=opizo_old_post_shrinkener"),
                    get_admin_url(null, "admin.php?page=opizo_old_post_shrinkener&start=1")
                ) . '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>');
        }
    }
}
