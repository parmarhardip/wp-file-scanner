<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link  https://https://example.com
 * @since 1.0.0
 *
 * @package    Wp_File_Scanner
 * @subpackage Wp_File_Scanner/admin/partials
 */

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<?php
if (! defined('ABSPATH') ) {
    exit; // Exit if accessed directly
}

$pagination_base = add_query_arg('paged', '%#%');
$pagination      = paginate_links(
    array(
        'base'      => $pagination_base,
        'format'    => '',
        'prev_text' => __('&laquo;'),
        'next_text' => __('&raquo;'),
        'total'     => $pagination_args['total_pages'],
        'current'   => $paged
    )
);
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form method="post">
		<?php wp_nonce_field( 'scan_action', 'scan_nonce' ); ?>
        <p><input type="submit" name="scan_now" class="button button-primary" value="<?php esc_attr_e( 'Scan Now', 'wp-file-scanner' ); ?>" /></p>
    </form>
    <table class="wp-list-table widefat fixed striped table-view-list">
        <thead>
        <tr>
            <th><?php esc_html_e('Type', 'wp-file-scanner'); ?></th>
            <th><?php esc_html_e('Size', 'wp-file-scanner'); ?></th>
            <th><?php esc_html_e('Nodes', 'wp-file-scanner'); ?></th>
            <th><?php esc_html_e('Path', 'wp-file-scanner'); ?></th>
            <th><?php esc_html_e('Name', 'wp-file-scanner'); ?></th>
            <th><?php esc_html_e('Extension', 'wp-file-scanner'); ?></th>
            <th><?php esc_html_e('Permissions', 'wp-file-scanner'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (empty($results) ) : ?>
            <tr>
                <td colspan="7">
                    <?php
                    esc_html_e('No files or directories found.', 'wp-file-scanner');
                    ?>
                </td>
            </tr>
            <?php
        else : ?>
            <?php
            foreach ( $results as $result ): ?>
                <tr>
                    <td><?php
                    echo esc_html($result->type); ?></td>
                    <td><?php
                    echo esc_html($result->size); ?></td>
                    <td><?php
                    echo esc_html($result->nodes); ?></td>
                    <td><?php
                    echo esc_html($result->path); ?></td>
                    <td><?php
                    echo esc_html($result->name); ?></td>
                    <td><?php
                    echo esc_html($result->extension); ?></td>
                    <td><?php
                    echo esc_html($result->permissions); ?></td>
                </tr>
                <?php
            endforeach; ?>
            <?php
        endif; ?>
        </tbody>
    </table>
    <div class="tablenav">
        <div class="tablenav-pages">
            <?php echo wp_kses_post($pagination); ?>
        </div>
    </div>
</div>
