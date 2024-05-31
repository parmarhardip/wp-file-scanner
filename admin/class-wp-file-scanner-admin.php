<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://example.com
 * @since      1.0.0
 *
 * @package    Wp_File_Scanner
 * @subpackage Wp_File_Scanner/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_File_Scanner
 * @subpackage Wp_File_Scanner/admin
 * @author     Hardip Parmar <parmarhardip1995@gmail.com>
 */
class Wp_File_Scanner_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * load init.
	 */
	public function admin_init() {
		global $wpdb;

		// Handle Scan Now button click
		$scan_now   = filter_input( INPUT_POST, 'scan_now', FILTER_SANITIZE_STRING );
		$scan_nonce = filter_input( INPUT_POST, 'scan_nonce', FILTER_SANITIZE_STRING );

		if ( isset( $scan_now ) && isset( $scan_nonce ) && wp_verify_nonce( $scan_nonce, 'scan_action' ) ) {
			// Clear previous scan results
			$wpdb->query( $wpdb->prepare( "TRUNCATE TABLE " . $wpdb->prefix . 'file_scan_results' ) );

			// Scan the root directory
			$scan_results = $this->scan_directory( ABSPATH );

			// Insert new scan results
			foreach ( $scan_results as $result ) {
				$wpdb->insert(
					$wpdb->prefix . 'file_scan_results',
					array(
						'type'        => $result['type'],
						'size'        => $result['size'],
						'nodes'       => $result['nodes'],
						'path'        => $result['path'],
						'name'        => $result['name'],
						'extension'   => $result['extension'],
						'permissions' => $result['permissions']
					),
					array(
						'%s', // Type
						'%s', // Size
						'%d', // Nodes
						'%s', // Path
						'%s', // Name
						'%s', // Extension
						'%s'  // Permissions
					)
				);
			}

			// Clear cache for refreshed data
			wp_cache_delete( 'file_scan_results', 'file_scan_results' );
			wp_cache_delete( 'file_scan_total_items', 'file_scan_results' );

			// Redirect to avoid form resubmission
			wp_redirect( esc_url_raw( add_query_arg( 'page', 'file-scan', admin_url( 'admin.php' ) ) ) );
			exit;
		}
	}

	/**
	 * Display admin page
	 *
	 * @since 1.0.0
	 */
	public function register_admin_menu() {
		add_menu_page(
			__( 'File Scan', 'wp-file-scanner' ),
			__( 'File Scan', 'wp-file-scanner' ),
			'manage_options',
			'file-scan',
			array( $this, 'display_admin_page' ),
			'dashicons-admin-site',
			6
		);
	}

	/**
	 * Scan the directory
	 *
	 * @since 1.0.0
	 */
	public function scan_directory( $dir, &$results = array() ) {
		$files = scandir( $dir );

		foreach ( $files as $value ) {
			$path = realpath( $dir . DIRECTORY_SEPARATOR . $value );
			if ( ! is_dir( $path ) ) {
				$results[] = array(
					'type'        => 'file',
					'size'        => $this->format_size( filesize( $path ) ),
					'nodes'       => 0,
					'path'        => $path,
					'name'        => basename( $path ),
					'extension'   => pathinfo( $path, PATHINFO_EXTENSION ),
					'permissions' => substr( sprintf( '%o', fileperms( $path ) ), - 4 )
				);
			} elseif ( $value !== "." && $value !== ".." ) {
				$subDirNodes   = 0;
				$subDirResults = array();
				$this->scan_directory( $path, $subDirResults );
				foreach ( $subDirResults as $res ) {
					$subDirNodes += $res['nodes'];
				}
				$results[] = array(
					'type'        => 'directory',
					'size'        => '',
					'nodes'       => $subDirNodes,
					'path'        => $path,
					'name'        => basename( $path ),
					'extension'   => '',
					'permissions' => substr( sprintf( '%o', fileperms( $path ) ), - 4 )
				);
				$results   = array_merge( $results, $subDirResults );
			}
		}

		return $results;
	}

	/**
	 * Format file size
	 *
	 * @since 1.0.0
	 */
	private function format_size( $bytes ) {
		$sizes  = array( 'B', 'KB', 'MB', 'GB', 'TB' );
		$factor = floor( ( strlen( $bytes ) - 1 ) / 3 );

		$size_label = isset( $sizes[ $factor ] ) ? $sizes[ $factor ] : 'B';

		return sprintf( "%.2f %s", $bytes / pow( 1024, $factor ), $size_label );
	}

	/**
	 * Display admin page
	 *
	 * @since 1.0.0
	 */
	public function display_admin_page() {
		global $wpdb;

		// Fetch results for display with pagination
		$items_per_page = 10;
		$paged          = filter_input( INPUT_GET, 'paged', FILTER_VALIDATE_INT );
		$paged          = $paged ? $paged : 1;
		$offset         = ( $paged - 1 ) * $items_per_page;

		$total_items = wp_cache_get( 'file_scan_total_items', 'file_scan_results' );

		if ( false === $total_items ) {
			$total_items = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM " . $wpdb->prefix . 'file_scan_results' ) );
			wp_cache_set( 'file_scan_total_items', $total_items, 'file_scan_results' );
		}

		$results = wp_cache_get( 'file_scan_results', 'file_scan_results' );

		if ( false === $results ) {
			$results = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}file_scan_results LIMIT %d, %d", $offset, $items_per_page )
			);
			wp_cache_set( 'file_scan_results', $results, 'file_scan_results' );
		}

		$pagination_args = array(
			'total_items' => $total_items,
			'total_pages' => ceil( $total_items / $items_per_page ),
			'per_page'    => $items_per_page,
		);

		include_once plugin_dir_path( __FILE__ ) . 'partials/wp-file-scanner-admin-display.php';
	}

}
