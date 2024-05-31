<?php

/**
 * Fired during plugin activation
 *
 * @link       https://https://example.com
 * @since      1.0.0
 *
 * @package    Wp_File_Scanner
 * @subpackage Wp_File_Scanner/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp_File_Scanner
 * @subpackage Wp_File_Scanner/includes
 * @author     Hardip Parmar <parmarhardip1995@gmail.com>
 */
class Wp_File_Scanner_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'file_scan_results';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            type varchar(20) NOT NULL,
            size varchar(50) NOT NULL,
            nodes int NOT NULL,
            path text NOT NULL,
            name varchar(255) NOT NULL,
            extension varchar(10) NOT NULL,
            permissions varchar(10) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

}
