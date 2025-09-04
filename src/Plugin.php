<?php

namespace WPBooks;

use Exception;

/**
 * Class Plugin
 *
 * Bootstrapper for the WP Books plugin.
 *
 * @package WPBooks
 */
class Plugin
{
    /**
     * Plugin instance (singleton).
     *
     * @var Plugin|null
     */
    private static ?Plugin $instance = null;

    /**
     * Plugin version.
     *
     * @var string
     */
    public string $version = '1.1.0';

    /**
     * Get singleton instance.
     *
     * @return Plugin
     */
    public static function get_instance(): Plugin {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Plugin constructor.
     * Registers hooks and initializes components.
     */
    private function __construct() {
        // Activation hook - create DB table.
        register_activation_hook( WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/wp-books.php', [ $this, 'on_activation' ] );

        // Init components when plugins_loaded fires.
        add_action( 'init', [ $this, 'init' ] );
    }

    /**
     * Initialize plugin components.
     *
     * @return void
     */
    public function init(): void {
        try {
            Database::get_instance()->maybe_create_table();
            Shortcode::get_instance()->register();
            AjaxHandler::get_instance()->register();

            if ( !is_admin() ) {
                add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
            }

        } catch ( Exception $e ) {
            error_log( 'WP Books init error: ' . $e->getMessage() );
        }
    }

    /**
     * Activation callback.
     *
     * @return void
     */
    public function on_activation(): void {
        // Ensure table is created on activation.
        Database::get_instance()->maybe_create_table();
    }

    /**
     * Enqueue frontend assets.
     *
     * @return void
     */
    public function enqueue_assets(): void {
        $plugin_url = plugin_dir_url( dirname( __DIR__ ) . '/wp-books.php' );
        wp_register_script(
            'wp-books-frontend',
            $plugin_url . 'assets/js/book-frontend.js',
            [ 'jquery' ],
            $this->version,
            true
        );

        // Localize important data (ajax_url, nonce)
        wp_localize_script( 'wp-books-frontend', 'WPBooksData', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'wpbooks_nonce' ),
            'per_page' => 10
        ] );

        wp_enqueue_script( 'wp-books-frontend' );
    }
}