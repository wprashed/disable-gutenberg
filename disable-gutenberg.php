<?php
/*
Plugin Name: Disable Gutenberg
Plugin URI:  https://github.com/wprashed/disable-gutenberg
Description: Enable or disable Gutenberg editor for specific post types.
Version:     1.2
Author:      Rashed Hossain
Author URI:  https://rashed.im
Text Domain: disable-gutenberg
Domain Path: /languages
License:     GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Disable_Gutenberg_Plugin {

    private $option_name = 'dgb_disabled_post_types';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_filter( 'use_block_editor_for_post', array( $this, 'disable_gutenberg_for_selected_post_types' ), 10, 2 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
    }

    // Add top-level menu
    public function add_admin_menu() {
        add_menu_page(
            __( 'Disable Gutenberg', 'disable-gutenberg' ),
            __( 'Disable Gutenberg', 'disable-gutenberg' ),
            'manage_options',
            'disable-gutenberg',
            array( $this, 'settings_page' ),
            'dashicons-editor-table',
            60
        );
    }

    // Register plugin settings
    public function register_settings() {
        register_setting( 'dgb_settings_group', $this->option_name );
    }

    // Disable Gutenberg for selected post types
    public function disable_gutenberg_for_selected_post_types( $use_block_editor, $post ) {
        $disabled_post_types = get_option( $this->option_name, array() );
        if ( isset( $post->post_type ) && in_array( $post->post_type, $disabled_post_types, true ) ) {
            return false;
        }
        return $use_block_editor;
    }

    // Admin settings page
    public function settings_page() {
        $all_post_types = get_post_types( array( 'public' => true ), 'objects' );
        $disabled_post_types = get_option( $this->option_name, array() );

        // Remove Media (attachments)
        if ( isset( $all_post_types['attachment'] ) ) {
            unset( $all_post_types['attachment'] );
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Disable Gutenberg', 'disable-gutenberg' ); ?></h1>
            <p><?php esc_html_e( 'Select the post types for which you want to disable the Gutenberg editor.', 'disable-gutenberg' ); ?></p>
            <form method="post" action="options.php" style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:8px;max-width:600px;">
                <?php settings_fields( 'dgb_settings_group' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'Post Types', 'disable-gutenberg' ); ?></th>
                        <td>
                            <?php foreach ( $all_post_types as $post_type ): ?>
                                <label style="display:block;margin-bottom:8px;font-weight:normal;">
                                    <input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[]" value="<?php echo esc_attr( $post_type->name ); ?>"
                                        <?php checked( in_array( $post_type->name, $disabled_post_types ) ); ?> />
                                    <?php echo esc_html( $post_type->labels->singular_name ); ?>
                                </label>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                </table>
                <?php submit_button( __( 'Save Changes', 'disable-gutenberg' ) ); ?>
            </form>
        </div>
        <?php
    }

    // Admin styling
    public function enqueue_styles( $hook ) {
        if ( $hook !== 'toplevel_page_disable-gutenberg' ) {
            return;
        }
        echo '<style>
            .wrap h1 { font-size:24px; color:#0073aa; }
            .form-table th { width:200px; }
            input[type="checkbox"] { transform:scale(1.2); margin-right:6px; }
        </style>';
    }

}

new Disable_Gutenberg_Plugin();
