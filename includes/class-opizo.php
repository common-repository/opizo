<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://opizo.com
 * @since      1.0.0
 *
 * @package    Opizo
 * @subpackage Opizo/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Opizo
 * @subpackage Opizo/includes
 * @author     Opizo <opizo.com@gmail.com>
 */
class Opizo
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Opizo_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        if (defined('OPIZO_PLUGIN_VERSION'))
        {
            $this->version = OPIZO_PLUGIN_VERSION;
        }
        else
        {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'opizo';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        //$this->define_public_hooks();

    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Opizo_Loader. Orchestrates the hooks of the plugin.
     * - Opizo_i18n. Defines internationalization functionality.
     * - Opizo_Admin. Defines all hooks for the admin area.
     * - Opizo_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-opizo-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-opizo-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-opizo-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        //require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-opizo-public.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-opizo-api.php';

        $this->loader = new Opizo_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Opizo_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new Opizo_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {

        $plugin_admin = new Opizo_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('plugins_loaded', $plugin_admin, 'check_update_db');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        /*Mojtaba*/
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_page');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_setting');
        $this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_meta_box');
        $this->loader->add_action('wp_ajax_opizo_shrink', $plugin_admin, 'ajax_shrink');
        $this->loader->add_action('wp_ajax_opizo_statics', $plugin_admin, 'ajax_statics');
        $this->loader->add_action('wp_ajax_opizo_delete_link', $plugin_admin, 'ajax_delete_link');
        $this->loader->add_action('wp_ajax_opizo_delete_old_post', $plugin_admin, 'ajax_delete_old_post');
        $this->loader->add_action('wp_dashboard_setup', $plugin_admin, 'dashboard_widgets');

        $this->loader->add_filter('save_post', $plugin_admin, 'shrink_urls_in_content', 10, 3);
        $this->loader->add_filter('the_content', $plugin_admin, 'replace_shrinked_in_content');
        $this->loader->add_action('admin_notices', $plugin_admin, 'opizo_bulk_action_admin_notice');

        $options = get_option($this->plugin_name, $plugin_admin->getDefaultOptions());
        if (is_array($options["post-types"]))
        {
            foreach ($options["post-types"] as $_post_type)
            {
                $this->loader->add_filter('bulk_actions-edit-' . $_post_type, $plugin_admin, 'register_opizo_bulk_actions');
                $this->loader->add_filter('handle_bulk_actions-edit-' . $_post_type, $plugin_admin, 'opizo_bulk_action_handler', 10, 3);
            }
        }
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {
        //$plugin_public = new Opizo_Public($this->get_plugin_name(), $this->get_version());

        //$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        //$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Opizo_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }


}
