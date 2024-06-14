<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://https://example.com
 * @since      1.0.0
 *
 * @package    Wp_File_Scanner
 * @subpackage Wp_File_Scanner/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wp_File_Scanner
 * @subpackage Wp_File_Scanner/includes
 * @author     Hardip Parmar <parmarhardip1995@gmail.com>
 */
class Wp_File_Scanner_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'file-scanner',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
