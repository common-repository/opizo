<?php

/**
 * Fired during plugin activation
 *
 * @link       http://opizo.com
 * @since      1.0.0
 *
 * @package    Opizo
 * @subpackage Opizo/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Opizo
 * @subpackage Opizo/includes
 * @author     Opizo <opizo.com@gmail.com>
 */
class Opizo_Activator
{
    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        self::installDBTable();
    }

    public static function installDBTable()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . OPIZO_PLUGIN_DB_TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
		id int(11) NOT NULL AUTO_INCREMENT,
		post_id int(11) NOT NULL,
		url text NOT NULL,
		shrinked text NOT NULL,
		url_crc int(11) unsigned NOT NULL,
		shrinked_crc int(11) unsigned NOT NULL,
		shrink_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY  (id),
		KEY `url_crc` (`url_crc`),
		KEY `shrinked_crc` (`shrinked_crc`)) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        //$row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$wpdb->dbname' AND TABLE_NAME = '$table_name' AND COLUMN_NAME = 'url_crc'");
        $row = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'url_crc'");

        if(empty($row)){
            $wpdb->query("ALTER TABLE `$table_name` ADD `url_crc` INT(11) UNSIGNED NOT NULL AFTER `shrinked`, ADD INDEX `url_crc` (`url_crc`);");
        }

        update_option('opizo_db_version', OPIZO_PLUGIN_DB_VERSION);
    }
}
