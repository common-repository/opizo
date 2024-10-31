<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://opizo.com
 * @since             1.0.0
 * @package           Opizo
 *
 * @wordpress-plugin
 * Plugin Name:       Opizo
 * Plugin URI:        http://opizo.com/page/view/opizo_wp_plugin
 * Description:       Opizo Link Shortener and Make money system from Links and URLs
 * Version:           1.2.0
 * Author:            Opizo
 * Author URI:        http://opizo.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       opizo
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC'))
{
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('OPIZO_PLUGIN_VERSION', '1.2.0');
define('OPIZO_PLUGIN_DB_VERSION', '1.1.0');
define('OPIZO_PLUGIN_DB_TABLE_NAME', 'opizo_shrinked_urls');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-opizo-activator.php
 */
function activate_opizo()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-opizo-activator.php';
    Opizo_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-opizo-deactivator.php
 */
function deactivate_opizo()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-opizo-deactivator.php';
    Opizo_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_opizo');
register_deactivation_hook(__FILE__, 'deactivate_opizo');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-opizo.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_opizo()
{
    $plugin = new Opizo();
    $plugin->run();

}
run_opizo();