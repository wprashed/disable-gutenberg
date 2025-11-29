<?php
/*
Plugin Name: Disable Gutenberg
Description: Enable or disable Gutenberg editor for specific post types from a top-level admin menu in WordPress.
Version: 1.2
Author: Rashed Hossain
*/

if ( ! defined( 'ABSPATH' ) ) exit;

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
            'Disable Gutenberg',
            'Disable Gutenberg',
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

    // Filter to disable Gutenberg
    public function disable_gutenberg_for_selected_post_types( $use_block_editor, $post ) {
        $disabled_post_types = get_option( $this->option_name, array() );
        if ( isset( $post->post_type ) && in_array( $post->post_type, $disabled_post_types, true ) ) {
            return false;
        }
        return $use_block_editor;
    }

    // Admin page
    public function settings_page() {
        $all_post_types = get_post_types( array( 'public' => true ), 'objects' );
        $disabled_post_types = get_option( $this->option_name, array() );

        // Remove Media
        if ( isset( $all_post_types['attachment'] ) ) {
            unset( $all_post_types['attachment'] );
        }
        ?>
        <div class="wrap">
            <h1 style="margin-bottom:20px;">Disable Gutenberg</h1>
            <p>Select the post types for which you want to disable the Gutenberg editor.</p>
            <form method="post" action="options.php" style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:8px;max-width:600px;">
                <?php settings_fields( 'dgb_settings_group' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Post Types</th>
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
                <?php submit_button('Save Changes'); ?>
            </form>
        </div>
        <?php
    }

    // Admin styles
    public function enqueue_styles($hook) {
        if ( $hook !== 'toplevel_page_disable-gutenberg' ) return;
        echo '<style>
            .wrap h1 { font-size:24px; color:#0073aa; }
            .form-table th { width:200px; }
            input[type="checkbox"] { transform:scale(1.2); margin-right:6px; }
        </style>';
    }

}

// Initialize plugin
new Disable_Gutenberg_Plugin();